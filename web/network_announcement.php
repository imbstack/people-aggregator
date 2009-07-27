<?php
  //anonymous user can not view this page;
  $login_required = TRUE;
  
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.  
  
  //including necessary files
  include_once("web/includes/page.php");
  include_once "api/ModuleSetting/ModuleSetting.php";
  include_once "api/Theme/Template.php";
  require_once "ext/Announcement/Announcement.php";
  include_once "api/Network/Network.php";
  require_once "web/includes/network.inc.php";
  require_once "web/includes/functions/auto_email_notify.php";
  
  //defining Constant for magic number
  define("ANNOUNCEMENT", 11);
  define("LIVE", 1);
  define("ARCHIVED", 2);
  $error = FALSE;
  $error_msg = '';
  $position_available = FALSE;

  $announce_get = $_REQUEST['aid'];
  if ($_POST['form_action'] == 'chng_stat') {
    if ($announce_get) {
      $condition = array('content_id' => (int)$announce_get);
      $announce = Announcement::load_announcements_array($params = NULL, $condition);
      $announcement = new Announcement();
      $announcement->content_id = $announce_get;
      $announcement->title = $announce[0]['title'];
      $announcement->body = $announce[0]['body'];
      $announcement->position = $announce[0]['position'];
      if ($announce[0]['status'] == 'Live') {
        $announcement->status = ARCHIVED;
      } else {
        $position_available = Announcement::is_position_available($announce[0]['position']);
        if ($position_available == TRUE) {
          $announcement->status = LIVE;
        } else {
          $announcement->status = ARCHIVED;
          $error_msg = 'Could not change status.<br> Reason: Position not available';
        } 
      }
      if(empty($error_msg)) {
        try {
         $announcement->save();
        } catch (PAException $e) {
          $error_msg = $e->message;
        } 
      }
    }
  }  
  
  //getting announcement id if any
 else {
  if ($_REQUEST['aid']) {
    $aid = $_REQUEST['aid'];
    if ($aid) {
     $condition = array('content_id' => (int)$aid);
     $announce = Announcement::load_announcements_array($params = NULL, $condition) ;
      $form_data['title'] = $announce[0]['title'];
      $form_data['body'] = $announce[0]['body'];
      $form_data['announcement_time'] = $announce[0]['announcement_time'];
      $form_data['position'] = $announce[0]['position'];
    }
  } 
  //if announcement is submitted and $error is false yet
   if ($_POST['PublishNow'] && !$error) {
   filter_all_post($_POST);  
   //validate posted values
   $value_to_validate = array('title'=>'Title','body'=>'Body'
                              );
    foreach ($value_to_validate as $key=>$value) {
      $_POST[$key] = trim($_POST[$key]);
      if (empty($_POST[$key])) {
        $error_msg .= $value.' can not be empty<br>';
      }
    }
    // saving value if announcement fails for any reason
    $vartoset = array('title','body','position','action');
    filter_all_post($_POST);//filters all data of html
    for ($i = 0; $i < count($vartoset); $i += 1) {
      $var = $vartoset[$i];
      if (!empty($_POST[$var])) {
        $form_data[$var] = $_POST[$var];
      }
    }
    //if no error so far
    if (!$error_msg) {
      $announcement = new Announcement();
      $position_available = FALSE;
        if ($aid) {
          $condition1['content_id'] = $aid;
          $position_available = Announcement::is_position_available( $_POST['position'], $condition1);
        } else {
          $position_available = Announcement::is_position_available( $_POST['position']);
        }
        if ($position_available == TRUE) {
          $announcement->status = LIVE;
        } else {
          $announcement->status = ARCHIVED;
        }
        
       
      //for parent ie content class
      $announcement->title = $_POST['title'];
      $announcement->body = $_POST['body'];
      $announcement->author_id = $_SESSION['user']['id'];
      $announcement->type = ANNOUNCEMENT;
      
      // for child ie announcement class
      $announcement->position = $_POST['position'];
      //for publishing time
      $announcement->announcement_time = time();
      $announcement->is_active = 1;
      if ($aid){
        $announcement->content_id = $aid;
      } 
      $announcement->save(); 
      if ($announcement->status == ARCHIVED) {
       $error_msg = "Announcement could not be made live, it is saved as Archieved";
      }
      $error_msg .= "<br>Announcement has been made succesfully";
      $form_data = NULL;
      if (!$aid) {//send notification only if announcement is created
        $params['aid'] = $res['aid'];
        auto_email_notification('announcement', $params);
      } 
      header("Location: ". PA::$url ."/network_announcement.php");
    }//if no error
  } // $_post
} // else end
  
  function setup_module($column, $module, $obj) {
  global $paging,$form_data;
  
    $obj->Paging["page"] = $paging["page"];
    $obj->Paging["show"] = 10;
    $obj->mode = PUB;
    $obj->form_data = $form_data;
    
  }
  
  
  $page = new PageRenderer("setup_module", PAGE_NETWORK_ANNOUNCEMENT, "Network Announcement", 'container_one_column.tpl','header.tpl',PRI,HOMEPAGE,$network_info);
  if (!empty($error_msg)) {  
    $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
    $msg_tpl->set('message', $error_msg);
    $m = $msg_tpl->fetch();
    $page->add_module("middle", "top", $m);
  }
  
  $css_path = PA::$theme_url . '/layout.css';
  $page->add_header_css($css_path);
  $css_path = PA::$theme_url . '/network_skin.css';
  $page->add_header_css($css_path);
  
  echo $page->render();
?>
  