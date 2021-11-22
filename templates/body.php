<?php
if (!defined('ABSPATH')) exit;
global $mtv_search_parameters;
global $mtv_search_archive_option;
$searchtext = isset($_REQUEST['searchtext']) ? sanitize_text_field($_REQUEST['searchtext']) : '';
$autotrigger = $searchtext != '' ? 1 : 0;
$number = $autotrigger == 1 ? -1 : 15;
if ($mtv_search_parameters['clean_view'] == 1) {
  $number = 15;
  $searchtext = '';
}
$mtv_search_parameters['number'] = $number;
$hidden_inputs = mtv_search_hidden_inputs($mtv_search_parameters);
$mtv_search_parameters['filters'] = mtv_search_prepare_filters($mtv_search_parameters, $mtv_search_archive_option);


?>
<div id="search_form" data-trigger="<?php echo $autotrigger; ?>">
  <form id="mtv-search-form" method="get" action="<?php echo $mtv_search_parameters['action'] ?>">

    <div class="search-bar <?php echo implode(' ', $mtv_search_parameters['main-class']); ?>">
      <div class="inputs"><input type="hidden" name="searchpage" value="<?php echo get_the_ID(); ?>" /><input type="text" placeholder="<?php echo $mtv_search_parameters['placeholder']; ?>" id="searchtext" name="searchtext" class="highlight" value="<?php echo $searchtext; ?>" required="true"><?php echo awm_show_content($hidden_inputs); ?></div>
      <div class="search-icon"><span id="search-trigger" onclick="mtv_search();"><?php echo @file_get_contents($mtv_search_parameters['search_icon']) ?: '<img src="' . $mtv_search_parameters['search_icon'] . '"/>'; ?></span></div>

      <?php
      if (!empty($mtv_search_parameters['filters'])) {
      ?>
        <div class="search-icon"><span id="filter-trigger" onclick="changeSearchContainer(this);"><?php echo @file_get_contents($mtv_search_parameters['filter_icon']) ?: '<img src="' . $mtv_search_parameters['filter_icon'] . '"/>'; ?></span></div>
      <?
      }
      if ($mtv_search_parameters['clean_view'] == 1) {

      ?>
        <div class="search-icon"><span id="close-trigger" onclick="mtv_close_search();"><?php echo @file_get_contents($mtv_search_parameters['close_icon']) ?: '<img src="' . $mtv_search_parameters['close_icon'] . '"/>'; ?></span></div>
      <?php
      }
      ?>
    </div>


    <?php
    if ($mtv_search_parameters['results'] == 1) {

      if (!isset($_REQUEST['searchtext'])) {
        global $mtv_search_results;
        global $search_title;
        global $mtv_search_action;
        $mtv_search_action = false;
        $search_title = __('Most popular articles', 'mtv-search');
        $args = array(
          'post_type' => 'post',
          'post_status' => 'publish',
          'suppress_filters' => false,
          'orderby' => 'date',
          'order' => 'asc'
        );
        if (is_tax('category')) {
          $obj = get_queried_object();

          $mtv_search_archive_option = $obj->term_id;
          $search_title = $obj->name;
          $args['tax_query'] = array(
            array(
              'taxonomy' => $obj->taxonomy,
              'terms'    => array($obj->term_id),
              'field' => 'id',
              'operator' => 'IN'
            )
          );
        }
        $mtv_search_results = get_posts($args);
      }


    ?>
      <div id="search_form_body">
        <div id="search_form_resutls" class="active">
          <div id="search-results">
            <?php echo mtv_search_template_part('results.php'); ?>
          </div>
        </div>
        <?php if (!empty($mtv_search_parameters['filters'])) {
        ?>
          <div id="search_form_filter">
            <?php echo mtv_search_template_part('filters.php'); ?>
          </div>
        <?php
        } ?>

      </div>
    <?php
    }
    ?>
    <input type="submit" id="submit" value="submit">
  </form>
</div>