<?php // global var $_base_url has been removed - please, use PA::$url static variable
?>
<div class="module_tagcloud">
<?php if(!empty($links[0]['body'])) { ?>
  <ul>
    <li><?php echo $links[0]['body'];?></li>
    <li style="text-align:center"><a href="<?php echo
PA::$url .'/'.FILE_ANSWERS;?>">View/Submit Answers</a></li>
  </ul>
<? } ?>
</div>
