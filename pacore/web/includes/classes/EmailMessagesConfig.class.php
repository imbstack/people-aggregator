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
 * @class EmailMessagesConfig
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *             Jan. 2009.
 */
class EmailMessagesConfig extends XmlConfig {

  public $messages;
  
  public function __construct($filename = null, $default_root = 'email_messages') {
    parent::__construct($filename, $default_root);
    $this->messages = array();
    if($this->docLoaded) { 
      $this->messages = parent::asArray();
    }
  }
  
  public function exportMessages($file_name) {
    $obj = new XmlConfig($file_name, "email_messages");
    $obj->loadFromArray($this->messages, $obj->root_node);
    $obj->saveToFile();
  }

  public function asArray() {
    $messages = $this->messages;
    foreach($messages as $type => &$data) {
      $maches = array();
      if(preg_match("#\<\!\[CDATA\[(.*)\]\]\>#s", trim($data['subject']), $matches)) {
        $data['subject'] = $matches[1];
      }  
      if(preg_match("#\<\!\[CDATA\[(.*)\]\]\>#s", trim($data['message']), $matches)) {
        $data['message'] = $matches[1];
      }  
    }
    return $messages;
  }

  
  
  public function getAllMessages() {
    return $this->messages;
  }

  public function __destruct() {
  }
  
}

?>