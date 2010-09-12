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
 * @class XmlConfig
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.2.0
 *
 */
class XmlConfig extends DOMDocument
{
    public $xml_file;
    protected $default_root;
    protected $modified = false;
    public $root_node = false;
    public $docLoaded = false;
    public $isUpdated = false;
    public $level;

    public function __construct($filename = null, $default_root = 'root')
    {
        $this->level = 0;
        parent::__construct();
        if ($filename)
        {
            $this->xml_file = $filename;
        }
        $this->formatOutput = true;
        $this->preserveWhiteSpace = false;
        $this->default_root = $default_root;
        if ($this->xml_file)
        {
            $this->load($this->xml_file);
        }
        else
        {
            $this->root_node = $this->appendChild($this->createElement($this->default_root));
        }
    }

    public function __destruct()
    {
    }

    public function load($filename, $options = 0)
    {
        if (file_exists($filename))
        {
            $this->docLoaded = parent::load($filename);
            if ($this->docLoaded)
            {
                $this->root_node = $this->firstChild;
            }
            return $this->docLoaded;
        }
        else
        {
            if (!$this->docLoaded && !$this->root_node)
            {
                $this->root_node = $this->appendChild($this->createElement($this->default_root));
            }
        }
    }

    public function addConfigSection($section, $path, $attributes = array())
    {
        $query = $path;
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if (($entries->length == 1))
        {
            $node = $entries->item(0);
        }
        else
        {
            throw new XmlConfigException("XML node not found. XPath: '$path', data section: '$section'", 'addConfigSection');
        }
        $new_section = $this->createElement($section);
        if (count($attributes) > 0)
        {
            foreach ($attributes as $name => $value)
            {
                $new_section->setAttribute($name, $value);
            }
        }
        $node->appendChild($new_section);
        $this->modified = true;
    }

    public function hasConfigSection($section, $path)
    {
        $query = "$path/$section";
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if ($entries->length > 0)
        {
            return true;
        }
        return false;
    }

    public function hasDataValue($name, $value, $path)
    {
        $query = "$path/$name";
        if ($value)
        {
            $query .="[. = '$value']";
        }
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if ($entries->length == 1)
        {
            return true;
        }
        return false;
    }

    public function addConfigData($name, $value, $path, $isCData = false, $attributes = array())
    {
        if (!$this->hasDataValue($name, ((!$isCData) ? $value : null), $path))
        {// CDATA can't be compared in XPath expressoion
            $query = $path;
            $xpath = new DOMXPath($this);
            $entries = $xpath->query($query);
            if (($entries->length == 1))
            {
                $node = $entries->item(0);
            }
            else
            {
                throw new XmlConfigException("XML node not found. XPath: '$path'", 'addConfigSection');
            }
            $element = $this->createElement($name);
            if ($isCData)
            {
                $node_value = $this->createCDATASection($value);
            }
            else
            {
                $node_value = $this->createTextNode($value);
            }
            $element->appendChild($node_value);
            if (count($attributes) > 0)
            {
                foreach ($attributes as $name => $value)
                {
                    $element->setAttribute($name, $value);
                }
            }
            $node->appendChild($element);
            $this->modified = true;
        }
    }

    public function getConfigData($name, $path)
    {
        $query = "$path/$name";
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if (($entries->length == 1))
        {
            $matches = array();
            $node = $entries->item(0);
            if (preg_match('#\<\!\[CDATA\[(.*)\]\]\>#s', trim($node->nodeValue), $matches))
            {
                $node_value = trim($matches[1]);
            }
            else
            {
                $node_value = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
            }
            $data = ($name != 'item') ? $node_value : array($node_value);
            return $data;
        }
        elseif ($entries->length > 1)
        {
            $data_arr = array();
            foreach ($entries as $node)
            {
                $data_arr[] = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
            }
            return $data_arr;
        }
        return null;
    }

    public function query($query)
    {
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if (($entries->length == 1))
        {
            $matches = array();
            $node = $entries->item(0);
            if (preg_match('#\<\!\[CDATA\[(.*)\]\]\>#s', trim($node->nodeValue), $matches))
            {
                $node_value = trim($matches[1]);
            }
            else
            {
                $node_value = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
            }
            $data = ($name != 'item') ? $node_value : array($node_value);
            return $data;
        }
        elseif ($entries->length > 1)
        {
            $data_arr = array();
            foreach ($entries as $node)
            {
                $data_arr[$node->nodeName] = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
            }
            return $data_arr;
        }
        return null;
    }

    public function getConfigSection($name = null, $condition = null)
    {
        $xpath = new DOMXPath($this);
        $section_info = array();
        if ($name)
        {
            $query ="//$name";
            $section = $xpath->query($query);
            if (($section->length == 1))
            {
                $section = $section->item(0);
                if ($section->hasAttributes())
                {
                    $sec_attr = array();
                    foreach ($section->attributes as $attr)
                    {
                        $sec_attr[$attr->name] = $attr->value;
                    }
                    $section_info = $sec_attr;
                    $section_info['name'] = $section->nodeName;
                }
            }
            else
            {
                throw new XmlConfigException("Data section '$name' undefined or multiple defined!", 'getConfigSection');
            }
            $query = "//*[@section='$name'";
            if ($condition)
            {
                $query .= " and $condition";
            }
            $query .= ']';
        }
        else
        {
            $query = "//*[$condition]";
        }
        $entries = $xpath->query($query);
        if (($entries->length == 1))
        {
            $matches = array();
            $node = $entries->item(0);
            if ($node->hasChildNodes())
            {
                $node_value = $this->deepGetNode($node);
            }
            else
            {
                $node_value = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
            }
            if ($node->hasAttributes())
            {
                $attributes = array();
                foreach ($node->attributes as $attr)
                {
                    $attributes[$attr->name] = $attr->value;
                }
                $node_value = (array_key_exists('value', $node_value)) ? $node_value['value'] : $node_value;
//                $node_value = (isset($node_value['value'])) ? $node_value['value'] : $node_value;
                $data_arr[$node->nodeName] = array('attributes' => $attributes, 'value' => $node_value);
            }
            else
            {
                $data_arr[$node->nodeName]['value'] = $node_value['value'];
            }
            return array($section_info, $data_arr);
        }
        elseif ($entries->length > 1)
        {
            $data_arr = array();
            foreach ($entries as $node)
            {
                if ($node->hasChildNodes())
                {
                    $node_value = $this->deepGetNode($node);
                }
                else
                {
                    $node_value = stripslashes(htmlspecialchars_decode(trim($node->nodeValue)));
                }
                if ($node->hasAttributes())
                {
                    $attributes = array();
                    foreach ($node->attributes as $attr)
                    {
                        $attributes[$attr->name] = $attr->value;
                    }
                    $node_value = (array_key_exists('value', $node_value)) ? $node_value['value'] : $node_value;
//                    $node_value = (isset($node_value['value'])) ? $node_value['value'] : $node_value;
                    $data_arr[$node->nodeName] = array('attributes' => $attributes, 'value' => $node_value);
                }
                else
                {
                    $data_arr[$node->nodeName]['value'] = $node_value['value'];
                }
            }
            return array($section_info, $data_arr);
        }
        return null;
    }

    function deepGetNode($n)
    {
        $return=array();
        foreach ($n->childNodes as $nc)
        {
            if ($nc->hasChildNodes() )
            {
                if ($n->firstChild->nodeName == $n->lastChild->nodeName && $n->childNodes->length > 1)
                {
                    $item = $n->firstChild;
                    if ($item->nodeName == 'item')
                    {
                        if ($item->hasChildNodes() )
                        {
                            $return[] = $this->deepGetNode($nc);
                        }
                        else
                        {
                            $return[] = stripslashes(htmlspecialchars_decode(trim($nc->nodeValue)));
                        }
                    }
                    else
                    {
                        $return[$nc->nodeName][] = $this->deepGetNode($item);
                    }
                }
                else
                {
                    $return[$nc->nodeName] = $this->deepGetNode($nc);
                }
            }
            else
            {
                if (preg_match('#\<\!\[CDATA\[(.*)\]\]\>#s', trim($nc->nodeValue), $matches))
                {
                    $node_value = trim($matches[1]);
                }
                else
                {
                    $node_value = stripslashes(htmlspecialchars_decode(trim($nc->nodeValue)));
                }
                $return = $node_value;
            }
        }
        return $return;
    }

    public function removeConfigSection($name, $path)
    {
        $this->removeConfigData($name, $path);
    }

    public function removeConfigData($name, $path, $value = null)
    {
        $query = "$path/$name";
        if ($value)
        {
            $query .= "[. = \"$value\"]";
        }
        $xpath = new DOMXPath($this);
        $entries = $xpath->query($query);
        if ($entries->length == 1)
        {
            $node = $entries->item(0);
            $node->parentNode->removeChild($node);
            $this->modified = true;
        }
    }

    public function saveToFile($file_name = null)
    {
        if (!$file_name)
        {
            $file_name = $this->xml_file;
        }
        if (!$res = $this->save($file_name))
        {
            echo "Can't save XML data to \"$file_name\" file!";
            die();
        }
    }

    public function loadFromArray($arr, &$n, $name='')
    {
        foreach ($arr as $key => $val)
        {
            $newKey = 'item';
            if (is_int($key))
            {
                if (strlen($name) > 1)
                {
                    $newKey = ($this->level == 0) ? $name : 'item';
                }
            }
            else
            {
                $newKey = $key;
            }
            if (($newKey == '@attributes'))
            {
                $node_attrs = $arr[$key];
                foreach ($node_attrs as $attr_name => $attr_value)
                {
                    $n->setAttribute($attr_name, $attr_value);
                }
                continue;
            }
            else
            {
                $node = $this->createElement($newKey);
            }
            if (is_array($val))
            {
                $this->level++;
                $this->loadFromArray($arr[$key], $node, $key);
                $this->level--;
            }
            else
            {
                $matches = array();
                if (preg_match('#\<\!\[CDATA\[(.*)\]\]\>#s', trim($val), $matches))
                {
                    $val = $matches[1];
                    $nodeCData = $this->createCDATASection(trim($val));
                    $node->appendChild($nodeCData);
                }
                else
                {
                    $nodeText = $this->createTextNode($val);
                    $node->appendChild($nodeText);
                }
                $this->docLoaded = true;// if any node has been created - DOC is loaded
            }
            $n->appendChild($node);
        }
    }

    protected function simpleXML_to_array($obj, &$input_arr = array())
    {
        $obj_vars = get_object_vars($obj);
        if (isset($obj_vars['item']))
        {
            $obj_vars = (is_array($obj_vars['item'])) ? $obj_vars['item'] : array($obj_vars['item']);
        }
        foreach ($obj_vars as $key => $var)
        {
            if (is_object($var))
            {
                if (count((array) $var) == 0)
                {
                    $input_arr[$key] = null;
                }
                else
                {
                    $this->simpleXML_to_array($var, $input_arr[$key]);
                }
            }
            else
            {
                if (!is_array($var))
                {
                    $matches = array();
                    if (preg_match('#\<\!\[CDATA\[(.*)\]\]\>#s', trim($var), $matches))
                    {
                        $var = trim($matches[1]);
                    }
                }
                $input_arr[$key] = $var;
            }
        }
    }

    public function asArray()
    {
        $res = array();
        $simple_xml = new SimpleXMLElement($this->saveXML(), LIBXML_NOCDATA | LIBXML_COMPACT);
        $this->simpleXML_to_array($simple_xml, $res);
        return $res;
    }

    public function __sleep()
    {
        $this->xml = $this->saveXML();
        return(array_keys(get_object_vars($this ) ) );
    }

    public function __wakeup()
    {
        $this->loadXML($this->xml);
    }
}

/**
 *
 * @class XmlConfigException
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.2.0
 *
 *
 */
class XmlConfigException extends Exception
{
    public function __construct($message, $fnc_name, $object = null)
    {
        if($object) 
        { 
          parent::__construct(get_class($object) . "::$fnc_name() - " . $message);
        } 
        else 
        {
          parent::__construct("XmlConfig::$fnc_name() - " . $message);
        }
    }
}
?>