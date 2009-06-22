<?php

/**
 *
 * @class XmlConfigException
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
 
 class XmlConfigException extends Exception {

    public function __construct($message, $fnc_name) {
      parent::__construct("XmlConfig::$fnc_name() - " . $message);
    }
 }

/**
 *
 * @class XmlConfig
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 *
 */
class XmlConfig extends DOMDocument {
	public $xml_file;
	protected $default_root;
  protected $modified = false;
	public  $root_node = false;
	public  $docLoaded = false;
	public  $isUpdated = false; 
	private $level = 0;
  
	public function __construct($filename = null, $default_root = 'root') {
		parent::__construct();
    if($filename) {
      $this->xml_file = $filename;
    }  
		$this->formatOutput = true;
		$this->preserveWhiteSpace = false;
		$this->default_root = $default_root;
		if($this->xml_file) $this->load($this->xml_file);
	}

  public function __destruct() {
  }

  public function load($filename) {
		if(file_exists($filename))	{
			$this->docLoaded = parent::load($filename);
			if($this->docLoaded) {
				$this->root_node = $this->firstChild;
			}
			return $this->docLoaded;
		} else {
			if(!$this->docLoaded && !$this->root_node) {
				$this->root_node = $this->appendChild($this->createElement($this->default_root));
			}
		}
	}
  
  public function addConfigSection($section, $path) {
    $query = $path;
    $xpath = new DOMXPath($this);
    $entries = $xpath->query($query);
    if(($entries->length == 1)) {
      $node = $entries->item(0);
    } else {
      throw new XmlConfigException("XML node not found. XPath: '$path', data section: '$section'", "addConfigSection");
    }
    $node->appendChild($this->createElement($section));
    $this->modified = true;
  }

  public function hasConfigSection($section, $path) {
    $query = "$path/$section";
    $xpath = new DOMXPath($this);
    $entries = $xpath->query($query);
   
    if($entries->length > 0) {
      return true;
    }
    return false;
  }

  public function hasDataValue($name, $value, $path) {
    $query = "$path/$name";
    if($value) {
      $query .="[. = '$value']";
    }  
    
    $xpath = new DOMXPath($this);
    $entries = $xpath->query($query);
    
    if($entries->length == 1) {
      return true;
    }
    return false;
  }

  public function addConfigData($name, $value, $path, $isCData = false) {
     if(!$this->hasDataValue($name, ((!$isCData) ? $value : null), $path)) { // CDATA can't be compared in XPath expressoion
       $query = $path;
       $xpath = new DOMXPath($this);
       $entries = $xpath->query($query);
       if(($entries->length == 1)) {
         $node = $entries->item(0);
       } else {
         throw new XmlConfigException("XML node not found. XPath: '$path'", "addConfigSection");
       }
       $element = $this->createElement($name);
       if($isCData) {
         $node_value = $this->createCDATASection($value);
       } else {
         $node_value = $this->createTextNode($value);
       }
       $element->appendChild($node_value);
       $node->appendChild($element);
       $this->modified = true;
     }
//    echo "$query<pre>".print_r(htmlspecialchars($this->saveXML()),1)."</pre>";
  }

  public function getConfigData($name, $path) {
    $query = "$path/$name";
    $xpath = new DOMXPath($this);
    $entries = $xpath->query($query);
    if(($entries->length == 1)) {
      $matches = array();
      $node = $entries->item(0);
      if(preg_match("#\<\!\[CDATA\[(.*)\]\]\>#s", trim($node->nodeValue), $matches)) {
        $node_value = trim($matches[1]);
      } else {
        $node_value = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
      }
      $data  = ($name != 'item') ? $node_value : array($node_value);
      return $data;
    } else if($entries->length > 1) {
      $data_arr = array();
      foreach($entries as $node) {
        $data_arr[] = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
      }
      return $data_arr;
    }
    return null;
  }
  
  public function removeConfigSection($name, $path) {
    $this->removeConfigData($name, $path);
  }
  
  public function removeConfigData($name, $path, $value = null) {
    $query = "$path/$name";
    if($value) {
      $query .= "[. = \"$value\"]";
    }  
    $xpath = new DOMXPath($this);
    $entries = $xpath->query($query);
    
    if($entries->length == 1) {
      $node = $entries->item(0);
      $node->parentNode->removeChild($node);
      $this->modified = true;
    }
  }
  
  public function saveToFile($file_name = null) {
    if(!$file_name) {
      $file_name = $this->xml_file;
    }
    if(!$res = $this->save($file_name)) {
        echo "Can't save XML data to \"$file_name\" file!";
        die();
    }
 }

  public function loadFromArray($arr, &$n, $name="") {
    foreach ($arr as $key => $val) {
      $newKey = "item";
      if (is_int($key)) {
        if (strlen($name) > 1) {
         $newKey = ($this->level == 0) ? $name : "item";
        }
      }else{
        $newKey = $key;
      }

      $node = $this->createElement($newKey);
      if (is_array($val)){
        $this->level++;
        $this->loadFromArray($arr[$key], $node, $key);
        $this->level--;
      }else{
        $matches = array();
        if(preg_match("#\<\!\[CDATA\[(.*)\]\]\>#s", trim($val), $matches)) {
          $val = $matches[1];
          $nodeCData = $this->createCDATASection(trim($val));
          $node->appendChild($nodeCData);
        } else {
          $nodeText = $this->createTextNode($val);
          $node->appendChild($nodeText);
        }  
        $this->docLoaded = true;         // if any node has been created - DOC is loaded
      }
      $n->appendChild($node);
    }
  }

  protected function simpleXML_to_array($obj, &$input_arr = array()) {
    $obj_vars = get_object_vars($obj);
    if(isset($obj_vars['item'])) {
      $obj_vars = (is_array($obj_vars['item'])) ? $obj_vars['item'] : array($obj_vars['item']);
    }
    foreach($obj_vars as $key => $var) {
      if(is_object($var)) {
        if(count((array) $var) == 0) {
          $input_arr[$key] = null;
        } else {
          $this->simpleXML_to_array($var, $input_arr[$key]);
        }
      } else {
        if(!is_array($var)) {
          $matches = array();
          if(preg_match("#\<\!\[CDATA\[(.*)\]\]\>#s", trim($var), $matches)) {
            $var = trim($matches[1]);
          } 
        }  
        $input_arr[$key] = $var;
//        echo "Key: $key, value: <beg>$var</beg><br />";
      }
    }
  }
/*
  private function map_items(&$element) {
    if(is_array($element)) {
      if(isset($element['item'])) {
        if((count($element['item']) == 1) && ($element['item'] != null)) {
          $element = array($element['item']);
        } else {
          $element = $element['item'];
        }
      } else {
        array_walk($element, array($this, 'map_items'));
      }
    }
  }
*/  
  public function asArray()  {
    $res = array();
    $simple_xml = new SimpleXMLElement($this->saveXML(), LIBXML_NOCDATA);
    $this->simpleXML_to_array($simple_xml, $res);
//    array_walk($res, array($this, 'map_items'));
    return $res;
  }
}

?>