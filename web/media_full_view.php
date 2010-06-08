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
$login_required = FALSE;

if(isset($_REQUEST['login_required']) && ($_REQUEST['login_required'] == 'true')) {
  $login_required = TRUE;
}

$use_theme = 'Beta';
include_once("web/includes/page.php");
// global var $path_prefix has been removed - please, use PA::$path static variable
 


require_once "api/ImageResize/ImageResize.php";
require_once "api/Relation/Relation.php";

require_once "ext/Image/Image.php";
require_once "ext/Audio/Audio.php";
require_once "api/Video/Video.php";
require_once "api/Content/Content.php";
require_once "web/includes/functions/user_page_functions.php";
// for query count
global $query_count_on_page;
$query_count_on_page = 0;

$show_media = NULL;
$error_msg = NULL;

if (!empty($_GET['cid'])) {
  if (!empty($_POST['rptabuse'])) { // if an abuse is reported
    require_once "web/includes/blocks/submit_abuse.php";
    if (isset($_GET['err'])) {
      $error_msg = strip_tags(urldecode($_GET['err']));
    }
  }
  $cid = $_GET['cid'];
  if($content_info = Content::load_content($cid,$login_uid)) { 
  $info = ContentCollection::get_collection_type($content_info->parent_collection_id);
  
  
  if ($content_info->type == 'Image') {
    $show_media = new Image();
  }
  else if ($content_info->type == 'Audio') {
    $show_media = new Audio();
  }
  else if ($content_info->type == 'TekVideo') {
    $show_media = new TekVideo();
  }
  else {
    die("Content ID $cid is non-media (not image, audio, or video)");
  }
   $show_media->load($cid);

  // loading tags for media
  $tags_array = Tag::load_tags_for_content ($show_media->content_id);
  $tag_string = NULL;
  if(!empty($tags_array)) {
    $t = array();
    for($i = 0;$i<count($tags_array);$i++) {
      $name = $tags_array[$i]['name'];
      $uid = (isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null; // TODO: check this, maybe -1 is better value
      $t[] = "<a class=\"management-link-module\" href=\"showcontent.php?tag_id=".$tags_array[$i]["id"]."\">".$name."</a>";
    }
      $tag_string = "<b>Tags : </b>".implode(", ", $t);
  }
  $show_media->tags = $tag_string;
 } 
}

function setup_module($column, $moduleName, $obj) {
    global $content_type, $show_media,$uid, $group_ids, $paging, $error_msg, $login_uid;
    $extra = unserialize(PA::$network_info->extra);
    $authorized_users = array();
    if(!empty($show_media)) {
      $authorized_users = array($show_media->author_id, PA::$network_info->owner_id);
      if ($extra['network_content_moderation'] == NET_YES && Network::item_exists_in_moderation($show_media->content_id, $show_media->parent_collection_id, 'content') && !in_array($login_uid, $authorized_users)) {
        $error_msg = 1001;
        return 'skip';
      }
    }  
    switch ($column) {
    case 'middle':        
        $obj->mode = PUB;        
        $obj->content_id = $_REQUEST['cid'];        
        $obj->uid = $uid;
        $obj->media_data = $show_media;
        $obj->Paging["page"] = $paging["page"];
        $obj->Paging["show"] = $paging["show"];        
    break;
    default:
    	return 'skip';
    break;
    }    
}

$header_tpl = 'header_user.tpl';
if(!empty($info) && $info['type'] == GROUP_COLLECTION_TYPE) {
  if(!empty($_REQUEST['gid']) || !empty($show_media->parent_collection_id)) {
    $header_tpl = 'header_group.tpl';
  }
}

$page = new PageRenderer("setup_module", PAGE_MEDIA_FULL_VIEW, "Media Full View", "container_one_column_media_gallery.tpl", $header_tpl, PUB, HOMEPAGE, PA::$network_info);

if (!empty($_GET['msg_id'])) {
  $error_msg = $_GET['msg_id'];
}
uihelper_error_msg($error_msg);

if(!empty($content_info)) {
  if (!empty($info) && $info['type'] == GROUP_COLLECTION_TYPE) {
    uihelper_get_group_style($content_info->parent_collection_id);
  }
  else {
    uihelper_set_user_heading($page, TRUE, $content_info->author_id);
  }
}  
echo $page->render();
?>