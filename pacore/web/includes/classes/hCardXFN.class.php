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
/***************************************************************************
 *   Copyright (C) 2007 by Zoran Hron                                      *
 *   zhron@net.hr                                                          *
 *                                                                         * 
 *   hCardXFN parser for PHP5 v0.1.1 - a class for parsing hCard/XFN       *
 *   Microformats inspired with hKit Library for PHP5 written by:          *
 *                                                                         *
 *                                                   Drew McLellan         *
 *                                                                         *
 *   require:                                                              *  
 *                                                                         *
 *   - PHP 5 with simpleXML library                                        *
 *                                                                         *
 *   standard futures:                                                     *
 *                                                                         *
 *   - parsing hCard                                                       *
 *   - fn/n optimisation                                                   *
 *                                                                         *
 *   additional futures:                                                   *
 *                                                                         *
 *   - handle XFN relations                                                *
 *   - handle includes                                                     *
 *   - handle keys                                                         *
 *   - handle sounds                                                       *
 *   - added subclasses for "note" class:                                  *
 *                                                                         *
 *        'languages', 'awards', 'religion', 'ethnicity',                  *
 *        'political', 'passion', 'activities', 'books',                   *
 *        'movies', 'music', 'tv', 'food', 'summary'                       *
 *                                                                         *
 *  v0.1.1 changes:                                                        *
 *                                                                         *
 *   - removed n-optimisation bug                                          *
 *   - added new tag->attribute relation for fn class                      *
 *                                                                         *
 ***************************************************************************/
define('TIDY_URL', 'http://cgi.w3.org/cgi-bin/tidy?forceXML=on&docAddr=');

class hCardXFN {

    public $errors = array();

    public $current_card;

    private $domXML;

    private $url = false;

    protected $outData = array();

    protected $vcard_types = array(
        'personal',
        'professional',
    );

    protected $relation_types = array(
        'internal',
        'flickr',
        'facebooks',
        'others',
    );

    protected $root_classes = array(
        'org',
        'tel',
        'geo',
        'fn',
        'n',
        'adr',
        'email',
        'label',
        'bday',
        'agent',
        'nickname',
        'photo',
        'class',
        'category',
        'key',
        'logo',
        'mailer',
        'note',
        'tz',
        'uid',
        'url',
        'rev',
        'role',
        'sort-string',
        'sound',
        'title',
        'include',
    );

    protected $sub_classes = array(
        'org' => array(
            'organization-name',
            'organization-unit',
            'type',
        ),
        'tel' => array(
            'type',
            'value',
        ),
        'geo' => array(
            'latitude',
            'longitude',
        ),
        'fn' => array(
            'honorific-prefix',
            'given-name',
            'additional-name',
            'family-name',
            'honorific-suffix',
        ),
        'n' => array(
            'honorific-prefix',
            'given-name',
            'additional-name',
            'family-name',
            'honorific-suffix',
        ),
        'adr' => array(
            'post-office-box',
            'extended-address',
            'street-address',
            'postal-code',
            'country-name',
            'type',
            'region',
            'locality',
        ),
        'email' => array(
            'type',
            'value',
        ),
        'note' => array(
            'languages',
            'awards',
            'religion',
            'ethnicity',
            'political',
            'passion',
            'activities',
            'books',
            'movies',
            'music',
            'tv',
            'food',
            'summary',
            'college',
            'degree',
        ),
    );

    private $resolve_classes = array(
        'url',
        'photo',
        'logo',
    );

    private $attributes_map = array(
        'fn' => array(
            'IMG' => 'alt',
            'ABBR' => 'title',
        ),
        'url' => array(
            'A'    => 'href',
            'IMG'  => 'src',
            'AREA' => 'href',
        ),
        'photo' => array(
            'IMG' => 'src',
        ),
        'bday' => array(
            'ABBR' => 'title',
        ),
        'logo' => array(
            'IMG' => 'src',
        ),
        'email' => array(
            'A' => 'href',
        ),
        'geo' => array(
            'ABBR' => 'title',
        ),
        'type' => array(
            'ABBR' => 'title',
        ),
        'include' => array(
            'A' => 'href',
            'OBJECT' => 'data',
        ),
        'key' => array(
            'OBJECT' => 'type',
        ),
        'sound' => array(
            'OBJECT' => 'data',
        ),
    );
    //-------------- Public functions -----------------------------------------------------------------------------------------
    public function __construct() {
        $this->domXML = new DOMDocument();
        $this->domXML->preserveWhiteSpace = false;
    }

    public function getFromString($strHtml) {
        $strHtml = preg_replace('/<\?xml.+>/', ' ', $strHtml);
        @$this->domXML->loadHTML('<root>'.$strHtml.'</root>');
        if(!$this->domXML) {
            $this->setError(__("XML parsing error!"));
            return false;
        }
        $xpath = new DOMXPath($this->domXML);
        $vcards = @$xpath->query("//*[contains(@class,'vcard')]");
        // look up for hCards
        if(($vcards->length) == 0) {
            $this->setError(__("Data not found or XML parsing error!"));
            return false;
        }
        $cnt = 0;
        foreach($vcards as $vcard) {
            $this->current_card = &$this->outData[$cnt];
            $vcard_sxml         = simplexml_import_dom($vcard);
            $vcard_class        = explode(" ", $vcard_sxml['class']);
            if(((count($vcard_class) > 0) && (isset($vcard_class[1]))) && ((in_array($vcard_class[1], $this->vcard_types)) || (in_array($vcard_class[1], $this->relation_types)))) {
                $this->outData[$cnt]['type'] = $vcard_class[1];
                // store type of the current hCard
            }
            else {
                $this->outData[$cnt]['type'] = 'personal';
                // type not defined, we will use 'personal' as default
            }
            $this->outData[$cnt++]['value'] = $this->parseNode($vcard_sxml, $this->root_classes);
        }
        foreach($this->outData as $k => &$v) {
            $this->do_fn_from_n($v['value']);
            $this->do_n_from_fn($v['value']);
            $this->normalize_notes($v['value']);
        }
        $this->check_relations();
        return true;
    }

    public function getFromURL($url, $tidy = false) {
        $this->url = $url;
        if($tidy) {
            $url = TIDY_URL.$url;
            // use Tidy proxy service to clean HTML
        }
        $source = @file_get_contents($url);
        if($source) {
            return $this->getFromString($source);
        }
        $this->setError(__("Can't get data from URL. Please, check the given URL."));
        return false;
    }

    public function getFromFile($fname) {
        // file name with full path
        $fp = @fopen($fname, 'r');
        if(!$fp) {
            if(file_exists($fname)) {
                $this->setError(__("Can't open file '".$fname."'.  Check permissions."));
                return false;
            }
            else {
                $this->setError(__("File")." '".$fname."' ".__("doesn't exist."));
                return false;
            }
        }
        $str = @fread($fp, filesize($fname));
        @fclose($fp);
        if($str) {
            return $this->getFromString($str);
        }
        $this->setError(__("No data! Check if file is empty."));
        return false;
    }

    public function getCards() {
        return $this->outData;
    }

    public function countCards() {
        return count($this->outData);
    }

    public function countRelations() {
        $relations = 0;
        foreach($this->outData as $k => $v) {
            if(isset($v['rel_type'])) {
                ++$relations;
            }
        }
        return $relations;
    }

    public function getCardsByType($type) {
        $cards = array();
        if(!in_array($type, $this->vcard_types)) {
            return $cards;
        }
        // unknown type: return empty array
        foreach($this->outData as $k => $v) {
            if(($v['type'] == $type) && (!isset($v['rel_type']))) {
                $cards[$k] = $v;
            }
        }
        return $cards;
    }

    public function getRelations() {
        $cards = array();
        foreach($this->outData as $k => $v) {
            if(isset($v['rel_type'])) {
                $cards[$k] = $v;
            }
        }
        return $cards;
    }

    public function getErrors() {
        return $this->errors;
    }
    //-------------- Private functions --------------------------------------------------------------------------------------
    private function parseNode($node, $classes) {
        $r_data = array();
        $n_attr = false;
        for($i = 0; $i < count($classes); $i++) {
            // iterate trough all valid class names
            $xpath = ".//*[contains(concat(' ',normalize-space(@class),' '),' ".$classes[$i]." ')]";
            $results = $node->xpath($xpath);
            foreach($results as $result) {
                $class_names = explode(" ", $result['class']);
                // class attribute contains more than one class name?
                foreach($class_names as $class_name) {
                    $n_attr = false;
                    if(array_key_exists($class_name, $this->attributes_map)) {
                        $n_attr = $this->getAttributeValue($result, $class_name);
                        // look up for attributes
                        if($n_attr && ($class_name != "include")) {
                            $r_data[$class_name] = $n_attr;
                        }
                    }
                    if(isset($this->sub_classes[$class_name])) {
                        $sclasses = $this->sub_classes[$class_name];
                        // check node for sub-classes
                        $tmp_arr = $this->parseNode($result, $sclasses);
                        // recursive call -
                        if(isset($r_data[$class_name]) && is_array($r_data[$class_name])) {
                            if(count($tmp_arr) > 0) {
                                $r_data[$class_name] = array_merge($r_data[$class_name], $tmp_arr);
                            }
                            else {
                                array_push($r_data[$class_name], trim(preg_replace('/[\r\n\t]+/', ' ', implode(' ', $result->xpath('child::node()')))));
                            }
                        }
                        else {
                            if(count($tmp_arr) > 0) {
                                if($n_attr) {
                                    array_push($tmp_arr, $n_attr);
                                }
                                $r_data[$class_name] = $tmp_arr;
                            }
                            else {
                                if($n_attr) {
                                    $r_data[$class_name] = $n_attr;
                                }
                                else {
                                    $r_data[$class_name] = trim(preg_replace('/[\r\n\t]+/', ' ', implode(' ', $result->xpath('child::node()'))));
                                }
                            }
                        }
                    }
                    else {
                        if(!($class_name == 'include')) {
                            if($n_attr == false) {
                                $r_data[$class_name] = trim(preg_replace('/[\r\n\t]+/', ' ', implode(' ', $result->xpath('child::node()'))));
                            }
                        }
                        else {
                            $include_arr = $this->getAttributeValue($result, $class_name);
                            // handle includes
                            if(is_array($include_arr)) {
                                $r_data = array_merge($r_data, $include_arr);
                            }
                        }
                    }
                }
            }
        }
        return $r_data;
    }

    private function getAttributeValue($node, $class_name) {
        $tag_name = strtoupper($node->getName());
        $r_value = false;
        if($tag_name == 'DEL') {
            return $r_value;
        }
        // ignore DEL tags
        if(($tag_name == 'A' || $tag_name == 'OBJECT') && $class_name == 'include') {
            // check for include patterns
            return $this->handle_includes($node, $tag_name, $class_name);
        }
        if(($tag_name == 'OBJECT' && ($class_name == 'key' || $class_name == 'sound'))) {
            // check for key or sound pattern
            $r_value['value'] = trim(preg_replace('/[\r\n\t]+/', ' ', implode(' ', $node->xpath('child::node()'))));
            if(isset($node['type'])) {
                $r_value['type'] = trim((string) $node['type']);
            }
            if(isset($node['data'])) {
                $r_value['data'] = trim(preg_replace('/[\r\n\t\s]+/', '', (string) $node['data']));
            }
            return $r_value;
        }
        if(($tag_name == 'A' && $class_name == 'url') && isset($node['rel'])) {
            // check for XFN relations
            if(($node['rel'] != 'me') && ($node['rel'] != strtoupper('me'))) {
                $this->current_card['rel_type'] = trim((string) $node['rel']);
            }
        }
        if(($class_name == 'current') || ($class_name == 'prior')) {
            // check for organization type
            $this->current_card['org']['type'] = $class_name;
        }
        if(isset($this->attributes_map[$class_name][$tag_name])) {
            // look up attributes map values
            $nattr = $this->attributes_map[$class_name][$tag_name];
            if(isset($node[$nattr])) {
                $r_value = trim((string) $node[$nattr]);
            }
        }
        if($this->url && in_array($class_name, $this->resolve_classes)) {
            // parsing data from URL, maybe we need
            $r_value = $this->resolveURLs($r_value);
            // resolve URL
        }
        if($class_name == 'email') {
            $parts = parse_url($r_value);
            // resolve email address
            $r_value = $parts['path'];
        }
        return $r_value;
    }

    private function &handle_includes($node, $tag_name, $class_name) {
        if(isset($this->attributes_map[$class_name][$tag_name])) {
            // look up attributes map
            $tmp_arr = array();
            $nattr = $this->attributes_map[$class_name][$tag_name];
            if(isset($node[$nattr])) {
                $id       = str_replace('#', '', (string) $node[$nattr]);
                $xpattern = "//*[@id='$id']";
                $xpath    = new DOMXPath($this->domXML);
                $includes = $xpath->query($xpattern);
                // look up for includes
                foreach($includes as $include) {
                    $xml_str      = '<nodes>
                         <node>'.$this->domXML->saveXML($include).'</node>
                       </nodes>';
                    $include_sxml = new SimpleXMLElement($xml_str);
                    $tmp_arr      = $this->parseNode($include_sxml, $this->root_classes);
                }
            }
        }
        return $tmp_arr;
    }

    private function resolveURLs($value) {
        $r_val = $value;
        if(strpos($value, "://") === false) {
            // is relative URL?
            $url_data   = parse_url($this->url);
            $path_parts = pathinfo($url_data['path']);
            $r_val      = $url_data['scheme']."://".$url_data['host'].$path_parts['dirname'].'/'.$value;
        }
        return $r_val;
    }

    private function do_n_from_fn(&$vcard) {
        if(array_key_exists('fn', $vcard) && !is_array($vcard['fn']) && !array_key_exists('n', $vcard) && (!array_key_exists('org', $vcard) || $vcard['fn'] != $vcard['org'])) {
            $n_patterns = array(
                array(
                    '/^(\S+),\s*(\S{1})$/',
                    2,
                    1,
                ),
                // Lastname, Initial
                array(
                    '/^(\S+)\s*(\S{1})\.*$/',
                    2,
                    1,
                ),
                // Lastname Initial(.)
                array(
                    '/^(\S+),\s*(\S+)$/',
                    2,
                    1,
                ),
                // Lastname, Firstname
                array(
                    '/^(\S+)\s*(\S+)$/',
                    1,
                    2,
                )
                // Firstname Lastname,
            );
            $has_midd_name = '/^(\S+)\s*(\S+)\s*(\S+)$/';
            $n_count = count(explode(' ', $vcard['fn']));
            if($n_count == 2) {
                foreach($n_patterns as $pattern) {
                    if(preg_match($pattern[0], $vcard['fn'], $matches) === 1) {
                        $n                = array();
                        $n['given-name']  = $matches[$pattern[1]];
                        $n['family-name'] = $matches[$pattern[2]];
                        $vcard['n']       = $n;
                        break;
                    }
                }
            }
            else {
                if(preg_match($has_midd_name, $vcard['fn'], $matches) === 1) {
                    $n                    = array();
                    $n['given-name']      = $matches[1];
                    $n['additional-name'] = $matches[2];
                    $n['family-name']     = $matches[3];
                    $vcard['n']           = $n;
                }
            }
        }
    }

    private function do_fn_from_n(&$vcard) {
        if(array_key_exists('n', $vcard) && is_array($vcard['n']) && (!array_key_exists('fn', $vcard) || !isset($vcard['fn'])) && (!array_key_exists('org', $vcard) || $vcard['fn'] != $vcard['org'])) {
            $vcard['fn'] = implode(' ', $vcard['n']);
        }
        if(array_key_exists('fn', $vcard) && is_array($vcard['fn'])) {
            $fname = implode(' ', $vcard['fn']);
            $vcard['fn'] = $fname;
        }
    }

    private function normalize_notes(&$vcard) {
        if(array_key_exists('note', $vcard)) {
            // map all unamed notes into 'summary' field
            if(is_array($vcard['note'])) {
                foreach($vcard['note'] as $k => $v) {
                    if(is_numeric($k)) {
                        if(!isset($vcard['note']['summary'])) {
                            $vcard['note']['summary'] = '';
                        }
                        $vcard['note']['summary'] .= (strlen($vcard['note']['summary']) == 0) ? $v : (', '.$v);
                        unset($vcard['note'][$k]);
                    }
                }
            }
        }
    }

    private function check_relations() {
        foreach($this->outData as $k => &$v) {
            if(isset($v['rel_type'])) {
                if(!in_array($v['type'], $this->relation_types)) {
                    $v['type'] = 'others';
                }
            }
        }
    }

    private function setError($error) {
        array_push($this->errors, $error);
    }
}
?>
