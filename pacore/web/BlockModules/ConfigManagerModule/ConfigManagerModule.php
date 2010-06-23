<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* ConfigManagerModule.php is a part of PeopleAggregator.
* @todo: what kind of configuration does this file govern?
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Zhoron Hron
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/

error_reporting(E_ALL);
require_once 'web/includes/classes/PAForm.class.php';
require_once 'api/PAException/PAException.php';

class ConfigManagerModule extends Module {

    public $module_type = 'system|network';
    public $module_placement = 'middle';
    private $section;
    private $sections = array('system_strings', 'blogging', 'basic_group_settings', 'basic_family_settings', 'typedgroups', 'basic_network_settings', 'network_defaults', 'site_related', 'ad_center', 'api_keys', 'database', 'debug');

    function __construct() {

        parent::__construct();
        $this->title = __('ConfigManager');
        $this->outer_template = 'outer_public_group_center_module.tpl';
    }

    function __destruct() {
    } 

    /** !!
    * Determins the config of the user, 
    * @param key $config_sect used as a key to select config sections
    * @param int $manage_mode determins if the user is a user or super user
    * @return PAForm $form returns the form to be used
    */
    public function getConfigSection($config_sect, $manage_mode) {

        global $app, $error_msg;
        if(!isset($this->sections[$config_sect])) {
            $error_msg = __('Section does not exist!');
            return "skip";
        }
        $section = $this->sections[$config_sect];
        $condition = '@readonly=\'false\'';
        if ($manage_mode == 1) {
        	// super user mode
        	$condition = null;
        }
        list($info, $data) = $app->configObj->getConfigSection($section, $condition);
        //echo "<pre>" . print_r($data,1) . "</pre>";
        $this->section = $section;
        $form = new PAForm("form_data[$section]");
        $form->addContentTag('h1', array('value'=>$info['description']));
        $form->openTag('div', array('class'=>'field'));
        $form->closeTag('div');
        $form->openTag('fieldset', array('class'=>'center_box'));
        foreach($data as $name=>$item) {
            $this->populate_data($item, $name, $form);
            //echo "<pre>" . print_r($item,1) . "</pre>";
        }
        $form->addInputTag('hidden', array('name'=>'action', 'value'=>'saveConfSection'));
        $form->addInputTag('hidden', array('name'=>'section', 'value'=>"{$this->section}"));
        $form->addInputTag('submit', array('name'=>'submit', 'value'=>"Save changes"));
        $form->closeTag('fieldset');
        return $form;
    }

    /** !!
    * Sets of the page information and CSS
    * @param array $item contains the various parts of the page
    * @param string $name name of the page
    * @param PAForm &$form the form used for the page
    */
    private function populate_data($item, $name, &$form) {

        static $keys = array();
        static $parent_type = null;
        $input_types = array('', 'radio', 'text', 'text', 'text');
        array_push($keys, "[$name]");
        if(is_array($item)&&!empty($item['attributes'])) {
            array_push($keys, "[value]");
            $len = 0;
            $inp_type = 0;
            $type = $item['attributes']['type'];
            $parent_type = (isset($item['attributes']['sub-type'])) ? $item['attributes']['sub-type'] : null;
            switch($type) {
                case 'expression':
                    $len += 120;
                    $inp_type += 1;
                case 'string':
                    $len += 360;
                    $inp_type += 1;
                case 'int':
                    $len += 48;
                    $inp_type += 1;
                case 'bool':
                    $len += 24;
                    $inp_type += 1;
                    if(is_numeric($item['value'])) {
                        $len = 72;
                    }
                    // override length for numeric values
                    $form->openTag('div', array('class'=>'field'));
                    $form->addContentTag('h2', array('value'=>ucwords(strtolower(str_replace('_', ' ', $name)))));
                    $tag_name = "form_data[$this->section]".implode('', $keys);
                    $tag_id = preg_replace('/[\[\]]+/', '_', implode('', $keys));
                    if($type == 'bool') {
                        $form->addContentTag('label', array('value'=>'On', 'style'=>'display: inline; color: #f00'));
                        $form->addHtml("<input type='radio' value='1' name='$tag_name' id='{$tag_id}on' ".(($item['value'] == 1) ? 'checked' : '')." />");
                        $form->addContentTag('label', array('value'=>'Off', 'style'=>'display: inline; color: #f00'));
                        $form->addHtml("<input type='radio' value='0' name='$tag_name' id='{$tag_id}off' ".(($item['value'] == 0) ? 'checked' : '')." />");
                    }
                    else {
                        $form->addInputTag($input_types[$inp_type], array('name'=>$tag_name, 'id'=>$tag_id, 'value'=>$item['value'], 'style'=>"width: {$len}px; border: 1px solid silver"));
                    }
                    if(isset($item['attributes']['description'])) {
                        $form->openTag('div', array('class'=>'text', 'style'=>'padding: 4px; margin-top: 4px; background-color: #ddd'));
                        $form->addHtml($item['attributes']['description']);
                        $form->closeTag('div');
                    }
                    $form->closeTag('div');
                    break;
                case 'array':
                case 'multi_array':
                    $form->openTag('div', array('class'=>'field'));
                    $form->addContentTag('h2', array('value'=>ucwords(strtolower(str_replace('_', ' ', $name)))));
                    if(is_array($item['value'])) {
                        $arr_keys = array_keys($item['value']);
                        $arr_vals = array_values($item['value']);
                        if(is_numeric($arr_keys[0])&&!(is_array($arr_vals[0]))||($parent_type == 'string')) {
                            $_val = implode("\n", $item['value']);
                            $tag_name = "form_data[$this->section]".implode('', $keys);
                            $tag_id = preg_replace('/[\[\]]+/', '_', implode('', $keys));
                            $form->addInputTag('textarea', array('name'=>$tag_name, 'id'=>$tag_id.'value', 'value'=>$_val, 'style'=>'border: 1px solid silver'));
                        }
                        else {
                            array_walk($item['value'], array($this, 'populate_data'), $form);
                        }
                        if(isset($item['attributes']['description'])) {
                            $form->openTag('div', array('class'=>'text', 'style'=>'padding: 4px; margin-top: 4px; background-color: #ddd'));
                            $form->addHtml($item['attributes']['description']);
                            $form->closeTag('div');
                        }
                    }
                    $form->closeTag('div');
                    break;
                default:
                    throw new Exception('Unknown type of variable.');
            }
            array_pop($keys);
        }
        else {
            if(is_array($item)) {
                $_keys = array_keys($item);
                $_values = array_values($item);
                $form->addContentTag('h3', array('value'=>ucwords(strtolower(str_replace('_', ' ', $name)))));
                array_walk($item, array($this, 'populate_data'), $form);
                if(isset($item['attributes']['description'])) {
                    $form->openTag('div', array('class'=>'text small'));
                    $form->addHtml($item['attributes']['description']);
                    $form->closeTag('div');
                }
            }
            else {
                if($parent_type) {
                    $len = 0;
                    $inp_type = 0;
                    $type = $parent_type;
                    switch($type) {
                        case 'expression':
                            $len += 120;
                            $inp_type += 1;
                        case 'string':
                            $len += 360;
                            $inp_type += 1;
                        case 'int':
                            $len += 48;
                            $inp_type += 1;
                        case 'bool':
                            $len += 24;
                            $inp_type += 1;
                            $tag_name = "form_data[$this->section]".implode('', $keys);
                            $tag_id = preg_replace('/[\[\]]+/', '_', implode('', $keys));
                            if (is_numeric($item)) {
                            	// override length for numeric values {
                              $len = 72;
                            }
                            $form->addContentTag('label', array('value'=>ucwords(strtolower(str_replace('_', ' ', $name))), 'style'=>'display: block; color: #35558c'));
                            if ($type == 'bool') {
                                $form->addContentTag('label', array('value'=>'On', 'style'=>'display: inline; color: #f00'));
                                $form->addHtml("<input type='radio' value='1' name='$tag_name' id='{$tag_id}on' ".(($item == 1) ? 'checked' : '')." />");
                                $form->addContentTag('label', array('value'=>'Off', 'style'=>'display: inline; color: #f00'));
                                $form->addHtml("<input type='radio' value='0' name='$tag_name' id='{$tag_id}off' ".(($item == 0) ? 'checked' : '')." />");
                            } else {
                                $form->addInputTag($input_types[$inp_type], array('name'=>$tag_name, 'id'=>$tag_id, 'value'=>$item, 'style'=>"width: {$len}px; border: 1px solid silver"));
                            }
                            $form->addHtml('<br />');
                            break;
                        default:
                    }
                }
                else {
                    $tag_name = "form_data[$this->section]".implode('', $keys);
                    $tag_id = preg_replace('/[\[\]]+/', '_', implode('', $keys));
                    $len = (is_numeric($item)) ? 72 : 400;
                    // override length for numeric values
                    $form->addContentTag('label', array('value'=>ucwords(strtolower(str_replace('_', ' ', $name))), 'style'=>'display: block; color: #35558c'));
                    $form->addInputTag('text', array('name'=>$tag_name, 'id'=>$tag_id, 'value'=>$item, 'style'=>"width: {$len}px; border: 1px solid silver"));
                    $form->addHtml('<br />');
                }
            }
        }
        array_pop($keys);
    }

    /** !!
    * Handles request to the server
    * @param string $request_method POST/GET/AJAX
    * @param array $request_data holder for the data requested from the server
    */
    function handleRequest($request_method, $request_data) {

        if(!empty($request_data['action'])) {
            $action = $request_data['action'];
            $class_name = get_class($this);
            switch($request_method) {
                case 'POST':
                    $method_name = 'handlePOST_'.$action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request.");
                    }
                    break;
                case 'GET':
                    $method_name = 'handleGET_'.$action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request.");
                    }
                    break;
                case 'AJAX':
                    $method_name = 'handleAJAX_'.$action;
                    if(method_exists($this, $method_name)) {
                        $this-> {
                            $method_name
                        }($request_data);
                    }
                    else {
                        throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request.");
                    }
                    break;
            }
        }
    }

    /** !!
    * Updates the data in the page
    * @param array &$value updated values
    * @param array $key keys to be updates
    */
    private function recursive_update_data_object(&$value, $key) {

        global $app;
        static $keys = array();
        $configData = &$app->configData['configuration'];
        array_push($keys, "['$key']");
        if(is_array($value)) {
            array_walk($value, array($this, 'recursive_update_data_object'));
        }
        else {
            $path = implode("", $keys);
            $node_path = $keys;
            $node_key = trim(array_pop($node_path), "[]'");
            // get node entry path
            $node_root = implode("", $node_path);
            if(eval("if(array_key_exists('$node_key', \$configData$node_root)) return true; return false;")) {
                $var_value = $value;
                $attr_path = implode("", $node_path)."['@attributes']";
                $attribs = eval("return ((isset(\$configData$attr_path)) ? \$configData$attr_path : null);");
                if($attribs&&isset($attribs['sub-type'])) {
                    // check for array of strings
                    $var_value = explode("\n", $value);
                    foreach($var_value as &$v_val) {
                        $v_val = trim($v_val);
                    }
                }
                if(!eval("\$configData$path = \$var_value; return true;")) {
                    throw new PAException(INVALID_ARGUMENTS, "Configuration data path - \"$path\" is invalid!");
                }
            }
            else {
                throw new PAException(INVALID_ARGUMENTS, "Configuration data path - \"$node_root\" not found!");
            }
        }
        array_pop($keys);
    }

    /** !!
    * saves the setting selected by the user
    * @param array $request_data information to be saved
    */
    private function handlePOST_saveConfSection($request_data) {

        global $app, $error_msg;
        $msg = __("Configuration data sucessfully stored.");
        $this->section = $request_data['section'];
        $form_data = $request_data['form_data'];
        $this->recursive_update_data_object($form_data[$this->section], "{$this->section}']['value");
        try {
            unlink(PA::$project_dir.APPLICATION_CONFIG_FILE);
            $conf = new XmlConfig(PA::$project_dir.APPLICATION_CONFIG_FILE, 'application');
            $conf->loadFromArray($app->configData, $conf->root_node);
            $conf->saveToFile();
            $app->configData = $conf->asArray();
            $app->configObj = $conf;
        }
        catch(Exception$e) {
            $msg = $e->getMessage();
        }
        $form = $this->getConfigSection($this->config_sect, $this->manage_mode);
        $this->set_inner_template('show_config_section.tpl.php');
        $this->inner_HTML = $this->generate_inner_html(array('data'=>$form, 'mode_tag'=>$this->mode_tag, 'sect_tag'=>$this->sect_tag, 'message'=>null));
        $error_msg = $msg;
    }

    /** !!
    * Get the template for the page
    * @param string $template_fname name for the template to be used
    */
    function set_inner_template($template_fname) {

        $this->inner_template = PA::$blockmodule_path.'/'.get_class($this)."/$template_fname";
    }

    function render() {

        $content = parent::render();
        return $content;
    }

    function generate_inner_html($template_vars = array()) {

        $inner_html_gen = &new Template($this->inner_template);
        foreach($template_vars as $name=>$value) {
            if(is_object($value)) {
                $inner_html_gen->set_object($name, $value);
            }
            else {
                $inner_html_gen->set($name, $value);
            }
        }
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }

    /** !!
    * Sets up module appropriate based on the inputs
    * @param array $request_method displays module if this is GET
    * @param array $request_data contains the data for setting up the module
    * @return string returns 'skip' is $request_method is neither GET nor POST
    */
    function initializeModule($request_method, $request_data) {

        $this->manage_mode = (isset($request_data['mmode'])) ? $request_data['mmode'] : 0;
        $this->config_sect = (isset($request_data['sect'])) ? $request_data['sect'] : 0;
        $this->mode_tag = xHtml::selectTag(array('normal'=>'0', 'expert'=>'1'), array('id'=>'mmode', 'name'=>'mmode'), $this->manage_mode);
        $this->sect_tag = xHtml::selectTag(array_flip($this->sections), array('id'=>'sect', 'name'=>'sect'), $this->config_sect);
        switch($request_method) {
            case 'GET':
                $form = $this->getConfigSection($this->config_sect, $this->manage_mode);
                $this->set_inner_template('show_config_section.tpl.php');
                $this->inner_HTML = $this->generate_inner_html(array('data'=>$form, 'mode_tag'=>$this->mode_tag, 'sect_tag'=>$this->sect_tag, 'message'=>null));
                break;
            case 'POST':
                break;
            default:
                return "skip";
        }
    }
}
