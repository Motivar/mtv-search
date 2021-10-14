<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $result_post;
$default_img=3740;
$img=get_post_thumbnail_id($result_post->ID) ?: '';
$featured=!empty($img) ? $img : $default_img;
$permalink=get_permalink($result_post->ID);
?>
<div id="<?php echo $result_post->ID?>" class="result-wrapper">
<div class="featured"><a href="<?php echo $permalink;?>"><?php echo custom_image_element($featured);?></a></div>
<div class="details">
 <div class="text"><?php echo mtv_limit_text($result_post->post_title.'.<br>'.$result_post->post_content, 20,1);?></div>
 <div class="more"><span><a href="<?php echo $permalink;?>"><?php echo __('Read more','mtv-search');?></a></span></div>
</div>
</div>