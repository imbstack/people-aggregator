<?php
require_once "web/includes/functions/validations.php";

class ForgotPasswordModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  /**
  * $error : boolean variable and will be set to false when some error occurs while recovering password.
  */
  public $error;
  public $email;
  public $is_post_set;
  public $title;

  public function __construct() {
    parent::__construct();
    $this->is_post_set = FALSE;
  }
  
  public function initializeModule($request_method, $request_data) {
    if ($request_method == 'POST') {
      $this->is_post_set = TRUE;
    }
//    echo "INIT";
  }

  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  public function handlePOST_forgotPasswordSubmit($request_data) { 
    global $error_msg;
    $msg = NULL;
    if (isset($request_data['submit'])) {
//     echo "POST";
     $error = FALSE;
      $this->is_post_set = TRUE;
      $this->email = trim($request_data['email']);
      $l_name = trim($request_data['login_name']);
      
      // case when Both are Empty
      if (empty($this->email) && empty($l_name)) {
        $msg = __('Please enter your email address or login name.');
        $error = TRUE;
      }

      // Case when Both are filled 
      if (!empty($this->email) && !empty($l_name)) {
        $msg = __('Please enter either email or Login name');
        $error = TRUE;
      }

      // case when single field is field 
      if (!$error) {
        if(!empty($this->email)) {
          $val = validate_email($this->email);
          if (empty($val)) {
            $msg = __("Invalid email address.  Please try again.");
            $error = TRUE;
          }
        }
        if (!$error) {
          $usr = new User();
            try {
              (!empty($this->email)) ? $usr->load($this->email, 'email'): $usr->load($l_name, 'login_name');
              User::send_email_to_change_password($usr->email);
              $msg = urlencode(__("A link has been e-mailed to you to let you change your password.  Thanks!"));
            }
            catch(PAException $e) {
              $msg = "$e->message";
              $error = TRUE;
            }
        } else {
              $msg = nl2br(sprintf(__("There are no accounts in our system with the e-mail address %s.
              If you have spelled the address incorrectly or entered the wrong address, please try again."),  $this->email));
              $error = TRUE;
        }
      }
    }
//    $error_msg = $msg;
    $this->controller->redirect(PA::$url. "/login.php?msg=$msg");
/*    
    $msg_array = array();
    $msg_array['failure_msg'] = $msg;
    $msg_array['success_msg'] = NULL;
    $redirect_url = NULL;
    $query_str = NULL;
    set_web_variables($msg_array, $redirect_url, $query_str); 
*/    
  }
  
  public function render() {
    $this->title = __("Recover Password");    
    $this->inner_HTML = $this->generate_inner_html ();
    $content = parent::render();
    return $content;
  }
  
  public function generate_inner_html () {
    switch ($this->mode) {
     default:
        $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_public.tpl';
    }    
    
    $info = & new Template($tmp_file);
    global $global_form_error;
    
    $info->set('error', $global_form_error);
    $info->set('is_post_set',$this->is_post_set );
    $info->set('email', $this->email);
    $inner_html = $info->fetch();
    return $inner_html;
  }

}
?>