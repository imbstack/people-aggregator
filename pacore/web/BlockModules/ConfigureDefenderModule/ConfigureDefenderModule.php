<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ConfigureDefenderModule.php is a part of PeopleAggregator.
* Displays all the rules that govern PA defender and allows the user to toggle
* which rules are used.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/

error_reporting(E_ALL);// require_once "api/...";
// require_once "web/includes/classes/...";
require_once 'web/includes/classes/PAForm.class.php';
require_once 'web/includes/classes/PADefender.class.php';
require_once 'api/PAException/PAException.php';

class ConfigureDefenderModule extends Module
{
    public $module_type = 'system|network';
    public $module_placement = 'middle';

    function __construct()
    {
        parent::__construct();
        $this->title = __('People Aggregator Defender setup');
        $this->outer_template = 'outer_public_group_center_module.tpl';
    }

    function __destruct() {
    }

    /** !!i
    * Handles page rendering
    * @todo: why does this not call { @link generate_inner_html() }?
    * generate_inner_html() is instead called directly from initializeModule()
    */
    function render()
    {
        $content = parent::render();
        return $content;
    }

    /** !!
    * create the html for the page
    */
    function generate_inner_html($template_vars = array())
    {
        $inner_html_gen = & new Template($this->inner_template);
        foreach ($template_vars as $name => $value)
        {
            if (is_object($value))
            {
                $inner_html_gen->set_object($name, $value);
            }
            else
            {
                $inner_html_gen->set($name, $value);
            }
        }
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }

    /** !!
    * Checks to see if the needed data from GET is available and if so call
    * { @link getRules() } to get all the rules for PA Defender and their
    * settings. Then call { @link set_inner_template } and 
    * { @link generate_inner_html() } to set up how to display the page.
    * @param string $request_method if this is GET create the page
    * @todo: $request_data is unused
    */
    function initializeModule($request_method, $request_data)
    {
       $defendObj = new XmlConfig(PA::$project_dir . '/config/defend_rules.xml', 'rules');
       $this->defend_rules = $defendObj->asArray();

        switch($request_method) {
          case 'GET' :
               $form = $this->getRules($this->defend_rules);
               $this->set_inner_template('show_defend_rules.tpl.php');
               $this->inner_HTML = $this->generate_inner_html(array('data' => $form,
                                                                    'message'  => null));
            break;
          case 'POST' :
            break;
          default:
            return "skip";
        }
    }

    /** !!
    * Generate a PAFrom ( @link $form} to contain all the PA Defender rules.
    * Call { @link populate_data() } to gath the rules and their current
    * status and place the corresponding data into { @link $form}.
    * @param array $rules collection of all PA Defender rules and their current
    *		statuses
    * @return PAForm $form PAForm containing all rules and their statuses
    */
    public function getRules($rules)
    {
        $form = new PAForm("form_data");
        $form->openTag('fieldset', array('class' => 'center_box'));
        $form->openTag('div', array('class' => 'field'));
        $this->populate_data($rules, $form);
        $form->closeTag('div');
        $form->addInputTag('hidden', array('name' => 'action', 'value' => 'saveDefendRules'));
        $form->addInputTag('submit', array('name' => 'submit', 'value' => "Save changes"));
        $form->closeTag('fieldset');
        return $form;
    }

    /** !!
    * Fill { @link $form } with all the html and css need for displaying the
    * PA Defender rules.
    * @param array $rules all rules and their statuses
    * @param PAForm $form the PAForm to be filled out be the method
    */
    private function populate_data($rules, &$form)
    {
        $form->openTag('table', array('class' => 'table', 'style' => 'margin-left: 12px; border: none'));
        $form->openTag('thead');
        $form->openTag('tr');
        $form->openTag('th');
        $form->addHtml(__("Rule"));
        $form->closeTag('th');
        $form->openTag('th');
        $form->addHtml(__("Status"));
        $form->closeTag('th');
        $form->closeTag('tr');
        $form->closeTag('thead');
        $form->openTag('tbody');
        $cnt = 0;
        foreach($rules as $rule) {
          $tag_name = "form_data[$cnt][active]";
          $tag_id   = "form_data_{$cnt}_active";
          $form->openTag('tr');
          $form->openTag('td');
          $form->addHtml(__($rule['description']));
          $form->closeTag('td');
          $form->openTag('td', array('style' => 'width: 92px'));
          $form->addContentTag('label', array('value' => 'On', 'style' => 'display: inline; color: #f00'));
          $form->addHtml("<input type='radio' value='1' name='$tag_name' id='{$tag_id}on' " . (($rule['active'] == 1) ? 'checked' : '') . " />");
          $form->addContentTag('label', array('value' => 'Off', 'style' => 'display: inline; color: #f00'));
          $form->addHtml("<input type='radio' value='0' name='$tag_name' id='{$tag_id}off' " . (($rule['active'] == 0) ? 'checked' : '') . " />");
          $form->closeTag('td');
          $form->closeTag('tr');
          $cnt++;
        }
        $form->closeTag('tbody');
        $form->closeTag('table');
    }

    /** !!
    * Handle request made to the server
    * @param string $request_method POST/GET/AJAX, says hom the server call is made
    * @param array $request_data tells the server what is requested based on
    *		according to the payload in ['action']
    */
    function handleRequest($request_method, $request_data)
    {
        if (!empty($request_data['action']))
        {
            $action = $request_data['action'];
            $class_name = get_class($this);
            switch ($request_method)
            {
            case 'POST':
                $method_name = 'handlePOST_'. $action;
                if (method_exists($this, $method_name))
                {
                    $this->{$method_name}($request_data);
                }
                else
                {
                    throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
                }
                break;
            case 'GET':
                $method_name = 'handleGET_'. $action;
                if (method_exists($this, $method_name))
                {
                    $this->{$method_name}($request_data);
                }
                else
                {
                    throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
                }
                break;
            case 'AJAX':
                $method_name = 'handleAJAX_'. $action;
                if (method_exists($this, $method_name))
                {
                    $this->{$method_name}($request_data);
                }
                else
                {
                    throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
                }
                break;
            }
        }
    }

    /** !!
    * Save the rules that have been selected for PA Defender to use.
    * After saving the rules update the page html to reflect the changes.
    * This is done with the same function calls used by { @link initializeModule()}
    * to generate page data and html.
    * @param array $request_data contains the form the carries all the data
    *		about rule settings
    */
    private function handlePOST_saveDefendRules($request_data)
    {
        global $error_msg;
        $msg = __("PADefender configuration data sucessfully stored.");

        $form_data = $request_data['form_data'];
        foreach($form_data as $key => $value) {
           $this->defend_rules[$key]['active'] = $value['active'];
        }

        try {
          unlink(PA::$project_dir . '/config/defend_rules.xml');
          $conf = new XmlConfig(PA::$project_dir . '/config/defend_rules.xml', 'rules');
          $conf->loadFromArray($this->defend_rules, $conf->root_node);
          $conf->saveToFile();
          $this->defend_rules = $conf->asArray();
        }
        catch (Exception $e) {
          $msg = $e->getMessage();
        }
        $form = $this->getRules($this->defend_rules);
        $this->set_inner_template('show_defend_rules.tpl.php');
        $this->inner_HTML = $this->generate_inner_html(array('data' => $form,
                                                             'message'  => null));
        $error_msg = $msg;
   }

    /** !!
    * Set the template for the page
    * @param string $template_fname name of the template to be used for the page
    */
    function set_inner_template($template_fname)
    {
        $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
    }
}
