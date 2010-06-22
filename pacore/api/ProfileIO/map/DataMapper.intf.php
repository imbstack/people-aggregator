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
/** 
* 
* @interface DataMapper
*
* @author     Zoran Hron <zhron@broadbandmechanics.com>
* @version    0.1.0
*
*
* @brief interface DataMapper
* 
*
**/
interface DataMapper {

    public static function processInData($data = array());

    public static function processOutData();
}
