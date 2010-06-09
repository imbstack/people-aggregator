<?php  
  global $page_uid;
  $user_info = PA::$login_user;
  if (!empty($profile_feeds)) {
    foreach ($profile_feeds as $feed) {
    echo '<h1>'.$feed['blog_title'].'</h1>';
      if (!empty($feed['links'])) foreach ($feed['links'] as $link) {
      
?>
<div class="blog"><a name="<?php echo $link->feed_data_id;?>"></a>
  <h1><a href="<?php echo $link->original_url?>" target="_blank"><?php echo $link->title; ?></a></h1>
  <?php echo $link->description;?>
  <div class="post_info">
    posted by <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_info->user_id?>"><?php echo $user_info->login_name?></a> on <a href="<?php echo $link->original_url?>" target="_blank"><?php echo $feed['blog_title'];?></a>
    <div class="col_end"></div>
  </div>
</div>
<?php
      }
    }
  }
?>