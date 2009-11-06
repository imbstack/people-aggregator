<div class="module_actions">
  <ul>
<?php
  $c=0;
  foreach($actions as $k=>$action) {
  	if ($k=='highlight') continue;
  	$c++ ;
		$class = (( $c % 2 ) == 0) ? ' class="color"': NULL;
		if (! empty($action['url'])) {
      ?>
        <li <?=$class?>>
          <a href="<?php echo $action['url'];?>" 
          <?php echo @$action['extra']?>>
           <?php 
           if(isset($action['caption'])) echo  chop_string($action['caption'], 30);
           else echo  chop_string($action['title'], 30);
           ?>
          </a>
        </li>
  <?php
  } else if (! empty($action['html'])) { ?>
        <li <?=$class?>>
        <?=$action['caption']?><br />
        <?=$action['html']?>
        </li>
  <?php
  }
} ?>

  </ul>
</div>
