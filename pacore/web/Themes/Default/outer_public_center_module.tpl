<?
  if ($html_block_id) {
     $id = "id=\"$html_block_id\"";
  } 
  $gid = null;
  if (isset($_GET['gid'])) {
    $gid = (int)$_GET['gid'];
  } else if (isset($_GET['ccid'])) {
    $gid = (int)$_GET['ccid'];
  }
?>
<div class="wide_content" <?php echo $id;?>>
  <?php if(!empty($title)) {?><h1><?php echo $title;?> </h1><?}?>
  <?php echo $inner_HTML;?>
</div>  