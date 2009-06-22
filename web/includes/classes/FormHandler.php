<?php
  
 /**
  * Class for handling the form activities
  * @author Tekriti Software (http://www.tekritisoftware.com)
  */

  global  $current_blockmodule_path;
  
  class FormHandler {
   
  /**
   * @var for the name of block module which content the form variables
   * @access public
   */
   public $block_module_name;
    
  /**
   * @var for containing the message either successfull message or failure
   * @access public
   */
   public $msg = array();
  
  /**
   * @var for containing form data which is equal to the $_POST this is use when form fails
   * @access public
   */
    public $form_data = array();
    
  /**
   * @var for containing form data which is equal to the $_POST
   * @access public
   */
    public $_form = array();
    
  /**
   * @var  which contant name of action file ,bydefault it is "Action.php" inside the blockmodule
   * @access public
   */
    public $action_file;
  
  /**
   * @var  name of page which is rediect after success , bydefault it is on same page 
   * @access public
   */
    public $redirect_url;
  
   /**
   * @var  for redirection of page 
   * @access public
   */
    public $redirect = array();
    
  /**
   * @var  when we want to unset the form data after failure
   * @access public
   */
    public $error_key;
    
  /**
   * @var  when user want to set extra parameter during redirection
   * @access public
   */
    public $query_str;
    
    
    public function __construct() {
      // set the data array
       $this->_get_data();
    }
    
    
    /**
     This function is used for the setting the post data for object of this class
     access type is private
    */
    private function _get_data() {
      if (empty($_REQUEST)) return false;
      $this->_form = $_REQUEST;
    }
    
    
    /**
     This function set data for the action file , in the action file we can get the data by using a var $post_data
     access type is private
    */
    private function _set_data() {
      global $current_blockmodule_path;
      // When user don't specify the Action files
      if (!empty($this->action_file)) {
        $action_file = PA::$url .'/'.$this->action_file;
      }
      else {
        $action_file = $current_blockmodule_path.'/'.$this->block_module_name.'/action.php';
      }
      
      if(file_exists(PA::$project_dir . '/' .$action_file)  ||
         file_exists(PA::$core_dir . '/' .$action_file)) {
        // Setting the data for the file
        $obj_inner_template = & new Template($action_file);
        $obj_inner_template->set('_form', $this->_form);
        $obj_inner_template->set('path_prefix', PA::$path);
        $obj_inner_template->fetch();
      }
//       else {
//         $this->msg['failure_msg'] = 'Unable to find action file';
//         $this->_set_msg();
//       }
    }
    
    
    /**
     This function is used for the setting action file data for this class's object 
     access type is private
    */
    private function _set_msg() {
      global $global_form_data,$global_form_error;
      if (empty($this->msg['failure_msg'])) {
        $redirect['msg_id'] = $this->msg['success_msg'];
        
        // When we want to redirect on success:
        if (!empty($this->redirect_url)) {
          $redirect['url'] = $this->redirect_url;
          $redirect['query_str'] = $this->query_str;
          $this->_redirect_fn($redirect);
        }
      }
      else {
        $redirect['msg_id'] = $this->msg['failure_msg'];
       
        // When we want to rediect on failure:
       /* if (!empty($this->on_failure)) {
          $redirect['url'] = $this->on_failure;
          $redirect['query_str'] = $this->query_str;
          $this->_redirect_fn($redirect);
        }*/
      }
      
      // Setting form data and error in the global variable
      $global_form_data = $this->_form;
      $global_form_error = $redirect['msg_id'];
    }
    
    
   /**
     This function is used for redirect of the Page 
     access type is private
    */
    private function _redirect_fn ($redirect=array()) {
    
      $location = (strstr($redirect['url'], "http://")) ? $redirect['url'] : PA::$url.'/'.$redirect['url'];
      if (!empty($redirect['msg_id'])) {
        $location .= (preg_match('/\?/',$location)) ? '&' : '?';
        $location .= 'msg_id='.$redirect['msg_id'];
      }
      if (!empty($redirect['query_str'])) {
        //FIX for removing ? from query_str
        //TODO make correct query_str in all action.php files
        $redirect['query_str'] = str_replace('?', '', $redirect['query_str']);  
        $location .= (preg_match('/\?/',$location)) ? '&' : '?';
        $location .= $redirect['query_str'];
      }

      header("Location: $location");
      exit;
    }
    
   
   /**
     This function is reset the post data according to the key
     access type is private
    */    
    private function make_data() {
      if (empty($this->unset_array)) return;
        foreach ($this->_form as $k => $v) {
          if (in_array($k, $this->unset_array)) {
            unset($this->_form[$k]);
          }
        } // end of foreach
    }
    
    
   /**
     function for managing the post data 
     access type is public
    */  
    public function manage_post() {
      $this->_set_data();
    }
    
    
   /**
     function for setting the data in the web files as well as the error message 
     access type is public
    */
    public function handle_post_data() {
      $this->make_data();
      $this->_set_msg();
    }
    
  }// End of class
?>