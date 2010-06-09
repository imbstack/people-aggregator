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
$login_required = TRUE;

include_once("web/includes/page.php");
//require "api/Album/Album.php";
require "api/Image/Image.php";
require "api/Audio/Audio.php";
require "api/Video/Video.php";
require_once "api/Activities/Activities.php";

$location = PA::$url .'/manage_user.php';
$permission = FALSE;
if( Network::is_admin(PA::$network_info->network_id, $_SESSION['user']['id']) ) {
  $permission = TRUE;
}
if (!empty($_GET['msg']) && $_GET['msg'] == 'own_delete') {
  $permission = TRUE;
  $user_id = $_SESSION['user']['id'];
  $location = PA::$url . PA_ROUTE_HOME_PAGE;
}else if( !$user_id = $_GET['uid'] ) {
  $location .= '?msg=7005';
}


if( $user_id && $permission ) {
  $message_array = array();
  
  if(PA::$network_info->type == MOTHER_NETWORK_TYPE ) {//user delete for SU
  
  //deleting user data from mothership
  try {
    User::delete_user( $user_id );
    Activities::delete_for_user( $user_id );
  }
  catch ( PAException $e ) {
    $message_array[] = $e->message; 
  }
  
  $user_networks = Network::get_user_networks( $user_id );
    if( count( $user_networks ) ) {
      foreach( $user_networks as $network ) {
        
        if( $network->user_type != NETWORK_OWNER) {
          $network_prefix = $network->address;
          try {
            User::delete_user( $user_id );
            Activities::delete_for_user( $user_id );
            Network::leave( $network->network_id, $user_id );//leave
          }
          catch ( PAException $e ) {
            $message_array[] = $e->message; 
          }
        }
        else {
          try {
            Network::delete( $network->network_id );
          }
          catch ( PAException $e ) {
            $message_array[] = $e->message;  
          }
        }
      }
    }
    
    
    //deleting user
    try {
      User::delete( $user_id );
    }
    catch ( PAException $e ) {
      Logger::log('User has been already deleted');
    }
  }
  else {//user delete for network owner
    
    $network_prefix =PA::$network_info->address;
    try {      
      User::delete_user( $user_id );
      Activities::delete_for_user( $user_id );
      Network::leave(PA::$network_info->network_id, $user_id );//network leave
    }
    catch ( PAException $e ) {
      $message_array[] = $e->message;
    }
    
  }
  if (isset($_GET['msg']) and ($_GET['msg'] == 'own_delete')) {
    $location .= '?msg=7031';
  } else {
    $location .= '?msg=7020';
  }
}

if( count( $message_array ) ) {
  Logger::log('User with user_id = '.$user_id.' has been deleted with some errors.'.implode(',', $message_array).'');
}
header("Location: $location");
exit;

?>