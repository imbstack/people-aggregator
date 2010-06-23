<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>

<style>
.field {
	clear: both;
}
.field .center {
	width: 320px !important;
}
</style>

<?php
$op = (empty($mod->entity)) ? "create_entity" : "edit_entity";
?>
<input type="hidden" name="op" value="<?=$op?>" />
<input type="hidden" name="group_id" value="<?=$mod->gid?>"/>
<?= $mod->dynFields->hidden("name");?>

<?php
	if (@$mod->err) {
		echo '<div class="error">';
		echo _out($mod->err);
		echo '</div>';
	}
?>
<?php
$type = $mod->dynFields->getVal('type');
if (empty($type)) {
	// $mod->dynFields->select(__("Entity Type"), 'type', $mod->selectTypes);
} else {
	$mod->dynFields->hidden("type");
  ?>
      <div class="field">
        <h4><label><?=__("Entity Type")?></label></h4>
        <div class="center">
        <?= (@$mod->availTypes[$mod->dynFields->getVal('type')]) ?
        $mod->availTypes[$mod->dynFields->getVal('type')] 
        : $mod->dynFields->getVal('type')?>
        </div>
      </div>
		<br style="clear:both" />
  <?php
	foreach ($mod->profilefields as $i=>$field) {
		switch ($field['type']) {
			case 'stateselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::getStatesList());
			break;
			case 'industryselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::$config->industries);
			break;
			case 'religionselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::$config->religions);
			break;
			case 'countryselect':
				$mod->dynFields->select($field['label'], $field['name'], PA::getCountryList());
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
}

?>
