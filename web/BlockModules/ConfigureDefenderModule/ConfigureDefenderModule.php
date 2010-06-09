<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ConfigureDefenderModule.php is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
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

    function initializeModule($request_method, $request_data)
    {
       $defendObj = new XmlConfig(PA::$project_dir . '/web/config/defend_rules.xml', 'rules');
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


    private function handlePOST_saveDefendRules($request_data)
    {
        global $error_msg;
        $msg = __("PADefender configuration data sucessfully stored.");

        $form_data = $request_data['form_data'];
        foreach($form_data as $key => $value) {
           $this->defend_rules[$key]['active'] = $value['active'];
        }

        try {
          unlink(PA::$project_dir . '/web/config/defend_rules.xml');
          $conf = new XmlConfig(PA::$project_dir . '/web/config/defend_rules.xml', 'rules');
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

    function set_inner_template($template_fname)
    {
        $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
    }

    function render()
    {
        $content = parent::render();
        return $content;
    }

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
}
