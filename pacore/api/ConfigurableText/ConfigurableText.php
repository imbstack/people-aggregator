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

  include_once dirname(__FILE__)."/../../config.inc";
  // global var $path_prefix has been removed - please, use PA::$path static variable
  require_once "api/DB/Dal/Dal.php";
  require_once "api/PAException/PAException.php";
  require_once "api/Logger/Logger.php";
  
  class ConfigurableText {
  
      public $caption_value;
      
      public $caption;
      
      public $id;
      
      public function set_params ($param_array) {
          Logger::log("Enter: function ConfigurableText::set_params");
          
              foreach ( $param_array as $key => $value ) {
                  $this->$key = $value;
              }
              
          Logger::log("Enter: function ConfigurableText::set_params");
          return;
      }
      
      public function save () {
          Logger::log("Enter: function ConfigurableText::save");
              
              $condition = array('caption'=> $this->caption);
              $data_array = $this->load ($condition);
              
              if(count($data_array) != 0) {
                  // Caption already exists
                  Logger::log("Throwing exception CAPTION_NAME_EXISTS | Caption with the given name already exists", LOGGER_ERROR);
                  throw new PAException(CAPTION_NAME_EXISTS, "Caption with the given name already exists");
              }
              
              $sql = "INSERT INTO configurable_text (caption, caption_value) VALUES (?, ?)";
              $data = array ('caption'=> $this->caption, 'caption_value'=> $this->caption_value);
              $res = Dal::query($sql, $data);
              
          Logger::log("Enter: function ConfigurableText::save");
          return;                   
      }
      
      public function load ( $condition = NULL, $onlyCaptionValue = 0 ) {
          Logger::log("Enter: function ConfigurableText::load");
          
              $sql = "SELECT * FROM {configurable_text} WHERE 1 ";
              $data = array();
              if(count($condition) > 0) {
                  foreach( $condition as $key => $value ) {
                      $sql .= " AND $key = ?";
                      $data[] = $value;
                  }
              }
              
              $sql .= " ORDER BY id DESC";
              
              $res = Dal::query($sql, $data);
              $return = array();
              if ($res->numRows() > 0) {
                  if($onlyCaptionValue == 1) {
                       while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {                         
                          $return[$row->caption] = $row->caption_value;
                      }
                  
                  } else {
                      while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                          $return[] = array('id'=> $row->id, 'caption'=> $row->caption, $row->caption => $row->caption_value);
                      }
                  }
              }
              
          Logger::log("Enter: function ConfigurableText::load");
          return $return;
      }
      
      public function update () {
          Logger::log("Enter: function ConfigurableText::update");
          
              $sql = "SELECT * FROM {configurable_text} WHERE caption = ? AND id <> ?";
              $data = array('caption'=> $this->caption, 'id'=> $this->id);
              
              $res = Dal::query($sql, $data);
              
              if($res->numRows() == 0) {
                  $sql = "UPDATE {configurable_text} SET caption = ?, caption_value = ? WHERE id = ?"    ;
                  $data = array('caption'=> $this->caption, 'caption_value'=> $this->caption_value, 'id'=> $this->id);
                  
                  $res = Dal::query($sql, $data);
              
              } else {
                  // Caption already exists
                  Logger::log("Throwing exception CAPTION_NAME_EXISTS | Caption with the given name already exists", LOGGER_ERROR);
                  throw new PAException(CAPTION_NAME_EXISTS, "Caption with the given name already exists");
                  
              } 
          
          Logger::log("Enter: function ConfigurableText::update");
          return;
      }
      
  }

?>