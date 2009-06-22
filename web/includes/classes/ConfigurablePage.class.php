<?php


require_once "web/includes/classes/XmlConfig.class.php";

/**
 *
 * @class ConfigurablePageException
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
 class ConfigurablePageException extends Exception {

    public function __construct($message, $code = 0) {
      parent::__construct('ConfigurablePageException: ' . $message, $code);
    }
 }
 
/**
 *
 * @class ConfigurablePage
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
 class ConfigurablePage extends XmlConfig {
 
  private $config_dir;
  public  $page_id;
  
  public function __construct($page_id, $config_dir, $old_conf_array = array()) {  // old $settings_new var
    $this->page_id    = $page_id;
    $this->config_dir = $config_dir;
    $pages = array_flip(getConstantsByPrefix('PAGE_'));                           // function defined in helper_functions.php
//    ksort($pages, SORT_NUMERIC);
    if(isset($pages[$page_id]) && ($page_id != 0)) {
      $config_file = strtolower(substr($pages[$page_id], 5)) . '.xml';  // already defind page name constant with removed 'PAGE_' prefix
    } else if(!isset($pages[$page_id]) && ($page_id != 0)) {
      throw new ConfigurablePageException("Page name constant not defined for page with this ID:" . $page_id .' in constants.php file!' . '<br />' .
                                          "If this ID number represents an existing dynamic page, please define a page name constant" . '<br />' .
                                          'in constants.php file.');
    } else {
      $config_file = 'new_page.xml';  // fake name
    }

    $filename = $this->config_dir . "/$config_file";
    if(file_exists(PA::$project_dir . DIRECTORY_SEPARATOR . $filename)) {
      $this->xml_file = PA::$project_dir . DIRECTORY_SEPARATOR . $filename;
    } else if (file_exists(PA::$core_dir . DIRECTORY_SEPARATOR . $filename)) {
      $this->xml_file = PA::$core_dir . DIRECTORY_SEPARATOR . $filename;
    } else {
      $this->xml_file = PA::$project_dir . DIRECTORY_SEPARATOR . $filename;
    }
    
    parent::__construct(null, 'root');
    
    if(!$this->docLoaded) {
//       $this->importFromOldConfig($old_conf_array);
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

  private function importFromOldConfig($pages_conf_array) {
    if(count($pages_conf_array) > 0) {
      $this->loadFromArray($pages_conf_array, $this->root_node, "page");
      if(!$this->docLoaded) {
         throw new ConfigurablePageException("Can't load pages configuration data!");
      }
      $simpleXML = new SimpleXMLElement($this->saveXML());
      foreach ($simpleXML->xpath('//page') as $page) {
        if($page->page_id == $this->page_id) {
          $this->loadXML($page->asXML());
          $this->modified = true;
        }
      }
    } 
  }

  public function asArray()  {
    $res = parent::asArray();
    
    $data = $res['data'];
    unset($res['data']);
    $res = array_merge($res, $data);
    
    return $res;
  }
  
 }

?>