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
  // global var $_base_url has been removed - please, use PA::$url static variable
require_once "api/PAException/PAException.php";
require_once "api/User/User.php";
require_once "api/Network/Network.php";
require_once "api/Group/Group.php";
require_once "api/Message/Message.php";
require_once "web/includes/network.inc.php";
require_once "web/includes/functions/mailing.php";
require_once "web/includes/urls.php";
require_once "api/EmailMessages/EmailMessages.php";
define("EXPIRES", 3600*24*5);

/** how to use this file
*
* include this file where you want to call auto_email_notification
*  then create a function in this file as it is depicted in                           *  extra['notify_owner']['<-case->']['caption'] field of network info
*  for examaple 'some_joins_a_network'
*  within that function return if extra['notify_owner']['<-case->']['value'] !=       *  NET_NONE means no notification
*  other wise set variables that is to be used in mail/message subject body
*  then call function $this->switch_destination($destination)
*
*/
function auto_email_notification($activity_type, $params) {
    if(!(PA::$network_info)) {
        return;
    }
    //setting common variables
    $notification = new EmailNotification();
    //mail to
    if(PA::$network_info->type == MOTHER_NETWORK_TYPE) {
        $network_owner_id = SUPER_USER_ID;
    }
    else {
        $network_owner_id = Network::get_network_owner(PA::$network_info->network_id);
    }
    $notification->network_owner = User::map_ids_to_logins($network_owner_id);
    $notification->owner = new User();
    foreach($notification->network_owner as $key => $value) {
        $notification->owner->load((int) $key);
    }
    $notification->to = $notification->owner->email;
    $owner_name = $notification->owner->login_name;
    //mail from
    $notification->from = (int) PA::$login_uid;
    $array_of_data = array(
        'to'         => $notification->to,
        'from'       => $notification->from,
        'owner_name' => $owner_name,
        'params'     => $params,
    );
    $notification->send($activity_type, $array_of_data);
}
//function for members notification
function auto_email_notification_members($activity_type, $params) {
    if(!(PA::$network_info)) {
        return;
    }
    //setting common variables
    $notification = new EmailNotification();
    // mail to
    // FIXME: these two seemed not to be set in occasion
    $gid = (@$params['gid']) ? $params['gid'] : @$_GET['gid'];
    $rid = @$params['related_uid'];
    if($gid) {
        $network_owner_id            = Group::get_owner_id($gid);
        $notification->network_owner = User::map_ids_to_logins($network_owner_id['user_id']);
        $group_owner                 = new User();
        $group_owner->load((int) $network_owner_id['user_id']);
        $notification->to = $group_owner->email;
        $group_owner_name = $group_owner->login_name;
        //mail from
        $notification->from = (int) PA::$login_uid;
        $array_of_data = array(
            'to'             => $notification->to,
            'from'           => $notification->from,
            'owner_name'     => $group_owner_name,
            'group_owner_id' => $network_owner_id['user_id'],
        );
    }
    if($rid) {
        $notification->network_owner = User::map_ids_to_logins(array($rid));
        foreach($notification->network_owner as $key => $value) {
            $rel_user = new User();
            $rel_user->load((int) $key);
            $related_name = $rel_user->login_name;
            $notification->to = $rel_user->email;
        }
        $notification->from = (int) PA::$login_uid;
        $array_of_data = array(
            'to'           => $notification->to,
            'from'         => $notification->from,
            'related_name' => $related_name,
        );
    }
    $array_of_data['params'] = $params;
    $notification->send($activity_type, $array_of_data);
}

class EmailNotification {

    public $to;

    public $mail_type;

    public $mail_sub_msg_array;

    public $from;

    public $no_id;

    public $network_owner;

    public $subject;

    public $message;

    public $owner;

    public function __construct() {
    }

    public function send($activity_type, $array_of_data) {
        $method_name = $activity_type;
        if(method_exists($this, $method_name)) {
            $this-> {
                $method_name
            }($array_of_data);
        }
        else {
            throw new Exception(get_class($this)." error: Unhandled notification. Activity type: \"$activity_type\".");
        }
    }

    function some_joins_a_network($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['some_joins_a_network']['value'];
        if($destination == NET_NONE) {
            //if no notification
            return;
        }
        $uid = $array_of_data['params']['uid'];
        $login = User::get_login_name_from_id($uid);

        /*
            $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$uid;
            $url_perms = array('current_url' => $current_url,
                                      'login' => $login
                                    );
            $url = get_url(FILE_USER_BLOG, $url_perms);
        */
        $url = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;
        $user = new User();
        $user->load((int) $uid);
        $joinee_name              = $user->login_name;
        $this->network_owner_name = $array_of_data['owner_name'];
        PA::$network_info         = get_network_info();
        $member_count             = PA::$network_info->member_count;
        $this->mail_type          = 'network_join';
        $member_moderation_url    = PA::$url.'/'.FILE_NETWORK_MANAGE_USER;
        $this->mail_sub_msg_array = array(
            'joinee' => $joinee_name,
            //invited user name
            'network_name'          => PA::$network_info->name,
            'network_owner_name'    => $this->network_owner_name,
            'member_count'          => $member_count,
            'joinee_id'             => $uid,
            'joinee_url'            => $url,
            'config_site_name'      => PA::$site_name,
            'network_url'           => PA::$url,
            'member_moderation_url' => $member_moderation_url,
        );
        $this->from = $uid;
        $this->switch_destination($destination);
    }

    function content_posted($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['content_posted']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $this->mail_type = 'content_posted';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function content_modified($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['content_modified']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $this->mail_type = 'content_modified';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function content_posted_to_comm_blog($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['content_to_homepage']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $this->mail_type = 'content_posted_to_comm_blog';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function group_created($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['group_created']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $this->mail_type = 'group_created';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function group_settings_updated($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['group_settings_updated']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $this->mail_type = 'group_settings_updated';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function media_uploaded($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['media_uploaded']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $array_of_data['params']['network_owner_name'] = $array_of_data['owner_name'];
        $this->mail_type                               = 'media_uploaded';
        $this->mail_sub_msg_array                      = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function relation_added($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['relation_added']['value'];
        if($destination == NET_NONE) {
            //if no notification
            return;
        }
        $login = User::get_login_name_from_id($_SESSION['user']['id']);

        /*
            $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$_SESSION['user']['id'];
            $url_perms = array('current_url' => $current_url,
                                      'login' => $login
                                    );
            $url = get_url(FILE_USER_BLOG, $url_perms);
        */
        $url      = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;
        $user_url = "<a href=\"$url\">$url</a>";
        $login    = User::get_login_name_from_id($array_of_data['params']['related_uid']);

        /*
            $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$array_of_data['params']['related_uid'];
            $url_perms = array('current_url' => $current_url,
                                      'login' => $login
                                    );
            $url = get_url(FILE_USER_BLOG, $url_perms);
        */
        $url                                         = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;
        $related_url                                 = "<a href=\"$url\">$url</a>";
        $array_of_data['params']['establisher_url']  = $user_url;
        $array_of_data['params']['related_url']      = $related_url;
        $array_of_data['params']['config_site_name'] = PA::$site_name;
        $array_of_data['params']['owner_name']       = $array_of_data['owner_name'];
        $this->mail_type                             = 'relation_added';
        $this->mail_sub_msg_array                    = $array_of_data['params'];
        $this->switch_destination($destination);
    }

    function bulletin_sent($array_of_data) {
        $extra = unserialize(PA::$network_info->extra);
        $destination = $extra['notify_owner']['bulletin_sent']['value'];
        if($destination == NET_NONE) {
            return;
        }
        $cid              = $array_of_data['params']['cid'];
        $this->owner_name = $array_of_data['owner_name'];
        $expires          = EXPIRES;
        //5days
        $this->token   = $this->owner->get_auth_token($expires);
        $bulletin_url  = PA::$url.'/mail_action.php?cid='.$cid.'&token='.$this->token;
        $this->subject = "Hi $this->owner_name, A bulletins is posted in your network ".PA::$url;
        $configure_url = PA::$url.'/configure_network.php';
        $this->message = "Hi $this->owner_name,
                A Bulletin is sent in your network ".PA::$url."
                To get the details of this bulletin go through $bulletin_url
                To view your network go through ".PA::$url."
                To block this kind of alerts go through $configure_url to change your network setting.
                Regards,
                Auto Email Notification.
                Peeple Aggregator.
                ";
        $this->mail_sub_msg_array = array(
            'subject' => $this->subject,
            'message' => $this->message,
        );
        $this->switch_destination($destination);
    }
    //functions for members notification
    function relationship_created_with_other_member($array_of_data) {
        $rid = $array_of_data['params']['related_uid'];
        $user_profile = User::load_user_profile($rid, $rid, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['relationship_created_with_other_member']['value'];
        }
        if($destination == NET_NONE) {
            //if no notification
            return;
        }
        // to for external mail
        $this->to = $array_of_data['params']['to'];
        // to/from for internal messaging
        $this->from = $array_of_data['params']['user_id'];
        //relation establisher id
        $this->network_owner = $array_of_data['params']['related_user'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = 'relation_estab';
        $this->mail_sub_msg_array = $array_of_data['params'];
        // now send msg to proper destination either email or inbox
        $this->switch_destination($destination);
    }

    function someone_join_their_group($array_of_data) {
        $destination = NULL;
        $group_owner_uid = $array_of_data['params']['group_owner_id'];
        // check for group owner notification
        $user_profile = User::load_user_profile($group_owner_uid, $group_owner_uid, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['someone_join_their_group']['value'];
            if($destination == NET_NONE) {
                return;
            }
        }
        // to for external mail
        $this->to = $array_of_data['params']['group_owner_email'];
        // to/from for internal msg
        $this->network_owner = $array_of_data['params']['group_owner_name'];
        $this->from = $array_of_data['params']['joinee_uid'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = $array_of_data['params']['mail_type'];
        $this->mail_sub_msg_array = $array_of_data['params'];
        // now send msg to proper destination either email or inbox
        $this->switch_destination($destination);
    }

    function invitation_accept($array_of_data) {
        $invited_user_id = $array_of_data['params']['invited_user_id'];
        $user_profile = User::load_user_profile($invited_user_id, $invited_user_id, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['invitation_accept']['value'];
            if($destination == NET_NONE) {
                return;
            }
        }
        // to for external mail
        $this->to = $array_of_data['params']['to'];
        // to/from for internal msg
        $this->network_owner = $array_of_data['params']['invited_user_name'];
        $this->from = $array_of_data['params']['user_id'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = $array_of_data['params']['mail_type'];
        $this->mail_sub_msg_array = $array_of_data['params'];
        // now send msg to proper destination either email or inbox
        $this->switch_destination($destination);
    }
    //functions for members notification
    function msg_waiting($array_of_data) {
        $recipient_id = $array_of_data['params']['recipient_id'];
        $user_profile = User::load_user_profile($recipient_id, $recipient_id, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['msg_waiting_blink'];
        }
        if(@$destination == NET_EMAIL) {
            // to for external mail
            $this->to = $array_of_data['params']['recipient_email'];
            // mail type and array of data to be replaced with config variables
            $this->mail_type = 'msg_waiting';
            $this->mail_sub_msg_array = $array_of_data['params'];
            $this->switch_destination($destination);
        }
    }
    // function for friend request notification
    function relationship_requested($array_of_data) {
        $requested_user_id = $array_of_data['params']['requested_user_id'];
        $user_profile = User::load_user_profile($requested_user_id, $requested_user_id, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['friend_request_sent']['value'];
            if($destination == NET_NONE) {
                return;
            }
        }
        // to for external mail
        $this->to = $array_of_data['params']['to'];
        // to/from for internal msg
        $this->from = $array_of_data['params']['user_id'];
        $this->network_owner = $array_of_data['params']['invited_user_name'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = 'friend_request';
        $this->mail_sub_msg_array = $array_of_data['params'];
        // now send msg to proper destination either email or inbox
        $this->switch_destination($destination);
    }
    // function for friend request denial notification
    function relationship_denied($array_of_data) {
        $requested_user_id = $array_of_data['params']['requested_user_id'];
        $user_profile = User::load_user_profile($requested_user_id, $requested_user_id, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['friend_request_denial']['value'];
            if($destination == NET_NONE) {
                return;
            }
        }
        // to for external mail
        $this->to = $array_of_data['params']['to'];
        // to/from for internal messaging
        $this->from = $array_of_data['params']['user_id'];
        $this->network_owner = $array_of_data['params']['requested_user_name'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = 'friend_denied';
        $this->mail_sub_msg_array = $array_of_data['params'];
        $this->switch_destination($destination);
    }
    // function for send welocme message
    function welcome_message($array_of_data) {
        $requested_user_id = $array_of_data['params']['recipient_id'];
        $user_profile = User::load_user_profile($requested_user_id, $requested_user_id, 'notifications');
        if(!empty($user_profile)) {
            $notify = unserialize($user_profile[0]['value']);
            $destination = $notify['welcome_message']['value'];
            if($destination == NET_NONE) {
                return;
            }
        }
        // to for external mail
        $this->to = $array_of_data['params']['recipient_email'];
        // to/from for internal msg
        $this->from = $array_of_data['params']['sender_uid'];
        $this->network_owner = $array_of_data['params']['network_owner_name'];
        // mail type and array of data to be replaced with configurable variables
        $this->mail_type = 'welcome_message';
        $this->mail_sub_msg_array = $array_of_data['params'];
        // now send msg to proper destination either email or inbox
        $this->switch_destination($destination);
    }
    //this function is called after setting varibles for particular case, It handles notification through Email, internal_message, or both
    function switch_destination($destination) {
        $this->no_id = "";
        if(empty($this->mail_type)) {
            $this->mail_type = '';
            //none
        }
        if(empty($this->network_owner)) {
            // sometime group_owner param passed as recepient in this var - VERY BAD!
            $net_owner = new User();
            $net_owner->load((int) PA::$network_info->owner_id);
            $this->network_owner = $net_owner->login_name;
        }
        // checking whether subject or message is set or not
        $msg_data      = EmailMessages::get($this->mail_type, $this->mail_sub_msg_array);
        $this->subject = @$msg_data['subject'];
        $this->message = @$msg_data['message'];
        if(empty($this->subject)) {
            $this->subject = 'none';
        }
        if(empty($this->message)) {
            $this->message = 'Message for internal mail is under construction. <br /><br /> We\'ll back with appropriate message soon!!!';
        }
        if($this->mail_type == 'friend_request') {
            $mail_from = $_SESSION['user']['email'];
        }
        switch($destination) {
            case NET_EMAIL:
                //external mail
                $check = pa_mail($this->to, $this->mail_type, $this->mail_sub_msg_array, @$mail_from);
                break;
            case NET_MSG:
                //internal messageing
                Message::add_message($this->from, $this->no_id, $this->network_owner, $this->subject, $this->message);
                $sender = new User();
                $sender->load((int) $this->from);
                $sender_id       = $sender->user_id;
                $sender_name     = $sender->login_name;
                $recipient_id    = User::map_logins_to_ids($this->network_owner);
                $_sender_url     = url_for('user_blog', array('login' => $sender->login_name));
                $sender_url      = "<a href=\"$_sender_url\">$_sender_url</a>";
                $my_messages_url = '<a href="'.PA::$url.'/'.FILE_MYMESSAGE.'">'.PA::$url.'/'.FILE_MYMESSAGE.'</a>';
                $recipient_obj   = new User();
                foreach($recipient_id as $key => $value) {
                    $recipient_obj->load((int) $value);
                }
                // send msg waiting blink message
                $params = array(
                    'first_name_sender'    => $sender_name,
                    'first_name_recipient' => $recipient_obj->first_name,
                    'sender_id'            => $sender_id,
                    'recipient_id'         => $recipient_obj->user_id,
                    'recipient_email'      => $recipient_obj->email,
                    'sender_url'           => $sender_url,
                    'my_messages_url'      => $my_messages_url,
                    'config_site_name'     => PA::$site_name,
                );
                auto_email_notification('msg_waiting', $params);
                break;
            case NET_BOTH:
                // via both option
                // FIXME: $mail_from seems not be set on peepagg
                try {
                    $check = pa_mail($this->to, $this->mail_type, $this->mail_sub_msg_array, @$mail_from);
                }
                catch(PAEXception$e) {
                    Logger::log(__FILE__.": pa_mail: ".$e->getMessage());
                }
                Message::add_message($this->from, $this->no_id, $this->network_owner, $this->subject, $this->message);
                $sender = new User();
                $sender->load((int) $this->from);
                $sender_id       = $sender->user_id;
                $sender_name     = $sender->login_name;
                $recipient_id    = User::map_logins_to_ids($this->network_owner);
                $_sender_url     = url_for('user_blog', array('login' => $sender->login_name));
                $sender_url      = "<a href=\"$_sender_url\">$_sender_url</a>";
                $my_messages_url = '<a href="'.PA::$url.'/'.FILE_MYMESSAGE.'">'.PA::$url.'/'.FILE_MYMESSAGE.'</a>';
                $recipient_obj   = new User();
                foreach($recipient_id as $key => $value) {
                    $recipient_obj->load((int) $value);
                }
                // send msg waiting blink message
                $params = array(
                    'first_name_sender'    => $sender_name,
                    'first_name_recipient' => $recipient_obj->first_name,
                    'sender_id'            => $sender_id,
                    'recipient_id'         => $recipient_obj->user_id,
                    'recipient_email'      => $recipient_obj->email,
                    'sender_url'           => $sender_url,
                    'my_messages_url'      => $my_messages_url,
                    'config_site_name'     => PA::$site_name,
                );
                // chnaged by Martin: this is NET_BOTH, the user is already recieving it as email
                // so why do we also trigger the ms_waiting here?
                // auto_email_notification('msg_waiting', $params);
                break;
        }
    }
}
?>
