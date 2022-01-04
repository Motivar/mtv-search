<?php
if (!defined('ABSPATH')) {
 exit;
}

class MTV_SEARCH
{
 public function __construct()
 {
  require_once 'functions.php';
  add_action('rest_api_init', array($this, 'mtv_rest_endpoints'));
  add_filter('awm_add_options_boxes_filter', array($this, 'mtv_settings'), 100);
  add_action('init', array($this, 'registerScripts'), 10);
  add_action('wp', array($this, 'check_page'));
  add_filter('body_class', array($this, 'mtv_search_page_class'));
 }

 public function mtv_search_page_class($classes)
 {
  if (in_array(get_the_ID(), mtv_search_pages())) {
   $classes[] = 'mtv-search-page-results';
  }
  return $classes;
 }





 public function check_page()
 {
  $pages = array();
  $all = false;
  $include = get_option('mtv_search_include_script') ?: '';/* pages to include script */
  if (empty($include)) {
   $all = true;
  }
  if (!empty($include)) {
   $pages = explode(',', $include);
  }
  $extra_pages = mtv_search_pages();
  foreach ($extra_pages as $page) {
   $pages[] = $page;
  }
  if ($all || in_array(get_the_ID(), $pages)) {
   add_action('wp_enqueue_scripts', array($this, 'addScripts'), 100);
   add_action('wp_footer', array($this, 'loading_effect'));
   add_action('wp_body_open', array($this, 'mtv_add_hidden_divs'), 100);
  }
 }

 /**
  * register styles and script for tippy
  */
 public function registerScripts()
 {

  wp_register_script('mtv-search-script', mtv_search_url . 'assets/js/mtv_search.js', array(), false, 1);
  wp_register_style('mtv-search-style', mtv_search_url . 'assets/css/full-screen.min.css', false, '1.0.0');
 }

 /**
  * add scripts to run for admin and frontened
  */
 public function addScripts()
 {
  wp_enqueue_style('mtv-search-style');
  wp_localize_script('mtv-search-script', 'mtv_search_vars', apply_filters('mtv_search_vars_filter', array('trigger' => get_option('mtv_search_trigger_element'))));
  wp_enqueue_script('mtv-search-script');
 }



 public function mtv_add_hidden_divs()
 {
  global $post;
  $pages = mtv_search_pages();
  if (in_array($post->ID, $pages)) {
   return;
  }
  echo mtv_search_template_part('search-full-screen.php');
 }


 public function mtv_settings($options)
 {
  $options['mtv_search_settings'] = array(
   'title' => __('Mtv Search Settings', 'mtv-search'),
   'callback' => 'mtv_admin_settings',
   'explanation' => __('Here you configure all the settings regarding the search functionallity. It is <b>important</b> to create a page with the shortocode [mtv_search results="1"] and declare it below.')
  );
  return $options;
 }


 /**
  * 
  */
 public function loading_effect()
 {
  echo mtv_search_template_part('loading.php');
 }
 /**
  * register rest endpoints
  */
 public function mtv_rest_endpoints()
 {
  /*check here*/
  register_rest_route('mtv-search', '/search', array(
   'methods' => 'GET',
   'callback' => array($this, 'mtv_search_results'),
  ));
 }

 /**
  * make the query and gather the results
  */
 public function mtv_search_results($request)
 {
  $response = '';
  if (empty($request)) {
   return;
  }
  $params = $request->get_params();
  if (empty($params)) {
   return;
  }
  global $mtv_search_results;
  global $mtv_search_action;
  global $mtv_search_params;

  $mtv_search_params = $params;
  $mtv_search_action = true;
  $mtv_search_results = $this->construct_post_query();
  $response = mtv_search_template_part('results.php');
  return rest_ensure_response(new WP_REST_Response($response), 200);
 }
 /**
  * construct the query based on the request
  */

 public function construct_post_query()
 {
  global $mtv_search_params;
  $title = array();
  $tax_query = $meta_query = array();
  $default_order = get_option('mtv_default_order') ?: 'publish_date';
  $default_order_type = get_option('mtv_default_order_type') ?: 'DESC';
  $args = array(
   'post_status' => 'publish',
   'suppress_filters' => false,
   'post_type' => explode(',', $mtv_search_params['post_types']),
   'numberposts' => $mtv_search_params['numberposts'],
   'orderby' => $default_order,
   'order' => $default_order_type
  );
  if (isset($mtv_search_params['searchtext'])) {
   $args['s'] = sanitize_text_field($mtv_search_params['searchtext']);
   $title[] = sprintf(__('Results for %s', 'mtv-search'), '<span class="searched">"' . sanitize_text_field($mtv_search_params['searchtext']) . '"</span>');
  }



  if (isset($mtv_search_params['awm_custom_meta'])) {
   $taxonomies = $mtv_search_params['awm_custom_meta'];
   foreach ($taxonomies as $key) {
    if (isset($mtv_search_params[$key]) && !empty($mtv_search_params[$key])) {
     $tax_query[] =
      array(
       'taxonomy' => $key,
       'terms' => $mtv_search_params[$key],
       'field' => 'id',
       'operator' => 'IN',
      );
     $termTitle = array();
     if (isset($mtv_search_params['searchtext'])) {
      $title[] = __('at', 'mtv-search');
     }
     foreach ($mtv_search_params[$key] as $term) {
      $termData = get_term($term, $key);

      if ($termData) {
       $termTitle[] = $termData->name;
      }
     }
     $title[] = implode(', ', $termTitle);
    }
   }
  }
  if (!empty($tax_query)) {
   $tax_query['relation'] = 'OR';
   $args['tax_query'] = $tax_query;
  }

  if (isset($mtv_search_params['mtv_year']) && !empty($mtv_search_params['mtv_year'])) {
   $args['date_query'] = array(
    'relation' => 'OR',
   );
   $years = array();
   foreach ($mtv_search_params['mtv_year'] as $year) {
    $args['date_query'][] = array('year' => $year);
    $years[] = $year;
   }
   $title[] = sprintf(__('for the year(s) %s', 'mtv-search'), implode(', ', $years));
  }
  global $search_title;
  $search_title = implode(' ', $title);
  return get_posts($args);
 }
}


new MTV_SEARCH();
