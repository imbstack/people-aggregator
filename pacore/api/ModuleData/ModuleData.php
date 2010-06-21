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
 * Base Class for getting the block module image.
 *
 * 
 * @package ModuleData
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */
include_once dirname(__FILE__)."/../../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";

/**
* Class ModuleData for getting the block module image from the database in the system.
*
* @package ModuleData
* @author Tekriti Software
*/
class ModuleData {

    /**
    * The default constructor for ModuleData class.
    */
    public function __construct() {
        return;
    }

    /** This function svae the image path, 
    * image url etc store in serailze form in data field of  table ModuleData */
    static

    function save($data, $modulename) {
        Logger::log("Enter: function ModuleData::save()");
        $time = mktime(0, 0, 0, date("m"), date("d"), date("y"));
        $sql = "INSERT INTO {moduledata} (data, modulename, created, changed) VALUES
       (?, ?, ?, ?) ";
        $data = array(
            $data,
            $modulename,
            $time,
            $time,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Enter: function ModuleData::save");
        return;
    }

    /* This function update the image path, image url etc  store in serailze form in data column of ModuleData */
    static

    function update($data_image, $id_or_modulename) {
        Logger::log("Enter: function ModuleData::update()");
        $key_column = is_int($id_or_modulename) ? "id" : "modulename";
        $time       = mktime(0, 0, 0, date("m"), date("d"), date("y"));
        $sql        = "UPDATE  {moduledata} SET data = ?, changed = ? WHERE $key_column = ?";
        $data = array(
            $data_image,
            $time,
            $id_or_modulename,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Enter: function ModuleData::update()");
        return;
    }

    /* This function get  the image path,image url  etc    from  data field of ModuleData in the form array */
    static

    function get($module_name) {
        Logger::log("Enter: function ModuleData::get");
        $sql = "SELECT  data AS data FROM {moduledata}  WHERE modulename LIKE ? ";
        $data = array(
            $module_name,
        );
        $res = Dal::query($sql, $data);
        if($res->numRows() > 0) {
            $row = $res->fetchRow(DB_FETCHMODE_OBJECT);
            Logger::log("Exit: function ModuleData::get");
            return $row->data;
        }
    }
}
?>