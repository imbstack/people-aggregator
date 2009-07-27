<?php
require_once "api/Theme/Template.php";
require_once "web/includes/classes/PAForm.class.php";

class PAInstaller {
   const install_template = 'web/install/install.tpl';
   public  $error = false;
   public  $form_data;
   public  $config;
   public  $allow_network_spawning;
   private $steps = array(1 => array('title' => 'License arrangement', 'conf_section' => null, 'curr_status' => false),
                          2 => array('title' => 'Installation requirements test', 'conf_section' => null, 'curr_status' => false),
                          3 => array('title' => 'Database settings', 'conf_section' => 'database', 'curr_status' => false),
                          4 => array('title' => 'Populating the Database', 'conf_section' => null, 'curr_status' => false),
                    );

   private $curr_step = 0;

   public function __construct() {
      $this->config = array();
   }

   public function render ($template_vars, $template_path) {
     $template_file = getShadowedPath($template_path);
     $html = & new Template($template_file);
     foreach($template_vars as $name => $value) {
       if(is_object($value)) {
         $html->set_object($name, $value);
       } else {
         $html->set($name, $value);
       }
     }
     echo $html->fetch();
     exit;
   }

   public function run() {
      switch($_SERVER['REQUEST_METHOD']) {
          case 'GET':  $this->handleGET(); break;
          case 'POST': $this->handlePOST(); break;
          default:
      }
   }

   private function handlePOST() {
     if(isset($_POST['pa_inst'])) {
       $this->form_data = $_POST['pa_inst'];
     }

     $this->curr_step = (isset($_GET['step'])) ? $_GET['step'] : 1;

     $temp_vars = $this->buildForm('POST');
     $this->render($temp_vars, PAInstaller::install_template);
   }

   private function handleGET() {
     if(isset($_POST['pa_inst'])) {
       $this->form_data = $_POST['pa_inst'];
     }

     $step = (isset($_GET['step'])) ? $_GET['step'] : 1;
     if($step <= $this->curr_step) {  // reset error flag because user click 'Back'
       $this->error = false;
     }
     $this->curr_step = $step;

     $temp_vars = $this->buildForm('GET');
     $this->render($temp_vars, PAInstaller::install_template);
   }

   private function buildForm($server_method) {
     $method_name = $server_method . '_step_' . $this->curr_step;
     return $this->{$method_name}($this->steps[$this->curr_step]);
   }

   private function GET_step_1($params) {
     $accepted = (isset($this->form_data['accept'])) ? $this->form_data['accept'] : false;
     $html = "
      <center>
      <div>
        <iframe id='inst_licence' src='install/licence.txt.html' frameborder='0' style='font-family: Arial, Verdana;  font-size:12px; margin-top: 24px; width: 70%; height: 280px'></iframe>
      </div>
      <div>
        <h3>I agree</h3>
        <input type='checkbox' name='pa_inst[accept]' id='pa_inst_accept' " . ((@$accepted) ? 'checked' : '') . " />
      </div>
      </center>";
      $nav = "
              <a onclick=\"if(document.getElementById('pa_inst_accept').checked==true) return true; else {alert('You did not accept the license terms. You can not continue the installation.'); return false;} \"class='bt next' href='?step=" . (($this->curr_step < 4) ? $this->curr_step+1 : $this->curr_step) . "' alt='next'></a>
       ";

      $data = array('message' => '',
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => $html);
      return $data;
   }

   private function GET_step_2($params) {
       if($this->error)
         return $this->msg_unable_to_continue($params);

       $form = new PAForm('pa_inst');
       $form->addHtml( '<p>'.__('Installer will perform now a series of tests to determine the working environment of the server and the minimum conditions for a successful installation. Please do not interrupt this step of the installation process.').'</p>' );
       $nav  = "
              <a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>
              <a class='bt submit' href='#' alt='submit' onclick='document.forms[\"pa_inst\"].submit();'></a>
         ";
       $data = array('message' => '',
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => $form->getHtml());
       return $data;
   }

   private function POST_step_2($params) {
      $_SESSION['installer'] = serialize($this);
      $nav = "
              <a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>
              <a class='bt next' href='?step=" . (($this->curr_step < 4) ? $this->curr_step+1 : $step) . "' alt='next'></a>
      ";
      $data = array('message' => array('msg' => __('Please wait...'), 'class' => 'msg_warn'),
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => "<iframe src='/install/basic_tests.php' frameborder='0' style='width: 100%; height: 380px'></iframe>");
      return $data;
   }

   private function GET_step_3($params) {
     global $app;

      if($this->error)
         return $this->msg_unable_to_continue($params);

      list($info, $results) = $this->get_config_section('database', "@readonly='false'");
      $section_name = $info['name'];
      $form = new PAForm('pa_inst');
      $form->openTag('fieldset');
      $form->addContentTag('legend', array('value' => $info['description']));
      $form->addHtml('<div>');
      $form->addHtml('<p class="inst_info">'.__('Please complete the following information if you have already created a database for People Aggregator application.').'</p>');
      foreach($results as $key => $data) {
        $form->addInputField('text', $data['attributes']['description'],
                             array('id' => $key, 'required' => true, 'value' => $data['value'])
        );
      }
      $form->addHtml('<p class="inst_info">'.__('or you can provide bellow MySQL root username/password and People Aggregator will create a database for you.').'</p>');
      $form->addInputField('text', __('MySQL root username'),
                             array('id' => 'mysql_root_username', 'required' => false, 'value' => '')
      );
      $form->addInputField('text', __('MySQL root password'),
                             array('id' => 'mysql_root_password', 'required' => false, 'value' => '')
      );
      $form->addInputTag('hidden', array('id' => 'section_name', 'value' => $section_name));
      $form->addHtml('</div>');
      $form->closeTag('fieldset');
      $html = $form->getHtml();
      $nav  = "
              <a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>
              <a class='bt submit' href='#' alt='submit' onclick='document.forms[\"pa_inst\"].submit();'></a>
      ";
      $data = array('message' => (!empty($params['message'])) ? $params['message'] :'',
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => $html);
      return $data;
   }

   private function POST_step_3($params) {
      $db_data = $this->form_data;
      $db_sect = $this->form_data['section_name'];
      unset($db_data['section_name']);
      $this->config[$db_sect] = $db_data;
      $_SESSION['installer'] = serialize($this);

      $nav = "
              <a class='bt back' href='?step=" . $this->curr_step . "' alt='previous'></a>
              <a class='bt next' href='?step=" . (($this->curr_step < 4) ? $this->curr_step+1 : $step) . "' alt='next'></a>
      ";
      $data = array('message' => array('msg' => __('Please wait, trying to connect to the database.'), 'class' => 'msg_warn'),
                    'title' => __('Creating the Database'),
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => "<iframe src='/install/db_tests.php' frameborder='0' style='width: 100%; height: 380px'></iframe>");
      return $data;
   }

   private function GET_step_4($params) {
      if($this->error)
         return $this->msg_unable_to_continue($params);
      $this->updateSettings();

      $msg = "<p class='msg_info'>Congratulations. You have successfully installed People Aggregator.<br /><br />".
             "Your initially assigned administrator user name: <b>admin</b><br />".
             "Your initially assigned administrator password: <b>admin</b><br /><br />".
             "For security reasons, change your initially assigned administrator password and be sure to delete your installation directory: \"pacore/web/install\". ".
             "If you want to re-install People Aggregator application, replace your \"pacore/web/config/AppConfig.xml\" ".
             "configuration file with a fresh copy from the installation archive and installation process ".
             "will run again.<br /><br />" .
             "<center>Click <a href=\"". PA_BASE_URL . PA_ROUTE_HOME_PAGE . "\"><b>here</b></a> to continue.</center></p>";

      $data = array('message' => array('msg' => $msg, 'class' => 'msg_info'),
                    'title' => __('Congratulations!'),
                    'step'  => $this->curr_step,
                    'navig' => '',
                    'content' => '');
      return $data;
   }

   private function updateSettings() {
    global $app;
       foreach($this->config['database'] as $key => $value) {
         $app->configData['configuration']['database']['value'][$key]['value'] = $value;
       }
       $app->configData['configuration']['database']['value']['peepagg_dsn']['value'] = $this->config['peepagg_dsn'];
       $app->configData['configuration']['basic_network_settings']['value']['enable_networks']['value'] = $this->config['allow_network_spawning'];
       $app->configData['configuration']['site_related']['value']['pa_installed']['value'] = 1;

       unlink(PA::$project_dir . APPLICATION_CONFIG_FILE);
       $confObj  = new XmlConfig(null, 'application');

       $confObj->loadFromArray($app->configData, $confObj->root_node);
       $confObj->saveToFile(PA::$project_dir . APPLICATION_CONFIG_FILE);
   }

   private function msg_unable_to_continue($params) {
       $msg = "<p>We are very sorry but the previous step in the installation was unsuccessful. You will not be able to continue the installation until you correct detected errors.</p>";
       $nav  = "
              <a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>
       ";
       $data = array('message' => array('msg' => $msg, 'class' => 'msg_err'),
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => '');
       return $data;
   }

   private function checkDBSettings($params) {
     // figure out CURRENT_DB.
     foreach($params as $name => $value) {
       if(empty($value)) {
         return array(false, __("Field $name can't be empty."));
       }
     }
     $dsn = "mysql://". $params['db_user'] .
                   ":". $params['db_password'] .
                   "@". $params['db_host'] .
                   "/". $params['db_name'];

  }

   private function get_config_section($name, $condition = null) {
     global $app;
     return $app->configObj->getConfigSection($name, $condition);
   }

   private function get_config_section_form($name, $condition = null) {
      list($info, $results) = $this->get_config_section($name, $condition);
      $section_name = $info['name'];
      $form = new PAForm('pa_inst');
      $form->addContentTag('legend', array('value' => $info['description']));
      $form->addHtml('<div>');
      foreach($results as $key => $data) {
        $form->addInputField('text', $data['attributes']['description'],
                             array('id' => $key, 'required' => true, 'value' => $data['value'])
        );
      }
      $form->addInputTag('hidden', array('id' => 'section_name', 'value' => $section_name));
      $form->addHtml('</div>');
      return $form->getHtml();
   }

   private function formData($varname) {
      return ((isset($this->form_data[$varname])) ? $this->form_data[$varname] : null);
   }

   private function configData($varname) {
      return ((isset($this->config[$varname])) ? $this->config[$varname] : null);
   }
}
?>