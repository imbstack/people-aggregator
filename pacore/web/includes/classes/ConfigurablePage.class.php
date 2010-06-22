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
require_once "web/includes/classes/XmlConfig.class.php";

/**
 *
 * @class ConfigurablePage
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
class ConfigurablePage extends XmlConfig {

    private $config_dir;

    public $page_id;

    public function __construct($page_id, $config_dir, $old_conf_array = array()) {
        // old $settings_new var
        global $app;
        $this->page_id    = $page_id;
        $this->config_dir = $config_dir;
        $pages            = array_flip($app->configObj->query("//*[@section='pages']"));
        if(isset($pages[$page_id]) && ($page_id != 0)) {
            $config_file = strtolower(substr($pages[$page_id], 5)).'.xml';
            // already defind page name constant with removed 'PAGE_' prefix
        }
        elseif(!isset($pages[$page_id]) && ($page_id != 0)) {
            throw new ConfigurablePageException("Page name constant not defined for page with this ID:".$page_id.' in constants.php file!'.'<br />'."If this ID number represents an existing dynamic page, please define a page name constant".'<br />'.'in constants.php file.');
        }
        else {
            $config_file = 'new_page.xml';
            // fake name
        }
        $filename = $this->config_dir."/$config_file";
        if(file_exists(PA::$project_dir.DIRECTORY_SEPARATOR.$filename)) {
            $this->xml_file = PA::$project_dir.DIRECTORY_SEPARATOR.$filename;
        }
        elseif(file_exists(PA::$core_dir.DIRECTORY_SEPARATOR.$filename)) {
            $this->xml_file = PA::$core_dir.DIRECTORY_SEPARATOR.$filename;
        }
        else {
            $this->xml_file = PA::$project_dir.DIRECTORY_SEPARATOR.$filename;
        }
        parent::__construct(null, 'root');
        if(!$this->docLoaded) {
            //       if(!$this->modified) { // do nothing because no page settings loaded - so, this is a new page
            $this->loadXML('<page></page>');
            $file_info = pathinfo($config_file);
            $this->initialize(array('page_name' => $file_info['filename']));
            $this->saveToFile();
            $this->docLoaded = true;
            $this->modified = true;
            //       }
        }
    }

    public function __destruct() {
        if($this->modified) {
            $this->saveToFile();
        }
        parent::__destruct();
    }

    public function asArray() {
        $res = parent::asArray();
        $data = $res['data'];
        unset($res['data']);
        $res = array_merge($res, $data);
        return $res;
    }
}

/**
 *
 * @class ConfigurablePageException
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
class ConfigurablePageException extends Exception {

    public function __construct($message, $code = 0) {
        parent::__construct('ConfigurablePageException: '.$message, $code);
    }
}
?>