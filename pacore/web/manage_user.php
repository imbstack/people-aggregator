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

  //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  require_once "web/includes/functions/functions.php";

  $error = FALSE;
  $authorization_required = TRUE;

  $super_user_and_mothership = FALSE; //this flag will be set when logged in user is SU and is in Mothership
  if( ( SUPER_USER_ID == $_SESSION['user']['id'] )  && PA::$network_info->type == MOTHER_NETWORK_TYPE ) {
    $super_user_and_mothership = TRUE;
  }

  if( !$error && @$_REQUEST['action'] ) {

    if( empty( $_REQUEST['uid'] )) {
      $error = true;
      $message = __('Please select atleast one user.');
    }
  //
    if( !$error &&  $_REQUEST['uid'] != $_SESSION['user']['id'] ) {

      if( is_array( $_REQUEST['uid'] ) ) {
        $user_id_array = $_REQUEST['uid'];
      }
      else {
        $user_id_array[] = $_REQUEST['uid'];
      }

      $params['user_id_array'] = $user_id_array;
      $params['network_id'] = PA::$network_info->network_id;

      switch( $_REQUEST['action'] ) {
        case 'disable':
          try {

            if( $super_user_and_mothership ) { //status updated by SU in mothership
              $params['status'] = DISABLED;
              User::update_user_status ( $params );
            }
            else {// status updated by network owner in a network
              $params['user_type'] = DISABLED_MEMBER;
              Network::update_membership_type ( $params );
            }

            $message = __('Status of selected user(s) updated successfully');
          }
          catch ( PAException $e ) {
            $message = $e->message;
          }
          break;

        case 'enable':
          try {

             if( $super_user_and_mothership ) {//status updated by SU in mothership
              $params['status'] = ACTIVE;
              User::update_user_status ( $params );
            }
            else {// status updated by network owner in a network
              $params['user_type'] = NETWORK_MEMBER;
              Network::update_membership_type ( $params );
            }
            $message = __('Status of selected user(s) updated successfully');
          }
          catch ( PAException $e ) {
            $message = $e->message;
          }
          break;

        case 'approve':
          try {
            $params['status'] = ACTIVE;
            User::update_user_status ( $params );
            $params['user_type'] = NETWORK_MEMBER;
            Network::update_membership_type ( $params );

            // providing defaults to new user
            // creating message basic folders
            Message::create_basic_folders($_REQUEST['uid']);
            // adding default relation
            if ( $_REQUEST['uid'] != SUPER_USER_ID ) {
              User_Registration::add_default_relation($_REQUEST['uid'], PA::$network_info);
            }
            // adding default media as well as album
            User_Registration::add_default_media($_REQUEST['uid'], '', PA::$network_info);
            User_Registration::add_default_media($_REQUEST['uid'], '_audio', PA::$network_info);
            User_Registration::add_default_media($_REQUEST['uid'], '_video', PA::$network_info);
            User_Registration::add_default_blog($_REQUEST['uid']);
            //adding default link categories & links
            User_Registration::add_default_links ($_REQUEST['uid']);

            $message = __('Status of selected user(s) updated successfully');
          }
          catch ( PAException $e ) {
            $message = $e->message;
          }
          break;

        case 'delete':
          require_once "api/Activities/Activities.php";
          $message_array = delete_users($params);
          if($message_array == null) {
            $_GET['msg'] = '7020';
          } else {
            $message = __('An error has occured on deleting users.') . implode(',', $message_array).'';
          }
          break;
      }
    }
  }



  function setup_module($column, $module, $obj) {
    global $paging, $super_user_and_mothership;
    switch($module){

      case 'NetworkResultUserModule':
      if (@$_GET['keyword']) {
        $obj->keyword = $_GET['keyword'];
      }
      else {
        $obj->keyword = '';
      }
      if (@$_GET['sort_by'] == 'alphabetic') {
        $obj->sort_by = 'U.login_name';
        $obj->direction = 'ASC';
      } else {
        $obj->sort_by = 'U.created';
        $obj->direction = 'DESC';
      }

      $obj->Paging["page"] = $paging["page"];
      $obj->Paging["show"] = 10;
      $obj->network_info = PA::$network_info;
      $obj->super_user_and_mothership = $super_user_and_mothership;
      break;
    }
  }
  $page = new PageRenderer("setup_module", PAGE_NETWORK_MANAGE_USERS, "Manage Registered User", 'container_two_column.tpl','header.tpl',PUB, HOMEPAGE, PA::$network_info);

  if( @$_GET['msg'] ) {
    require_once 'web/languages/english/MessagesHandler.php';
    $msg_obj = new MessagesHandler();
    $message = $msg_obj->get_message($_GET['msg']);
  }

  if ( @$message ) {
    $msg_tpl = new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $message);
    $m = $msg_tpl->fetch();
    $page->add_module("middle", "top", $m);
  }

  $page->html_body_attributes ='class="no_second_tier network_config"';
  $css_array = get_network_css();
  if (is_array($css_array)) {
    foreach ($css_array as $key => $value) {
      $page->add_header_css($value);
    }
  }
  $page->add_header_html(js_includes('roles.js'));
  $page->add_header_html(js_includes('ModalWindow.js'));
  $page->add_header_css(PA::$theme_rel . '/invite_modal.css');

  $css_data = inline_css_style();
  if (!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
  }
  echo $page->render();
?>