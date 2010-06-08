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
$use_theme = 'Beta';

include_once("web/includes/page.php");
require_once "web/includes/functions/user_page_functions.php";
require_once "api/Image/Image.php";
require_once "api/Audio/Audio.php";
require_once "ext/Video/Video.php";

// for query count
global $query_count_on_page;
$query_count_on_page = 0;

$msg = NULL;

// deleting images
// TODO: add code for sb posts delete as well
try {
  if(isset($_GET['action']) && $_GET['action'] == 'delete') {
    if (isset($_POST['delete_type'])) {
      if ($_POST['delete_type'] == 'image') {
        foreach ($_POST as $k=>$v) {
          $delete_pics_id[] = $k;
        }
        array_pop($delete_pics_id);
        array_pop($delete_pics_id);

        foreach ($delete_pics_id as $id) {
			    if (Content::is_owner_of(PA::$login_user->user_id, $cid)) {
	          $new_image = new Image();
  	        $new_image->content_id = $cid;
    	      $new_image->delete($cid);
			    } else {
    				$msg .= "You are not owner of image " . $cid . "<br/>\n";
		    	}
        }
      }

      if ($_POST['delete_type'] == 'audio') {
        foreach ($_POST as $k=>$v) {
          $delete_audios_id[] = $k;
        }
        array_pop($delete_audios_id);
        array_pop($delete_audios_id);
        foreach ($delete_audios_id as $cid) {
			    if (Content::is_owner_of(PA::$login_user->user_id, $cid)) {
	          $new_image = new Audio();
  	        $new_image->content_id = $cid;
    	      $new_image->delete($cid);
			    } else {
    				$msg .= "You are not owner of audio " . $cid . "<br/>\n";
		    	}
        }
      }

      if ($_POST['delete_type'] == 'video') {
        foreach ($_POST as $k=>$v) {
          $delete_videos_id[] = $k;
        }
        array_pop($delete_videos_id);
        array_pop($delete_videos_id);
        foreach ($delete_videos_id as $cid) {
			    if (Content::is_owner_of(PA::$login_user->user_id, $cid)) {
	          $new_image = new Video();
  	        $new_image->content_id = $cid;
    	      $new_image->delete($cid);
			    } else {
    				$msg .= "You are not owner of video " . $cid . "<br/>\n";
		    	}
        }
      }
    }
  }
}
catch (PAException $e) {
   $msg = "$e->message";
   $error = TRUE;
}

// TODO : call delete of individual content types
// e.g. Image:: delete();
if (isset($_GET['action']) && $_GET['action'] == "delete") {
  $content_id = $_GET["cid"];
    // added check for ownership --Martin
    if (Content::is_owner_of(PA::$login_user->user_id, $content_id)) {
	    // $msg .= "Deleting cid " . $content_id . "<br/>\n";
	    Content::delete_by_id($content_id);
    } else {
    	$msg .= "You are not owner of cid " . $content_id . "<br/>\n";
    }
}
// Code for Deleting the Content Other than Media: Starts
if(!empty($_POST["delete_content"])) {
  $id_array = $_POST["delete_content"];

  for($counter = 0; $counter < count($id_array); $counter++) {
    // added check for ownership --Martin
    if (Content::is_owner_of(PA::$login_user->user_id, $id_array[$counter])) {
	    Content::delete_by_id($id_array[$counter]);
    } else {
    	$msg .= "You are not owner of cid " . $id_array[$counter] . "<br/>\n";
    }
  }
}

function setup_module($column, $moduleName, $obj) {
    global $content_type, $users,$uid,$_GET,$user;

    switch ($column) {
    case 'left':
        $obj->mode = PRI;
        if ($moduleName != 'LogoModule') {
            $obj->block_type = HOMEPAGE;
         }
         if ($moduleName == 'RecentCommentsModule') {
            $obj->cid = $_REQUEST['cid'];
            $obj->block_type = HOMEPAGE;
            $obj->mode = PRI;
          }
         
    break;
    case 'middle':
          $obj->content_id = @$_REQUEST['cid'];
          $obj->mode = PUB;
          $obj->uid = $_SESSION['user']['id'];
          $obj->type = get_content_type(@$_GET['type']);
          $obj->block_type = 'media_management';
          $obj->Paging["page"] = 1;
          $obj->Paging["show"] = 10;

    break;

    case 'right':
        $obj->mode = PRI;         
    break;
    }
}

$onload = NULL;
if(!empty($_POST['delete_type'])) {
  $onload = "show_content ('".$_POST['delete_type']."-content', '');";
}

$page = new PageRenderer("setup_module", PAGE_MEDIA_MANAGEMENT, "Media Management", "container_one_column_media_gallery.tpl", "header.tpl", PUB, HOMEPAGE,  PA::$network_info, $onload);
uihelper_error_msg(@$_GET['msg_id']);
uihelper_get_network_style();
if (!empty($msg)) uihelper_error_msg($msg);

echo $page->render();

?>