<?php
if (!defined('ABSPATH')) {
 exit;
}

if (!function_exists('mtv_search_pages')) {
 function mtv_search_pages()
 {
  $pages = (array)get_option('mtv_search_search_results_page') ?: array(); /*search result page*/
  if (!empty($pages)) {
   foreach ($pages as $page) {
    $tran_id = mtv_search_get_translation($page);
    if ($tran_id != $page) {
     $pages[] = $tran_id;
    }
   }
  }
  return $pages;
 }
}



if (!function_exists('mtv_admin_settings')) {
 /**
  * set the admin settings
  */
 function mtv_admin_settings()
 {
  return apply_filters('mtv_admin_settings_filter', array(
   'mtv_search_trigger_element' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Element class/id to trigger search on click', 'mtv-search'),
    'explanation' => __('valid query selector like #main,.class', 'mtv-search')
   ),
   'mtv_search_search_results_page' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Search results page id', 'mtv-search'),
    'label_class' => array('awm-needed'),
    'explanation' => __('The page where the results will be shown', 'mtv-search')
   ),
   'mtv_search_include_script' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Include scripts', 'mtv-search'),
    'explanation' => __('Leave empty to include it everywhere, otherwise write the ids of the page, seperated by comma', 'mtv-search')
   ),
   'mtv_search_img_id' => array(
    'case' => 'image',
    'label' => __('Default featured image', 'mtv-search'),
   ),
   'mtv_search_post_types' => array(
    'label' => __('Post types to search in', 'mtv-search'),
    'case' => 'post_types',
    'attributes' => array('multiple' => true),
    'label_class' => array('awm-needed'),
   ),
   'mtv_search_taxonomies' => array(
    'label' => __('Taxonomies to filter', 'mtv-search'),
    'case' => 'taxonomies',
    'attributes' => array('multiple' => true),
    'label_class' => array('awm-needed'),
   ),
   'mtv_exclude_taxonomies' => array(
    'label' => __('Taxonomies to exlude (ids)', 'mtv-search'),
    'case' => 'input',
    'type' => 'text',
   ),
   'mtv_search_years' => array(
    'label' => __('Years', 'mtv-search'),
    'case' => 'input',
    'type' => 'text',
    'explanation' => __('Leave empty not to show date search. Use comma to separate years', 'mtv-search')
   ),
   'mtv_default_order' => array(
    'label' => __('Default order', 'mtv-search'),
    'case' => 'select',
    'removeEmpty' => true,
    'options' => array(
     'publish_date' => array('label' => __('Publish date', 'mtv-search')),
     'modified' => array('label' => __('Modified date', 'mtv-search')),
     'title' => array('label' => __('Post title', 'mtv-search'))
    ),
   ),
   'mtv_default_order_type' => array(
    'label' => __('Default order type', 'mtv-search'),
    'case' => 'select',
    'removeEmpty' => true,
    'options' => array(
     'ASC' => array('label' => __('ASC', 'mtv-search')),
     'DESC' => array('label' => __('DESC', 'mtv-search')),

    ),
   ),

  ));
 }
}


add_shortcode('mtv_search', function ($atts) {

 $variables = shortcode_atts(array(
  'method' => 'get',
  'clean_view' => '0',
  'post_types' => array(),
  'taxonomies' => array(),
  'action' => '',
  'years' => array(),
  'results' => 0,
  'placeholder' => __('Search', 'motivar-search'),
  'filter_icon' => mtv_search_url . 'assets/img/filter.svg',
  'close_icon' => mtv_search_url . 'assets/img/close.svg',
  'search_icon' => mtv_search_url . 'assets/img/search.svg',
 ), $atts);
 $variables['action'] = get_permalink(mtv_search_get_translation(get_option('mtv_search_search_results_page')));
 $variables['method'] = 'post';
 $variables['main-class'] = array();
 $variables['main-class'][] = $variables['results'] == 1 ? 'show-filter' : '';
 $variables['main-class'][] = $variables['clean_view'] == 1 ? 'show-close' : '';
 if (empty($variables['post_types'])) {
  $variables['post_types'] = get_option('mtv_search_post_types') ?: array();
 }
 if (empty($variables['taxonomies'])) {
  $variables['taxonomies'] = get_option('mtv_search_taxonomies') ?: array();
 }
 $variables['exclude_ids'] = get_option('mtv_exclude_taxonomies') ?: array();
 if (empty($variables['years'])) {
  $variables['years'] = get_option('mtv_search_years') ?: array();
 }


 global $mtv_search_parameters;
 $mtv_search_parameters = $variables;
 return mtv_search_template_part('body.php');
});



if (!function_exists('mtv_search_template_part')) {
 /**
  * this function is used to get parts for the template of the project
  * @param string $file the full file path to get
  */
 function mtv_search_template_part($file)
 {
  $template_over_write = get_stylesheet_directory() . '/templates/mtv-search/' . $file;
  $file = file_exists($template_over_write) ? $template_over_write : mtv_search_path . 'templates/' . $file;
  ob_start();
  include $file;
  $content = ob_get_clean();
  return apply_filters('mtv_search_template_part_filter', $content, $file);
 }
}


if (!function_exists('mtv_search_get_translation')) {
 /**
  * this functions get the translations of the objects
  */
 function mtv_search_get_translation($id)
 {
  if (function_exists('icl_object_id')) {
   global $sitepress;
   $postType = get_post_type($id);
   $id = (int) icl_object_id($id, $postType, false, ICL_LANGUAGE_CODE);
  }
  return $id;
 }
}


if (!function_exists('mtv_seach_limit_text')) {
 /**
  * limits the text of a certain element
  * @param string $text 
  * @param int $limit how many words
  * @param boolean $strip  strip tags
  */
 function mtv_seach_limit_text($text, $limit = 10, $strip = false)
 {
  if ($limit != 0) {
   if ($strip) {
    $text = strip_tags($text, '<br>');
    $text = strip_shortcodes($text);
    $text = str_replace(array("\n", "\r", "\t"), ' ', $text);
    $text = str_replace('&nbsp;', ' ', preg_replace('#<[^>]+>#', ' ', $text));
   }
   $words = explode(' ', $text);
   $c = count($words);
   if ($c > $limit) {
    $textt = array();
    for ($i = 0; $i < $limit; ++$i) {
     $textt[] = $words[$i];
    }

    $text = implode(' ', $textt);
    $text .= '...';
   }
  }

  return $text;
 }
}


if (!function_exists('mtv_search_hidden_inputs')) {
 function mtv_search_hidden_inputs($parameters)
 {
  $inputs = array();
  $vars = array(
   'numberposts' => $parameters['number'],
   'lang' => function_exists('icl_object_id') ? ICL_LANGUAGE_CODE : '',
   'post_types' => is_array($parameters['post_types']) ? implode(',', $parameters['post_types']) : $parameters['post_types'],
   'mtv_search' => 1
  );

  foreach ($vars as $key => $value) {
   $inputs[$key] = array(
    'case' => 'input',
    'type' => 'hidden',
    'attributes' => array('value' => $value, 'exclude_meta' => true,)
   );
  }

  return $inputs;
 }
}


if (!function_exists('mtv_search_prepare_filters')) {
 function mtv_search_prepare_filters($parameters, $option)
 {
  $arrs = array();
  $exclude_ids = array();
  if (isset($parameters['taxonomies']) && !empty($parameters['taxonomies'])) {
   if (!empty($parameters['exclude_ids'])) {
    $exclude_ids = explode(',', $parameters['exclude_ids']);
   }

   foreach ($parameters['taxonomies'] as $taxonomy) {
    $tax = get_taxonomy($taxonomy);
    $arrs[$taxonomy] = array(
     'label' => $tax->label,
     'case' => 'term',
     'taxonomy' => $taxonomy,
     'args' => array('hide_empty' => true, 'exclude' => $exclude_ids),
     'view' => 'checkbox_multiple',
     'attributes' => array('value' => array($option)),
    );
   }
  }
  if (!empty($parameters['years'])) {
   $years = explode(',', $parameters['years']);
   $labels = array();
   foreach ($years as $year) {
    $labels[$year] = array('label' => $year);
   }
   $arrs['mtv_year'] = array(
    'label' => __('Year', 'motivar'),
    'case' => 'checkbox_multiple',
    'options' => $labels,
    'attributes' => array('exclude_meta' => true),
   );
  }
  return $arrs;
 }
}
