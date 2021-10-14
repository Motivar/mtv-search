<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $search_parameters;
global $search_archive_option;
$arrs=array();
$allvalues=array();
if (isset($search_parameters['filters']))
{
 $filters=explode('|',$search_parameters['filters']);
 foreach ($filters as $filter)
 {
  $filter=explode(':',$filter);
  $data=explode('=',$filter[1]);
  $include=explode(',',$data[1]);
  switch ($filter[0])
  {
   case 'taxonomy':
        $tax=get_taxonomy( $data[0] );

    $arrs[$data[0]]= array(
            'label' =>$tax->label,
            'case' => 'term',
            'taxonomy'=>$data[0],
            'args'=>array('include'=>$include),
            'view'=>'checkbox_multiple',
            'attributes'=>array('exclude_meta'=>true,'value'=>array($search_archive_option)),
            
    );
    $allvalues['taxonomies'][]=$data[0];
   break;
   default:
   $post_type_obj = get_post_type_object( $data[0] );
   $arrs[$data[0]]= array(
            'label' =>$post_type_obj->label,
            'case' => 'postType',
            'post_type' => $data[0],
            'args'=>array('posts__in'=>$include,'numberposts'=>count($include),'suppress_filters'=>false),
            'view'=>'checkbox_multiple',
            'attributes'=>array('exclude_meta'=>true),
    );
     $allvalues['post_types'][]=$data[0];
  break;
  }
 }
 if (!empty($allvalues))
 {
         foreach ($allvalues as $key=>$data)
         {      
                 $arrs[$key]=array(
                        'case'=>'input',
                        'type'=>'hidden',
                        'attributes'=>array('value'=>implode(',',$data),'exclude_meta'=>true)
                 );
         }
 }
 /*add year*/
 $arrs['year']=array(
'label' =>__('Year','mtv-search'),
'case' => 'checkbox_multiple',
'options'=>array(
        2018=>array('label'=>2018),
        2019=>array('label'=>2019),
        2020=>array('label'=>2020),
),
'attributes'=>array('exclude_meta'=>true),
);
}

?>
<div class="filters">
<?php echo awm_show_content($arrs);?>
</div>
<div class="filters-actions">
<div class="undo"><div class="button" id="undo-checkboxes" onclick="disableCheckboxes();"><?php echo __('Remove filters','mtv-search');?></div></div>
<div class="apply"><div class="button" id="apply-checkboxes" onclick="newSearch();"><?php echo __('Apply filters','mtv-search');?></div></div>
</div>
