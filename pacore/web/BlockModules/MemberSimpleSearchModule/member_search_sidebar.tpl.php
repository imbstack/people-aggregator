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
<div>
<form action="<?= PA::$url . PA_ROUTE_PEOPLES_PAGE?>">
<input style="width:auto;" type="text" name="allfields" value="<?=@$_REQUEST['allfields']?>" /><br/>
<input type="submit" name="submit_search" value="<?=__("find People")?>"/>
</form>
</div>