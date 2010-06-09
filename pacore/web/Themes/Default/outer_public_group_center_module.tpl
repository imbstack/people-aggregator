<?php
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }
?>
<div class="wide_content" <?php echo $id?>>
  <?php if(!empty($title)) {?><h1><?php echo $title?></h1><?}?>
  <?php echo $inner_HTML;?>
</div>