<?php 
  global $login_uid;
  $rating = rating('content', $contents->content_id);
?>
<div class="single_post">
<div class="image">
  <h1><?php echo $contents->title;?></h1>
  <table cellspacing="0">
   <tr valign="top">
   <td class="author" width="85">
    <?php global $current_theme_path;  ?>
    <?php echo '<a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id.'">'.uihelper_resize_mk_user_img($picture_name, 80, 80, 'alt=""').'</a>'; ?><br /><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id ?>"><?php echo wordwrap(chop_string($user_name,40),20);?></a>
     </td>
     <td class="message" width="100%">
    <p class="message_start">
 
      <?php 
          $image_hyperlink = "/media_full_view.php?cid=".$contents->content_id."&amp;type=image";
      ?>
        <a href="<?=$image_hyperlink;?>" class="links-module"><?= uihelper_resize_mk_img($contents->file_name, 80, 70, NULL, 'alt="Picture"', RESIZE_FIT) ?></a>
      <?php echo $contents->body;?>
    </p>
      <div class="rating">
        <b><?= __("Overall Rating") ?>:</b>
        <span id="overall_rating_<?php echo $contents->content_id?>"><?php echo $rating['overall']?></span>
      </div>
      <?php
        if (!empty($login_uid)) {
      ?>

      <div class="rating">
        <b><?= __("Your Rating") ?>:</b>
        <span><?php echo $rating['new']?></span>
      </div>
    
      <?php
        } else {
      ?>
      <div class="rating"><?= __("Please") ?> <a href="<?php echo PA::$url?>/login.php?return=<?php echo PA::$url .PA_ROUTE_CONTENT. '/cid=' . $contents->content_id?>"><?= __("log in") ?></a> <?= __("to rate this image") ?>
      </div>
      <?php
        }
      ?>
   
   <?php
     require "common_permalink_content.tpl";
   ?>
</div>
</div>