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
require_once "api/Messaging/MessageDispatcher.class.php";

class InvitationModule extends Module {

    public $module_type = 'network';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_module.tpl';

    public function __construct() {
        parent::__construct();
        $this->title = __("Invite");
    }

    public function initializeModule($request_method, $request_data) {
        if(empty(PA::$login_uid)) {
            return 'skip';
        }
    }

    public function handleInvitationModuleSubmit($request_method, $request_data) {
        switch($request_method) {
            case 'POST':
                if(method_exists($this, 'handlePOSTPageSubmit')) {
                    $this->handlePOSTPageSubmit($request_data);
                }
        }
    }

    public function handlePOSTPageSubmit($request_data) {
        if(!empty($request_data['submit'])) {
            filter_all_post($request_data);
            if(!empty($request_data['email_user_name'])) {
                $msg                    = NULL;
                $friend_user_name       = trim($request_data['email_user_name']);
                $friend_user_name_array = explode(',', $friend_user_name);
                $cnt_usr_name           = count($friend_user_name_array);
                for($counter = 0; $counter < $cnt_usr_name; $counter++) {
                    try {
                        $user_obj = new User();
                        $user_obj->load(trim($friend_user_name_array[$counter]));
                        if($user_obj->email == PA::$login_user->email) {
                            $msg = 6002;
                            //you can not invite your self
                        }
                        else {
                            $valid_user_login_names[] = $user_obj->login_name;
                            $valid_usr_name_email[] = $user_obj->email;
                        }
                    }
                    catch(PAException$e) {
                        if(!empty($friend_user_name_array[$counter])) {
                            $msg .= '<br />'.$friend_user_name_array[$counter];
                        }
                    }
                }
                // end for
                if(!empty($msg) && !(is_int($msg))) {
                    $msg = sprintf(__('Following user names are not valid %s'), $msg);
                }
            }
            // end if : if user names are supplied.
            $invalid = array();
            if(!empty($request_data['email_id'])) {
                $friend_email       = trim($request_data['email_id']);
                $friend_email_array = explode(',', $friend_email);
                $cnt_email          = count($friend_email_array);
                $self_invite        = FALSE;
                $error              = FALSE;
                // Check for valid-invalid email addresses start
                for($counter = 0; $counter < $cnt_email; $counter++) {
                    $email_validation = Validation::validate_email(trim($friend_email_array[$counter]));
                    if($email_validation == '0') {
                        $invalid[] = trim($friend_email_array[$counter]);
                    }
                    elseif($friend_email_array[$counter] == PA::$login_user->email) {
                        $self_invite = TRUE;
                    }
                    else {
                        $valid_user_first_emails[] = $friend_email_array[$counter];
                        $valid_email[] = trim($friend_email_array[$counter]);
                    }
                }
            }
            // Check for valid-invalid email addresses end
            // Action for valid-invalid email addresses start
            if(empty($friend_email) && empty($friend_user_name)) {
                // if email field is left empty
                if(PA::$network_info->type == MOTHER_NETWORK_TYPE) {
                    $msg = 6003;
                }
                else {
                    $msg = 6001;
                }
                $error_email = TRUE;
                $error = TRUE;
            }
            elseif(!empty($friend_email) && !empty($friend_user_name)) {
                $msg = 7026;
                $error = TRUE;
            }
            elseif(!empty($self_invite)) {
                // if self invitation is made
                $msg         = 6002;
                $error_email = TRUE;
                $error       = TRUE;
            }
            elseif(sizeof($invalid) > 0) {
                // if invalid email addresses are supplied
                $invalid_cnt = count($invalid);
                $msg = '';
                for($counter = 0; $counter < $invalid_cnt; $counter++) {
                    if(!empty($invalid[$counter])) {
                        $msg .= '<br />'.$invalid[$counter];
                    }
                }
                if(!empty($msg)) {
                    $msg = sprintf(__('Following email addresses are not valid: %s'), $msg);
                }
                else {
                    $msg = __(' Invalid Email addresses');
                }
                $error_email = TRUE;
                $error = TRUE;
            }
            elseif(empty($msg)) {
                // At this point invitation could be made
                $msg = '';
                if(!empty($valid_email) && !empty($valid_usr_name_email)) {
                    $valid_email = array_merge($valid_email, $valid_usr_name_email);
                    $valid_user_first_emails = array_merge($valid_user_first_emails, $valid_user_login_names);
                }
                elseif(!empty($valid_usr_name_email)) {
                    $valid_email = $valid_usr_name_email;
                    $valid_user_first_emails = $valid_user_login_names;
                }
                $valid_cnt = count($valid_email);
                $message = nl2br($request_data['message']);
                for($counter = 0; $counter < $valid_cnt; $counter++) {
                    $inv           = new Invitation();
                    $inv->user_id  = PA::$login_user->user_id;
                    $inv->username = PA::$login_user->login_name;
                    // for invitation not for any group invitation collection id is -1
                    $inv->inv_collection_id   =-1;
                    $inv->inv_status          = INVITATION_PENDING;
                    $auth_token               = get_invitation_token(LONG_EXPIRES, $valid_email[$counter]);
                    $token                    = '&amp;token='.$auth_token;
                    $inv->register_url        = PA::$url."/".FILE_REGISTER."?InvID=$inv->inv_id";
                    $inv->accept_url          = PA::$url."/".FILE_LOGIN."?action=accept&InvID=$inv->inv_id$token";
                    $inv->inv_user_id         = NULL;
                    $inv->inv_user_first_name = $valid_user_first_emails[$counter];
                    $inv->inv_email           = $valid_email[$counter];
                    $inv->inv_summary         = 'Invitation from'.PA::$login_user->first_name.' '.PA::$login_user->last_name.' to join '.PA::$site_name;
                    if($message != CUSTOM_INVITATION_MESSAGE) {
                        $inv->inv_message = !empty($message) ? $message : NULL;
                    }
                    if(empty($error)) {
                        try {
                            $inv->send();
                        }
                        catch(PAException$e) {
                            $msg = "$e->message";
                            $save_error = TRUE;
                        }
                    }
                    if(isset($save_error) && $save_error == TRUE) {
                        $msg = sprintf(__('Sorry: you are unable to invite a friend. Reason: %s'), $msg);
                    }
                    else {
                        // invitation has been sent, now send mail
                        PAMail::send('invite_pa', $inv->inv_email, PA::$login_user, $inv);
                        $msg .= $valid_user_first_emails[$counter];
                        if($counter == ($valid_cnt-1)) {
                            $msg = sprintf(__('An Invitation has been sent to - %s'), $msg);
                        }
                    }
                }
                // end for : invitation to multiple email
            }
            $this->message     = $msg;
            $this->redirect2   = NULL;
            $this->queryString = NULL;
            $this->isError     = TRUE;
            $this->setWebPageMessage();
        }
    }

    public function render() {
        $this->inner_HTML = $this->generate_inner_html();
        $content = parent::render();
        return $content;
    }

    public function generate_inner_html() {
        switch($this->mode) {
            default:
                $tmp_file = PA::$blockmodule_path.'/'.get_class($this)."/center_inner_public.tpl";
        }
        $register = &new Template($tmp_file);
        $inner_html = $register->fetch();
        return $inner_html;
    }
}
?>
