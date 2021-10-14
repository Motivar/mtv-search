<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $search_parameters;
global $search_archive_option;
$search_action=$search_parameters['results']==1 ? get_permalink(mtv_get_translation($search_parameters['results_id'])) : $search_parameters['action'];
$searchtext=isset($_REQUEST['searchtext']) ? sanitize_text_field($_REQUEST['searchtext']) : '';
$autotrigger=$searchtext!='' ? 1 : 0;
$number=$autotrigger==1 ? -1 : 15;
if ($search_parameters['clean_view']==1)
{
  $autotrigger=0;
  $number=15;
  $searchtext='';
}
 ?>
<div id="search_form" data-trigger="<?php echo $autotrigger;?>"> 
<form id="mtv-form" method="get" action="<?php echo $search_action?>">
   
    <div class="search-bar <?php echo $search_parameters['main-class'];?>">
      <div class="inputs"><input type="hidden" name="searchpage" value="<?php echo $autotrigger;?>"/><input type="text" placeholder="<?php echo $search_parameters['placeholder'];?>" id="searchtext" name="searchtext" class="highlight" value="<?php echo $searchtext;?>" required="true"><input type="hidden" name="numberposts" value="<?php echo $number;?>"><input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE;?>"/></div>
      <div class="search-icon"><span id="search-trigger" onclick="mtv_search();"><img src="<?php echo wp_get_attachment_url($search_parameters['search_icon']);?>" class="search_img" alt="corfu-official-website-search"/></span></div>
      
      <?php
      if ($search_parameters['results']==1)
      {
       ?>
       <div class="search-icon"><span id="filter-trigger" onclick="changeSearchContainer(this);"><img src="<?php echo wp_get_attachment_url($search_parameters['filter_icon']);?>" class="search_img" alt="corfu-official-website-search-filter"/></span></div>
       <?
      }
      ?>
      </div>
    
 
<?php
if ($search_parameters['results']==1)
{

if (!isset($_REQUEST['searchtext']))
{
    global $search_results;
    global $search_title;
    global $search_action;
    $search_action=false;
    $search_title=__('Most popular articles','mtv-search');
    $args=array(
        'post_type'=>'post',
        'post_status'=>'publish',
        'suppress_filters'=>false,
        'orderby'=>'date',
        'order'=>'desc');
    if (is_archive('category'))
    {
      
      $obj = get_queried_object();
      $search_archive_option=$obj->term_id;
       $search_title=$obj->name;
      $args['tax_query']= array(
              array(
                  'taxonomy' => 'category',
                  'terms'    => array($obj->term_id),
                  'field' => 'id',
                  'operator'=>'IN'
      ));
    }
    $search_results=get_posts($args);
}


 ?>
 <div id="search_form_body">
 <div id="search_form_resutls" class="active">
   <div id="search-results">
<?php echo crf_template_part(crf_path.'guest/search/results.php');?>
</div>
</div>
<div id="search_form_filter">
  <?php echo crf_template_part(crf_path.'guest/search/filters.php');?>
</div>
</div>
<?php
}
?>
<input type="submit" id="submit" value="submit">
</form>
 </div>