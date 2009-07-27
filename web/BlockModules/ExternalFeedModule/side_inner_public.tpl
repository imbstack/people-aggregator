<?php  //p($links);?>
<div class="module_tagcloud">
  <ul>
  <?php if (count($links) == 0) { ?> 
    <li>No data  </li>
  <?php } else { ?>
    <?php foreach ($links as $value) { ?>
		 <li>
       <a href="<?=stripslashes($value->original_url);?>" target = "_blank"><?=$value->title;?></a>
     </li>
	 	<?php } ?>
   
   <?php } ?>
  </ul> 
</div>