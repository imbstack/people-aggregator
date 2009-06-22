<?
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }   
?>
<?if (!empty($title)) {?><h1><?php echo $title;?></h1><?}?>
<div class="wide_content" <?php echo $id;?>>

  <?php echo $inner_HTML;?>

</div>