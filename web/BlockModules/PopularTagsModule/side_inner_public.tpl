<?php
  global  $current_theme_path;
?>
<div class="module_tagcloud">
<ul>
<?php
  if ( $links_count = count($links) ) {
    for ($counter =0; $counter < $links_count; $counter++) {
      $links[$counter]['name'] = chop_string($links[$counter]['name'],25);
?>
  <li><a href="<?php echo PA::$url .'/'.FILE_TAG_SEARCH.'?name_string=content_tag&keyword='.$links[$counter]['id']?>"><?php
  echo _out($links[$counter]['name']);
  ?></a> <span>(<?php echo $links[$counter]['occurence']; ?>)</span></li>

<?php
    }
  }
  else { ?>
    <li><?= __("Nothing has been tagged yet.") ?></li>
 <? } ?>

</ul>
</div>
