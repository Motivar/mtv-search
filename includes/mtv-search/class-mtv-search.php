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
  add_action('wp_footer', array($this, 'loading_effect'));
  add_action('wp_body_open', array($this, 'crf_add_hidden_divs'), 100);
  add_filter('awm_add_options_boxes_filter', array($this, 'mtv_settings'), 100);

  add_action('init', array($this, 'registerScripts'), 10);
  add_action('wp_enqueue_scripts', array($this, 'addScripts'), 10);
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
  $pages = array();
  $all = false;
  $include = get_option('mtv_search_include_script') ?: '';/* pages to include script */
  if (empty($include)) {
   $all = true;
  }
  if (!empty($include)) {
   $pages = explode(',', $include);
  }
  $pages[] = get_option('mtv_search_search_results_page') ?: ''; /*search result page*/
  foreach ($pages as $page) {
   $tran_id = mtv_search_get_translation($page);
   if ($tran_id != $page) {
    $pages[] = $tran_id;
   }
  }
  if ($all || in_array(get_the_ID(), $pages)) {
   wp_enqueue_style('mtv-search-style');
   wp_enqueue_script('mtv-search-script');
  }
 }



 public function crf_add_hidden_divs()
 {
  echo mtv_search_template_part('search-full-screen.php');
 }


 public function mtv_settings($options)
 {
  $options['mtv_search_settings'] = array(
   'title' => __('Mtv Search Settings', 'mtv-search'),
   'callback' => 'mtv_admin_settings',
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
  global $search_results;
  global $search_action;
  global $search_params;
  $search_params = $params;
  $search_action = true;
  $search_results = $this->construct_post_query();
  $response = mtv_search_template_part('results.php');
  return rest_ensure_response(new WP_REST_Response($response), 200);
 }
 /**
  * construct the query based on the request
  */

 public function construct_post_query()
 {
  global $search_params;
  $title = array();
  $tax_query = $meta_query = array();
  $args = array(
   'post_status' => 'publish',
   'suppress_filters' => false,
   'post_type' => explode(',', $search_params['post_types']),
   'numberposts' => $search_params['numberposts'],
   'orderby' => 'date',
   'order' => 'DESC'
  );
  if (isset($search_params['searchtext'])) {
   $args['s'] = sanitize_text_field($search_params['searchtext']);
   $title[] = sprintf(__('Results for %s', 'mtv-search'), '<span class="searched">"' . sanitize_text_field($search_params['searchtext']) . '"</span>');
  }



  if (isset($search_params['awm_custom_meta'])) {
   $taxonomies = $search_params['awm_custom_meta'];
   foreach ($taxonomies as $key) {
    if (isset($search_params[$key]) && !empty($search_params[$key])) {
     $tax_query[] =
      array(
       'taxonomy' => $key,
       'terms' => $search_params[$key],
       'field' => 'id',
       'operator' => 'IN',
      );
     $termTitle = array();
     if (isset($search_params['searchtext'])) {
      $title[] = __('at', 'mtv-search');
     }
     foreach ($search_params[$key] as $term) {
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

  if (isset($search_params['year']) && !empty($search_params['year'])) {
   $args['date_query'] = array(
    'relation' => 'OR',
   );
   $years = array();
   foreach ($search_params['year'] as $year) {
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
