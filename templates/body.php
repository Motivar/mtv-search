<?php
if (!defined('ABSPATH')) exit;
global $search_parameters;
global $search_archive_option;
$searchtext = isset($search_params['searchtext']) ? sanitize_text_field($search_params['searchtext']) : '';
$autotrigger = $searchtext != '' ? 1 : 0;
$number = $autotrigger == 1 ? -1 : 15;
if ($search_parameters['clean_view'] == 1) {
  $autotrigger = 0;
  $number = 15;
  $searchtext = '';
}
$search_parameters['number'] = $number;
$hidden_inputs = mtv_search_hidden_inputs($search_parameters);
$search_parameters['filters'] = mtv_search_prepare_filters($search_parameters, $search_archive_option);


?>
<div id="search_form" data-trigger="<?php echo $autotrigger; ?>">
  <form id="mtv-form" method="get" action="<?php echo $search_parameters['action'] ?>">

    <div class="search-bar <?php echo implode(' ', $search_parameters['main-class']); ?>">
      <div class="inputs"><input type="hidden" name="searchpage" value="<?php echo $autotrigger; ?>" /><input type="text" placeholder="<?php echo $search_parameters['placeholder']; ?>" id="searchtext" name="searchtext" class="highlight" value="<?php echo $searchtext; ?>" required="true"><?php echo awm_show_content($hidden_inputs); ?></div>
      <div class="search-icon"><span id="search-trigger" onclick="mtv_search();"><?php echo file_get_contents($search_parameters['search_icon']) ?: '<img src="' . $search_parameters['search_icon'] . '"/>'; ?></span></div>

      <?php
      if (!empty($search_parameters['filters'])) {
      ?>
        <div class="search-icon"><span id="filter-trigger" onclick="changeSearchContainer(this);"><?php echo file_get_contents($search_parameters['filter_icon']) ?: '<img src="' . $search_parameters['filter_icon'] . '"/>'; ?></span></div>
      <?
      }
      if ($search_parameters['clean_view'] == 1) {

      ?>
        <div class="search-icon"><span id="close-trigger" onclick="mtv_close_search();"><?php echo file_get_contents($search_parameters['close_icon']) ?: '<img src="' . $search_parameters['close_icon'] . '"/>'; ?></span></div>
      <?php
      }
      ?>
    </div>


    <?php
    if ($search_parameters['results'] == 1) {

      if (!isset($search_params['searchtext'])) {
        global $search_results;
        global $search_title;
        global $search_action;
        $search_action = false;
        $search_title = __('Most popular articles', 'mtv-search');
        $args = array(
          'post_type' => 'post',
          'post_status' => 'publish',
          'suppress_filters' => false,
          'orderby' => 'date',
          'order' => 'desc'
        );
        if (is_archive()) {
          $obj = get_queried_object();
          $search_archive_option = $obj->term_id;
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
        $search_results = get_posts($args);
      }


    ?>
      <div id="search_form_body">
        <div id="search_form_resutls" class="active">
          <div id="search-results">
            <?php echo mtv_search_template_part('results.php'); ?>
          </div>
        </div>
        <?php if (!empty($search_parameters['filters'])) {
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