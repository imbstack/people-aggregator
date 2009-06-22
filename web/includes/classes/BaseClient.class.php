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