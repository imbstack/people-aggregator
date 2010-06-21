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
<div class="blog">
<p><?=__("There is no TypedEntity associated with this Group yet. Would you like to create one?")?></p>
<form action="<?=PA::$url.PA_ROUTE_GROUP."?gid=".$mod->gid?>" method="post">
<input type="hidden" name="op" value="create_entity"/>
<fieldset>
<?php
	if(@$mod->err) {
    echo '<div class="error">';
    echo _out($mod->err);
    echo '</div>';
}
?>
<div class="field">
<label><h4><?=__("Type")?></h4></label>
<select name="type" id="type">
	<option value=""><?=__("--- Please select ---")?></option>
	<?php
	foreach($mod->availTypes as $key => $label) {
    echo "<option value=\"$key\"";
    if(@$mod->entity_type == $key) {
        echo ' selected="selected"';
    }
    echo ">$label</option>\n";
}
?>
</select>
</div>
<div class="field">
<label><h4><?=__("Name")?></h4></label>
<input type="text" name="name" value="<?=@$mod->entity_name?>" />
</div>
</fieldset>
<div class="button_position">
	<input type="submit" name="submit" value="<?=__("Create it")?>"/>
</div>
</form>
</div>