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
<?php
foreach ($mod->availTypes as $type=>$label) {
	$label = $mod->availTypes[$type];
?>

<div class="blog">
<h1><a href="<?=PA::$url.PA_ROUTE_TYPED_DIRECTORY.$type?>"><?=$label?></a></h1>
	<div>
		<div style="float:right;">
			<form action="<?=PA::$url.'/'.FILE_ADDGROUP?>">
				<input type="hidden" name="entityType" value="<?=$type?>" />
				<input type="submit" name="submit" value="<?=
					sprintf(__("Create new %s"), $label)
					?>" />
			</form>
		</div>
	
		<?=sprintf(__("There are %s entries in this directory."), $mod->groupCount[$type])?>
	
	</div>
</div>
<? } ?>