<?
  if ( $html_block_id ) {
     $id = "id=\"$html_block_id\"";
  }
  
  if (!isset($outer_class_name) || empty ($outer_class_name)) {
    $outer_class_name = 'class="wide_content_edit_page"';
  }
 
?>
<div <?php echo $outer_class_name;?> <?php echo $id;?>>
  <?php if (!empty($title)) { ?><h1><?php echo $title;?></h1><?}?> 
  <?php echo $inner_HTML;?>
</div>