<?php
if (!defined('ABSPATH')) exit;
global $mtv_search_parameters;
global $mtv_search_results;
global $search_title;
global $search_default_img;
global $mtv_search_action;

if (isset($_REQUEST['searchpage'])) {
  if (in_array($_REQUEST['searchpage'], mtv_search_pages())) {
    $mtv_search_action = false;
  }
}
?>
<div class="results-title"><?php echo $search_title; ?></div>
<?php
if (empty($mtv_search_results)) {
?>
  <div id="results-empty">
    <?php
    echo __('Unfortunately, there are no results for your search. Please try once more with different criteria.', 'mtv-search');
    ?>
  </div>
<?php
  return;
}
$search_default_img = get_option('mtv_search_img_id') ?: '';
?>
<div class="results-wrapper">
  <?php
  foreach ($mtv_search_results as $post) {
    global $result_post;
    $result_post = $post;
    echo mtv_search_template_part('result.php');
  }
  ?>
</div>
<?php

if ($mtv_search_action) {
  echo mtv_search_template_part('more_results.php');
}
