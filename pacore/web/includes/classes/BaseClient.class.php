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
 * @class BaseClient
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 * @brief Abstract class BaseClient
 * 
 *
 **/

abstract class BaseClient {

    // Extending class should define these methods
    abstract public function connect();
    abstract public function disconnect();
    abstract public function send($data, $encoded = true);
    abstract public function getResponse();
}