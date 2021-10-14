<?php
if (!defined('ABSPATH')) {
 exit;
}

class MTV_SEARCH
{
 public function __construct()
 {

  add_action('rest_api_init', array($this, 'mtv_rest_endpoints'));
  add_action('wp_footer', array($this, 'loading_effect'));
 }
 /**
  * 
  */
 public function loading_effect()
 {
  echo crf_template_part(crf_path . 'guest/search/loading.php');
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
 public function mtv_search_results()
 {
  $response = '';
  if (empty($_REQUEST)) {
  }
  global $search_results;
  global $search_action;
  $search_action = true;
  $search_results = $this->construct_post_query();
  $response = crf_template_part(crf_path . 'guest/search/results.php');


  return new WP_REST_Response($response);
 }
 /**
  * construct the query based on the request
  */

 public function construct_post_query()
 {
  $title = array();
  $tax_query = $meta_query = array();
  $args = array(
   'post_status' => 'publish',
   'suppress_filters' => false,
   'post_type' => array('service', 'post', 'member', 'project'),
   'numberposts' => $_REQUEST['numberposts'],
   'orderby' => 'date',
   'order' => 'DESC'
  );
  if (isset($_REQUEST['searchtext'])) {
   $args['s'] = crf_slug(sanitize_text_field($_REQUEST['searchtext']), false, true);
   $title[] = sprintf(__('Results for %s', 'mtv-search'), '<span class="searched">"' . sanitize_text_field($_REQUEST['searchtext']) . '"</span>');
  }

  if (isset($_REQUEST['taxonomies'])) {
   $taxonomies = explode(',', $_REQUEST['taxonomies']);
   foreach ($taxonomies as $key) {

    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key])) {
     $tax_query[] =
      array(
       'taxonomy' => $key,
       'terms' => $_REQUEST[$key],
       'field' => 'id',
       'operator' => 'IN',
      );
     $termTitle = array();
     if (isset($_REQUEST['searchtext'])) {
      $title[] = __('at', 'mtv-search');
     }
     foreach ($_REQUEST[$key] as $term) {
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

  if (isset($_REQUEST['year']) && !empty($_REQUEST['year'])) {
   $args['date_query'] = array(
    'relation' => 'OR',
   );
   $years = array();
   foreach ($_REQUEST['year'] as $year) {
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
