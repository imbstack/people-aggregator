<ul id="filters">
  <?php 
  foreach ($mod->sortFields as $i=>$field) {
  	?>
  <li <? if ($mod->sort_by == $field['name']) echo 'class="active"'?> ><a href="<?=PA::$url.PA_ROUTE_TYPED_DIRECTORY.$mod->directoryType?>?sort_by=<?=$field['name']?>"><?= $field['label']?></a></li>
  	<?
  } ?>
</ul>
  <?php if(!empty($mod->page_links)) { ?>
   <div class="prev_next">
     <?php if ($mod->page_first) { echo $mod->page_first; }?>
     <?php echo $mod->page_links?>
     <?php if ($mod->page_last) { echo $mod->page_last;}?>
   </div>
  <?php }  ?>
<div class="blog">
	<div style="float:right;">
		<form action="<?=PA::$url.'/'.FILE_ADDGROUP?>">
		<?php 
		$createType = $mod->directoryType;
		$createLabel = $mod->availTypes[$createType];
		?>
			<input type="hidden" name="entityType" value="<?=$createType?>" />
			<input type="submit" name="submit" value="<?=
				sprintf(__("Create new %s"), $createLabel)
				?>" />
		</form>
	</div>
</div>
<?php
foreach ($mod->typedGroupEntities as $i=>$entity) {
	$atts = $entity->attributes;
?>

<div class="blog">
<?php 
if (!empty($atts['logo'])) {
	$img_info = uihelper_resize_img($atts['logo']['value'], 80, 34, "images/default_group.gif", NULL, RESIZE_FIT);
	echo '<img src="'.$img_info['url'].'" alt="Logo" '.$img_info['size_attr'].'"/>';
}
?>
<h1><a href="<?=PA::$url.PA_ROUTE_GROUP."/gid=".$entity->entity_id?>"><?= $atts['name']['value']?></a></h1>
<div style="overflow:auto;">
<?= $atts['slogan']['value']?>
</div>

<table>
<?php
$statesList = PA::getStatesList();
$countryList = PA::getCountryList();
foreach ($mod->profilefields as $i=>$field) {
	if ('logo' == $field['name']) continue;
	if ('slogan' == $field['name']) continue;
	
	
	$label = $field['label'];
	$attval = @$atts[$field['name']]['value'];
	switch ($field['type']) {
		case 'urltextfield':
			if (!empty($attval)) {
				$url = htmlspecialchars($attval);
				if (!preg_match("!^http(s)*://!", $url)) {
					$url = 'http://'.$url;
				}
				$value = '<a href="'.$url.'">'.$url.'</a>';
			} else {
				$value = '';
			}
		break;
		case 'image':
			if (!empty($attval)) {
				$img_info = uihelper_resize_img($attval, 200, 90, PA::$theme_rel."/skins/defaults/images/header_net.gif", NULL, RESIZE_FIT);
				$value = '<img src="'.$img_info['url'].'" alt="'.$label.'" '.$img_info['size_attr'].' style="margin:0;float:none;"/>';
			} else {
				$value = '';
			}
		break;
		case 'stateselect':
			$value = @$statesList[$attval];
		break;
		case 'countryselect':
			$value = @$countryList[$attval];
		break;
		case 'dateselect':
			$day = @$atts[$field['name'].'_day']['value'];
			$month = @$atts[$field['name'].'_month']['value'];
			$year = @$atts[$field['name'].'_year']['value'];
			if ($year && $month && $day) {
				$value = PA::date(mktime(0,0,0, $month, $day, $year));
			}
		break;
		default:
			$value = $attval;
		break;
	}
	if (empty($value)) continue; // display only fields that have value
	?>
	<tr><td><?=$label?></td><td><?=$value?></td></tr>
	<?php
}
?>
</table>


</div>
<? } ?>

  <?php if(!empty($mod->page_links)) { ?>
   <div class="prev_next">
     <?php if ($mod->page_first) { echo $mod->page_first; }?>
     <?php echo $mod->page_links?>
     <?php if ($mod->page_last) { echo $mod->page_last;}?>
   </div>
  <?php }  ?>