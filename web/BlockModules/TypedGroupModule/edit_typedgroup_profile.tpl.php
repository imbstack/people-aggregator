<?php
	global $_PA;
?>
<style>
.field {
	clear: both;
	overflow: auto;
	margin-bottom: 20px;
}
.field h4 {
	float: left;
	width: 120px;
	margin:0;
}
.field .center {
	float: left;
}
</style>
<div class="blog">
<form action="<?= PA::$url.PA_ROUTE_GROUP."?gid=".$mod->gid?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="op" value="edit_entity"/>
<input type="hidden" name="group_id" value="<?=$mod->gid?>"/>
<?= $mod->dynFields->hidden("name");?>
<?= $mod->dynFields->hidden("type");?>
<fieldset>
<?php
	if (@$mod->err) {
		echo '<div class="error">';
		echo _out($mod->err);
		echo '</div>';
	}
?>
<?php
foreach ($mod->profilefields as $i=>$field) {
	switch ($field['type']) {
		case 'stateselect':
			$mod->dynFields->select($field['label'], $field['name'], $_PA->states);
		break;
		case 'industryselect':
			$mod->dynFields->select($field['label'], $field['name'], $_PA->industries);
		break;
		case 'religionselect':
			$mod->dynFields->select($field['label'], $field['name'], $_PA->religions);
		break;
		case 'countryselect':
			$mod->dynFields->select($field['label'], $field['name'], $_PA->countries);
		break;
		case 'urltextfield':
			$mod->dynFields->textfield($field['label'], $field['name']);
		break;
		case 'textfield':
			$mod->dynFields->textfield($field['label'], $field['name']);
		break;
		case 'image':
			$mod->dynFields->image($field['label'], $field['name']);
		break;
		case 'dateselect':
			$mod->dynFields->dateselect($field['label'], $field['name']);
		break;
		default:
			echo print_r($field);
		break;
	}
}
?>
</fieldset>
<div class="button_position">
	<input type="submit" name="submit" value="<?=__("Save")?>"/>
</div>
</form>
</div>