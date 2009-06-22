<?
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }   
?>
<div id="col_d">
<div class="total_content" <?php echo $id;?>>
  <?php echo $inner_HTML;?>
</div>
</div>