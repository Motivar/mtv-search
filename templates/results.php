<?php
if (!defined('ABSPATH')) exit;
global $search_parameters;
global $search_results;
global $search_title;
global $search_action;

?>
<div class="results-title"><?php echo $search_title; ?></div>
<?php
if (empty($search_results)) {
?>
  <div id="results-empty">
    <?php
    echo __('Unfortunately, there are no results for your search. Please try once more with different criteria.', 'mtv-search');
    ?>
  </div>
<?php
  return;
}
?>
<div class="results-wrapper">
  <?php
  foreach ($search_results as $post) {
    global $result_post;
    $result_post = $post;
    echo crf_template_part(crf_path . 'guest/search/result.php');
  }
  ?>
</div>
<?php

if ($search_action && $_REQUEST['searchpage'] != 1) {
  echo crf_template_part(crf_path . 'guest/search/more_results.php');
}
