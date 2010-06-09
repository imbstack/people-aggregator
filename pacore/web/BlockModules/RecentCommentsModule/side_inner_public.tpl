<?php // global var $_base_url has been removed - please, use PA::$url static variable

    ?>
<div class="module_definition_list">
<dl>
<?php
  if(count($links) > 0) {
    for ($i=0; $i<count($links); $i++) {
      $date = content_date($links[$i]['created']);
      $comment = $links[$i]['comment'];
      $comment = _out(chop_string($comment,48));
      $post_title = $links[$i]['post_title'];
      $post_title = _out(chop_string($post_title,18));
      $author = $links[$i]['name'];
      $author_id = $links[$i]['user_id'];
      $comment = str_replace('<br />',' ',$comment);
?>

          <dt>
           <a href="<?php echo PA::$url . PA_ROUTE_CONTENT . "/cid=".$links[$i]["content_id"]; ?>"><?php echo $post_title;?></a>
           <br />
           <span><?php echo $comment;?></span>
           <span><?= __('Author') . ': '?><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $author_id ?>"><?= $author ?></a></span>
         </dt>

<?}
  }
  else { ?>
    <dt><?= __('No comments posted yet.'); ?></dt>
<?php } ?>
</dl>
</div>
