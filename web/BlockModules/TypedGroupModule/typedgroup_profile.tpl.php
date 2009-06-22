<div class="blog" style="overflow:auto;">
<div style="float:left; width:40%;overflow:auto;">
<?php
$atts = $mod->entity->attributes;

if (!empty($atts['logo'])) {
	$img_info = uihelper_resize_img($atts['logo']['value'], 200, 90, "images/default_group.gif", NULL, RESIZE_FIT);
	echo '<img src="'.$img_info['url'].'" alt="Logo" '.$img_info['size_attr'].' style="margin:0;float:none;"/>';
}
if (!empty($atts['slogan'])) {
	echo "<br/><p>"._out($atts['slogan']['value'])."</p>";
}
?>
</div>
<table>
<tr>
	<td><?=__("Type")?>:</td>
	<td><?php
	$type = $mod->entity->entity_type;
	if (!empty($mod->availTypes[$type])) {
		echo '<a href="'.PA::$url.PA_ROUTE_TYPED_DIRECTORY.$type.'">'.$mod->availTypes[$type]."</a>";
	} else {
		echo $mod->entity->entity_type; 
	}
	?></td>
</tr>
<?php
// echo "<pre>".print_r($mod->profilefields,1)."</pre>";
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
<br style="clear:both"/>

<?php if ($mod->is_member) { ?>
  <a href="<?php echo PA::$url.PA_ROUTE_GROUP?>/action=leave&amp;gid=<?= $mod->group_details->collection_id?>">
  <?= __('Leave This Group') ?>
  </a>
<?php } ?>

<?php if (!empty(PA::$login_uid) ) { ?>
	<form action="<?= PA::$url.PA_ROUTE_GROUP?>/action=<?=
	(empty($mod->is_member)) ? 'join' : 'update' ?>&amp;gid=<?= $mod->group_details->collection_id?>">
	<?php if ($mod->is_member) { ?>
		<b><?=$mod->relationTypeString?></b>
		<a href="#" onclick="$('#relationType').toggle(); return false;"><?=__("Update relation")?></a>
		<br/>
	<? } else { ?>
	<b><?= $mod->join_this_group_string;?></b>
	<? } ?>
	<div id="relationType" <? if ($mod->is_member) echo 'style="display:none;"'; ?>>
	<select name="relation"
		<option value=""><?=__("-- Please select --")?></option>
		<?php
		foreach ($mod->availRelations as $k=>$v) {
			?>
			<option value="<?=$k?>" <?php
			if ($k == @$mod->relationType) echo 'selected="selected"'
			?>><?=$v?></option>
			<?
		}
		?>
	</select>
	<input type="submit" name="submit" id="joinbutton" value="<?= ($mod->is_member) ? __("Update") : __("Join now") ?>" />
	</div>
</form>
<?php } ?>

<?php if ($mod->is_admin) { ?>
  <a href="<?= PA::$url.'/'.FILE_ADDGROUP?>?gid=<?= $mod->group_details->collection_id?>">
   <?= __('Group Settings')?>    
  </a>
<?php 
  }
?>
</div>
