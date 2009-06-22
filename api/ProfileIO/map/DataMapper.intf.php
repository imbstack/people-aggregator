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