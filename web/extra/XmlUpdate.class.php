<?php

require_once "web/includes/classes/XmlConfig.class.php";
require_once "web/extra/db_update_page.class.php";
require_once 'db/Dal/Dal.php';

if(!defined('XML_UPDATES_FILE')) define('XML_UPDATES_FILE', dirname(__FILE__).'/XmlUpdates.xml');

class XmlUpdate extends XmlConfig {

   private $updates;
   private $db_update_obj;
   public function __construct($filename, $root_node, db_update_page $db_upd) {
       parent::__construct($filename, $root_node);
       $this->updates = new DOMDocument();
       $this->updates->formatOutput = true;
       $this->updates->preserveWhiteSpace = false;
       $this->updates->load(XML_UPDATES_FILE);
       $this->db_update_obj = $db_upd;
   }

//application/configuration

/**
* $args = array($path, $name, $value, $attributes = array(), $isCData = false, $replace = false)
*
**/


  public function add($path, $descr, $node, $replace = false) {
  
        if ($this->db_update_obj->is_applied($descr, NULL))
        {
            if (!$this->db_update_obj->quiet)
               $this->db_update_obj->note("XML config data patch - '$descr' - was already applied");
            return;
        }

       $query = $path;
       $xpath = new DOMXPath($this);
       
       $section = $xpath->query($query);
       if (($section->length == 1))
       {
          $parent_node = $section->item(0);
       }
       else
       {
          throw new XmlConfigException("Can't resolve XPath: '$path'", 'add', $this);
       }

       $query = "$path/$node->nodeName";
       $old_node = $xpath->query($query);
       if (($old_node->length > 0) && !$replace) {
          $this->db_update_obj->note("XML data entry for '$node->nodeName' can't be overwritten - use replace() function instead of add().");
       }
       
        if(!$replace) {
            $domNode = $this->importNode($node, true);
            $parent_node->appendChild($domNode);
        } else {
            $old_node = $old_node->item(0);        
            $domNode = $this->importNode($node, true);
            $parent_node->replaceChild($domNode, $old_node);
        }    
        $this->modified = true;
        $this->saveToFile();
        
        if (!$this->db_update_obj->quiet)
        {
           $this->db_update_obj->note("applying XML configuration data patch - $descr");
        }
        Dal::query('INSERT INTO mc_db_status SET stmt_key=?', Array($descr));
        
        return true;
   }
  
  public function replace($path, $descr, $node) {
      return $this->add($path, $descr, $node, true);
  }
  
  public function delete($path, $descr) {
       if ($this->db_update_obj->is_applied($descr, NULL))
       {
           if (!$this->db_update_obj->quiet)
              $this->db_update_obj->note("XML config data patch - '$descr' - already applied");
           return;
       }
        
       $query = $path;
       $xpath = new DOMXPath($this);
       
       $section = $xpath->query($query);
       if (($section->length == 1))
       {
          $node = $section->item(0);
       }
       else
       {
          throw new XmlConfigException("Can't resolve XPath: '$path'", 'delete', $this);
       }
       
       $node->parentNode->removeChild($node);
       $this->modified = true;
       $this->saveToFile();
       
       if (!$this->db_update_obj->quiet)
       {
           $this->db_update_obj->note("applying XML configuration data patch - $descr" . ($this->db_update_obj->running_on_cli ? (' (<a href="db_update.php?override='.htmlspecialchars($descr).'">override</a>)') : ''));
       }
       Dal::query('INSERT INTO mc_db_status SET stmt_key=?', Array($descr));
       
       return true;
  }
  
  public function run_updates() {
   
       $xpath = new DOMXPath($this->updates);
       
       $add_section = $xpath->query("//updates/add/*");
       $replace_section = $xpath->query("//updates/replace/*");
       $remove_section = $xpath->query("//updates/remove/*");

       if (($add_section->length > 0))
       {
          foreach($add_section as $item)
          {
            $path  = $item->getAttribute('path');
            $descr = $item->getAttribute('descr');
            if(empty($path) || empty($descr)) {
               throw new XmlConfigException("Error in XML update file: " . XML_UPDATES_FILE . "! Undefined attribute 'path' or 'descr' for node: " . $item->nodeName, 'run_updates', $this);
            }
            $node  = $item->firstChild;
            $this->add($path, $descr, $node);
          }  
       }

       if (($replace_section->length > 0))
       {
          foreach($replace_section as $item)
          {
            $path  = $item->getAttribute('path');
            $descr = $item->getAttribute('descr');
            if(empty($path) || empty($descr)) {
               throw new XmlConfigException("Error in XML update file: " . XML_UPDATES_FILE . "! Undefined attribute 'path' or 'descr' for node: " . $item->nodeName, 'run_updates', $this);
            }
            $node  = $item->firstChild;
            $this->replace($path, $descr, $node);
          }  
       }
  
       if (($remove_section->length > 0))
       {
          foreach($remove_section as $item)
          {
            $path  = $item->getAttribute('path');
            $descr = $item->getAttribute('descr');
            if(empty($path) || empty($descr)) {
               throw new XmlConfigException("Error in XML update file: " . XML_UPDATES_FILE . "! Undefined attribute 'path' or 'descr' for node: " . $item->nodeName, 'run_updates', $this);
            }
            $this->delete($path, $descr);
          }  
       }
  }
}

?>
