<?php
  global $login_uid;
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }
  $var_url=NULL;
  if ( !empty($_GET['uid'])) {
    $var_url = "/uid=".$_GET['uid'];
  } 
  if ( !empty( $_GET['view'] )) {
    $var_url .= "&view=friends";
  }
?>
<div class="total_content" <?php echo $id;?>>
<?php if (!isset($_GET['gid']) || (empty($_GET['gid'])) ) {?>
<ul id="filters">
  <li <?php echo (!isset ($_GET['type']) || ($_GET['type'] == 'Images')) ? 'class = "active"':''; ?> id="epm0"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . $var_url ?>">Image</a></li>
  <li <?php echo (!empty($_GET['type']) && $_GET['type'] == 'Audios') ? 'class = "active"':''; ?> id="epm1"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . $var_url ?>">Audio</a></li>
  <li <?php echo (!empty($_GET['type']) && $_GET['type'] == 'Videos') ? 'class = "active"':''; ?> id="epm2"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . $var_url ?>">Video</a></li>
</ul>
<?} else {?>
<ul id="filters">
  <li <?php echo (!isset ($_GET['type']) || ($_GET['type'] == 'Images')) ?'class = "active"':''; ?> id="epm0"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media&gid=" . $_GET['gid'];?>">Image</a></li>
  <li <?php echo (!empty($_GET['type']) && $_GET['type'] == 'Audios') ?'class = "active"':''; ?> id="epm1"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/view=groups_media&gid=" . $_GET['gid'];?>">Audio</a></li>
  <li <?php echo (!empty($_GET['type']) && $_GET['type'] == 'Videos') ?'class = "active"':''; ?> id="epm2"><a href="<?= PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS . "/view=groups_media&gid=" . $_GET['gid'];?>">Video</a></li>
</ul>
<?}?>
  <?php echo $inner_HTML;?>
</div>