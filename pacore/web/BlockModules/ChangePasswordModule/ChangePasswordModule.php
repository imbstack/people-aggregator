<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ChangePasswordModule.php is a part of PeopleAggregator.
* Allows the user to update there password, also handles authenticating that
* the user as well as checking that all information they entered is correct.
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author Martin Spernau
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
class ChangePasswordModule extends Module {

    public $module_type = 'network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_module.tpl';

    public $forgot_password_id;

    function __construct() {
        parent::__construct();
        $this->title = __('Change Password');
        $this->main_block_id = "mod_change_pass";
    }

    /** !!
    * Handles rendering of the page
    * This calls { @link generate_inner_html }() and then packages up the 
    * data to be displayed and returns it in ( @link $content }
    * @return string $content html created by the function
    */
    function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    /** !!
    * Called by { @link render() } to create the html for the page
    * @return string $inner_html the html for the module
    */
    function generate_inner_html() {
        $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        $inner_html_gen = &new Template($tmp_file);
        $inner_html_gen->set('forgot_password_id', $this->forgot_password_id);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }

    /** !!
    * Checks to see if the appropriate data to create the module is available
    * If the data is available then the forgot_password_id value in
    * { @link $request_data } is passed into the forgot_password_id object in the class
    * @todo these parameters do nothing: $request_method
    * @param array $request_data contains user id
    * @return string returns 'skip' if the user id is not available
    */
    public function initializeModule($request_method, $request_data) {
        if(empty($request_data['log_nam']) || empty($request_data['forgot_password_id'])) {
            return 'skip';
        }
        //login name given in the url, should be same as of the page user.
        if(empty(PA::$page_uid) || PA::$page_user->login_name != $request_data['log_nam']) {
            return 'skip';
        }
        $this->forgot_password_id = $request_data['forgot_password_id'];
    }

    /** !!
    * Checks to see if all neccassary information has been provided by the user
    * If it has then the passord is updated otherwise it returns an alter stating
    * what information is missing.
    * @param array $request_data contains the data entered by the user
    * @param array $request_method must be POST or will the entire function is skipped
    */
    public function handleChangePassword($request_method, $request_data) {
        switch($request_method) {
            case 'POST':
                $message = NULL;
                //input validations.
                if(empty($request_data['password'])) {
                    $message = __('Please enter your new password');
                }
                elseif(empty($request_data['confirm_password'])) {
                    $message = __('Please confirm your new password');
                }
                elseif($request_data['confirm_password'] != $request_data['password']) {
                    $message = __('Passwords do not match. Please re-enter');
                }
                elseif(strlen($request_data['password']) < PA::$password_min_length) {
                    $message = sprintf(__('Password should be of %s characters or more.'), PA::$password_min_length);
                }
                elseif(strlen($request_data['password']) > PA::$password_max_length) {
                    $this->message = sprintf(__('Password should be less than %s charcaters.'), PA::$password_max_length);
                }
                //if $message is set then there is an error
                $redirect_url = $query_str = NULL;
                if(empty($message)) {
                    //inputs are valid, try changing the password
                    try {
                        User::change_password($request_data['password'], $this->forgot_password_id);
                        $msg_array = array(
                            'failure_msg' => NULL,
                            'success_msg' => $message,
                        );
                        $redirect_url = PA::$url.'/'.FILE_LOGIN;
                        $query_str = '?msg_id=7004';
                    }
                    catch(PAException$e) {
                        $msg_array = array(
                            'failure_msg' => $e->message,
                            'success_msg' => NULL,
                        );
                    }
                }
                else {
                    $msg_array = array(
                        'failure_msg' => $message,
                        'success_msg' => NULL,
                    );
                }
                @set_web_variables($msg_array, $redirect_url, $query_str);
                break;
        }
    }
}
?>
