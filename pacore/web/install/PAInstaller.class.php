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
require_once "api/Theme/Template.php";
require_once "web/includes/classes/PAForm.class.php";

class PAInstaller {
   const install_template = 'web/install/install.tpl';
   public  $error = false;
   public  $form_data;
   public  $config;
   public  $allow_network_spawning;
   public  $subdomain;
   private $steps = array(1 => array('title' => 'License arrangement', 'conf_section' => null, 'curr_status' => false),
                          2 => array('title' => 'Installation requirements test', 'conf_section' => null, 'curr_status' => false),
                          3 => array('title' => 'Setup Administrator account', 'conf_section' => null, 'curr_status' => false),
                          4 => array('title' => 'Database settings', 'conf_section' => 'database', 'curr_status' => false),
                          5 => array('title' => 'Populating the Database', 'conf_section' => null, 'curr_status' => false),
                    );

   private $curr_step = 0;
   private $adm_data;

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
              <a onclick=\"if(document.getElementById('pa_inst_accept').checked==true) return true; else {alert('You did not accept the license terms. You can not continue the installation.'); return false;} \"class='bt next' href='?step=" . (($this->curr_step < 5) ? $this->curr_step+1 : $this->curr_step) . "' alt='next'></a>
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
      $nav ="
              <a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>
              <a class='bt next' href='?step=" . (($this->curr_step < 5) ? $this->curr_step+1 : $step) . "' alt='next'></a>
      ";
      $data = array('message' => array('msg' => __('Please wait...'), 'class' => 'msg_warn'),
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => "<iframe src='/install/basic_tests.php' frameborder='0' style='width: 100%; height: 380px'></iframe>");
      return $data;
   }

   private function GET_step_3($params, $is_post = false) {
     global $app;

      if($this->error)
         return $this->msg_unable_to_continue($params);

      $fileName = getShadowedPath("web/install/PeepAgg.mysql");
      $rexp = "INSERT\sINTO\s\`users\`\s\([^\)]+\)\sVALUES\s\(([^\)]+)\)";
      list($succ, $txt_line, $adm_data) = $this->findLine($fileName, $rexp);
      if(!$succ) {
        $this->error = true;
        $params['message']['msg'] = __("Installer is unable to read \"$fileName\" file.");
        $params['message']['class'] = 'msg_err';
        return $this->msg_unable_to_continue($params);
      }
      $adm_data = explode(",", $adm_data);
      $form = new PAForm('pa_inst');
      $form->openTag('fieldset');
      $form->addContentTag('legend', array('value' => 'Admin account details'));
      $form->addHtml('<div>');
      $form->addHtml('<p class="inst_info">'.__('Please complete the following information to create an admin account. The first and last names default to Admin Peepagg if left blank').'</p>');
      $form->addInputField('text', __('First Name'),
                             array('id' => 'admin_first', 'required' => false, 'value' => (($is_post) ? $this->form_data['admin_first'] : trim($adm_data[3], "'\t ")))
      );$form->addInputField('text', __('Last Name'),
                             array('id' => 'admin_last', 'required' => false, 'value' => (($is_post) ? $this->form_data['admin_last'] : trim($adm_data[4], "'\t ")))
      );
      $form->addInputField('text', __('Admin username'),
                             array('id' => 'admin_username', 'required' => true, 'value' => (($is_post) ? $this->form_data['admin_username'] : ""))
      );
      $form->addInputField('password', __('Admin password'),
                             array('class' => 'admin_password','id' => 'admin_password', 'required' => true, 'value' => (($is_post) ? $this->form_data['admin_password'] : ''))
      );
      $form->addInputField('text', __('Admin email'),
                             array('id' => 'admin_email', 'required' => true, 'value' => (($is_post) ? $this->form_data['admin_email'] : ""))
		     );

      $form->addHtml('<p class="inst_info">'.__('Enable multiple networks should be checked if you want users to be able to create sub-networks').'</p>');
      $form->addInputField('checkbox', __('Enable Multiple Networks'),
                             array('id' => 'network_spawning', 'required' => true, 'value' => (($is_post) ? $this->form_data['network_spawning'] : 'checked'), "'\t ")
		     );

      $form->addHtml('<p class="inst_info">'.__('Your root network will be accessed through this subdomain ex. <code> subdomain.example.com </code>').'</p>');
      $form->addInputField('text', __('Root Subdomain'),
                             array('id' => 'subdomain', 'required' => true, 'value' => (($is_post) ? $this->form_data['subdomain'] : ''))
      );
      $form->addHtml('</div>');
      $form->closeTag('fieldset');
      $html = $form->getHtml();
      $nav  = "<a class='bt back' href='?step=" . (($this->curr_step > 1) ? $this->curr_step-1 : 1) . "' alt='previous'></a>";
      if(!$is_post) {
         $nav .= "<a class='bt submit' href='#' alt='submit' onclick='document.forms[\"pa_inst\"].submit();'></a>";
      } else {
         $nav .= "<a class='bt next' href='?step=" . (($this->curr_step < 5) ? $this->curr_step+1 : $step) . "' alt='next'></a>";
      }
      $data = array('message' => (!empty($params['message'])) ? $params['message'] :'',
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => $html);
      return $data;
   }

   private function POST_step_3($params) {
     global $app;
     require_once "api/Validation/Validation.php";

     $form_data = $this->form_data;
     $error = false;
     $errors = array();

     if(empty($form_data['admin_first'])) {
	     $form_data['admin_first'] = "Admin";
     }
     if(empty($form_data['admin_last'])) {
	     $form_data['admin_last'] = "Peepagg";
     }
     if(!Validation::validate_auth_id($form_data['admin_username']) || empty($form_data['admin_username'])) {
       $error = true;
       $errors[] = __("Invalid or empty user name.");
     }
     if(strlen($form_data['admin_password']) < MIN_PASSWORD_LENGTH) {
       $error = true;
       $errors[] = sprintf(__("Your password must be at least %d characters long."), MIN_PASSWORD_LENGTH);
     }
     if(strlen($form_data['admin_password']) > MAX_PASSWORD_LENGTH) {
       $error = true;
       $errors[] = sprintf(__("Your password can not be longer than %d characters."), MAX_PASSWORD_LENGTH);
     }
     if(!Validation::validate_email($form_data['admin_email']) || empty($form_data['admin_email'])) {
       $error = true;
       $errors[] = __("Invalid or empty email field.");
     }
     if($error) {
      $params['message']['msg'] = implode("<br />", $errors);
      $params['message']['class'] = 'msg_err';
      return $this->GET_step_3($params);
     }

     $adm_login = $form_data['admin_username'];
     $adm_first = $form_data['admin_first'];
     $adm_last = $form_data['admin_last'];
     $adm_pass  = $form_data['admin_password'];
     $adm_mail  = $form_data['admin_email'];
     $this->allow_network_spawning = (isset($form_data['network_spawning']) && $form_data['network_spawning'] == 'checked') ? 1 : 0;
     $this->subdomain = $form_data['subdomain'];

     $fileName = getShadowedPath("web/install/PeepAgg.mysql");
     $oldLine  = "INSERT INTO `users` (`user_id`, `login_name`, `password`, `first_name`, `last_name`, `email`, `is_active`, `picture`, `created`, `changed`, `last_login`, `zipcode`)";
     $newLine  = "INSERT INTO `users` (`user_id`, `login_name`, `password`, `first_name`, `last_name`, `email`, `is_active`, `picture`, `created`, `changed`, `last_login`, `zipcode`) VALUES (1, '$adm_login', '".md5($adm_pass)."', '$adm_first', '$adm_last', '$adm_mail', 1, NULL, ".time().", ".time().", ".time().", NULL);";

     if($this->replaceLine($fileName, $oldLine, $newLine)) {
      $params['message']['msg'] = __("Administrator account data sucessfully stored. Click 'Next' please...");
      $params['message']['class'] = 'msg_info';
      $this->adm_data['login_name'] = $adm_login;
      $this->adm_data['password'] = $adm_pass;
      $_SESSION['installer'] = serialize($this);
      return $this->GET_step_3($params, true);
     } else {
      $params['message']['msg'] = __("Installer is unable to store administrator account data...Please, check file permissions for \"$fileName\" file.");
      $params['message']['class'] = 'msg_err';
      $this->error = true;
      return $this->GET_step_3($params);
     }

/*
      $data = array('message' => $params['message'],
                    'title' => $params['title'],
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => $html);
*/

   }
   private function GET_step_4($params) {
     global $app;

      if($this->error)
         return $this->msg_unable_to_continue($params);

      list($info, $results) = $this->get_config_section('database', "@readonly='false'");
      $section_name = $info['name'];
      $form = new PAForm('pa_inst');
      $form->openTag('fieldset');
      $form->addContentTag('legend', array('value' => $info['description']));
      $form->addHtml('<div>');
      $form->addHtml('<p class="inst_info">'.__('Please complete the following information so PeopleAggregator can access your database.').'</p>');
      $form->addInputField('text', __('Database name'),
                             array('id' => 'db_name', 'required' => true, 'value' => '')
      );
      $form->addInputField('text', __('Database host'),
                             array('id' => 'db_host', 'required' => true, 'value' => '')
      );
	/*
      foreach($results as $key => $data) {
        $form->addInputField('text', $data['attributes']['description'],
                             array('id' => $key, 'required' => true, 'value' => $data['value'])
        );
      }*/
      $form->addHtml('<p class="inst_info">'.__('Please provide PeopleAggregator with a MySQL username and password for the database.').'</p>');
      $form->addInputField('text', __('Database User Name'),
                             array('id' => 'db_user', 'required' => true, 'value' => '')
      );
      $form->addInputField('password', __('Database password'),
                             array('id' => 'db_password', 'required' => true, 'value' => '')
      );
      $form->addHtml('<p class="inst_info">'.__('If you would like PeopleAggregator to create this user for you, please provide your MySQL root password.').'</p>');
      $form->addInputField('text', __('MySQL root username'),
                             array('id' => 'mysql_root_username', 'required' => false, 'value' => '')
      );
      $form->addInputField('password', __('MySQL root password'),
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

   private function POST_step_4($params) {
      $db_data = $this->form_data;
      $db_sect = $this->form_data['section_name'];
      unset($db_data['section_name']);
      $this->config[$db_sect] = $db_data;
      $_SESSION['installer'] = serialize($this);

      $nav = "
              <a class='bt back' href='?step=" . $this->curr_step . "' alt='previous'></a>
              <a class='bt next' href='?step=" . (($this->curr_step < 5) ? $this->curr_step+1 : $step) . "' alt='next'></a>
      ";
      $data = array('message' => array('msg' => __('Please wait, trying to connect to the database.'), 'class' => 'msg_warn'),
                    'title' => __('Creating the Database'),
                    'step'  => $this->curr_step,
                    'navig' => $nav,
                    'content' => "<iframe src='/install/db_tests.php' frameborder='0' style='width: 100%; height: 380px'></iframe>");
      return $data;
   }

   private function GET_step_5($params) {
      if($this->error)
         return $this->msg_unable_to_continue($params);
      $this->updateSettings();

      $msg = "<p class='msg_info'>Congratulations. You have successfully installed People Aggregator.<br /><br />".
             "Administrator user name: <b>" . $this->adm_data['login_name'] . "</b><br />".
             "Administrator password: <b>" . $this->adm_data['password'] . "</b><br /><br />".
             "<b>For security reasons, change your initially assigned administrator password and be sure to delete your installation directory: \"pacore/web/install\"</b>. ".
             "If you want to re-install People Aggregator application, make backup of your \"pacore/config/AppConfig.xml\" ".
             "configuration file and delete it. Then reload page in your browser and installation process will run again.<br /><br />" .
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
       // We don't need to store the MySQL root un/pw, so un-set it from the config array.
       unset($this->config['database']['mysql_root_password'], $this->config['database']['mysql_root_username']);
       
       foreach($this->config['database'] as $key => $value) {
         $app->configData['configuration']['database']['value'][$key]['value'] = $value;
       }
       $app->configData['configuration']['database']['value']['peepagg_dsn']['value'] = $this->config['peepagg_dsn'];
       $app->configData['configuration']['basic_network_settings']['value']['domain_prefix']['value'] = $this->subdomain;
       $app->configData['configuration']['basic_network_settings']['value']['enable_networks']['value'] = $this->allow_network_spawning;
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
       $data = array('message' => array('msg' => $msg . ((!empty($params['message']['msg'])) ? "\nDetails: {$params['message']['msg']}" : ''), 'class' => 'msg_err'),
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

   private function replaceLine($fileName, $oldLine, $newLine) {
    $lines = @file($fileName, FILE_IGNORE_NEW_LINES);
    if(count($lines) <= 1) return false;
    foreach($lines as &$line) {
      $line = trim($line);
    }
    for($i = 0; $i < count($lines); ++$i) {
       if(false !== strpos($lines[$i] , $oldLine)) {
         $lines[$i] = $newLine;
         $f = @fopen($fileName, 'w');
         if(is_resource($f)) {
           fwrite($f, implode(LINE_BREAK, $lines));
           fclose($f);
           return true;
         }
         return false;
       }
     }
     return false;
   }

   private function findLine($fileName, $rexp) {
     $lines = @file($fileName);
     $matches = array();
     if(count($lines) <= 1) return array(false, null, null);
     for($i = 0; $i < count($lines); ++$i) {
       if(preg_match("/$rexp/", $lines[$i], $matches)) {
         return array(true, $lines[$i], $matches[1]);
       }
     }
     return array(false, null, null);
   }

   private function formData($varname) {
      return ((isset($this->form_data[$varname])) ? $this->form_data[$varname] : null);
   }
}
