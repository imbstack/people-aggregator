<?php // global var $_base_url has been removed - please, use PA::$url static variable
?>
<div class="module_moderator_info">
 <div style="padding:8px">

  <?php foreach($posts as $post) : $thread_id = $post->get_thread_id(); $post_id = $post->get_id() ?>
  <div class="latest_posts">
   <div class="lpost_img">
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/" . $post->user->user_id ?>">
    <?php echo uihelper_resize_mk_img($post->user->picture, 35, 35, "images/default.png", 'alt="Picture of the forum owner."') ?>
    </a>
   </div>
   <div>
    <a href="<?= $forums_url."&thread_id=$thread_id&post_id=$post_id#p_$post_id"?>"><?= $post->get_title(24) ?></a><br />
    by: <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $post->user->user_id ?>"><?= $post->user->login_name ?></a>
    <p class="post_date"><?= PA::datetime(strtotime($post->get_created_at()), 'long', 'short') ?></p>
   </div>
  </div>
  <?php endforeach; ?>
 </div>
</div>
