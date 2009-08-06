<?php
$login_required = FALSE;
include_once("web/includes/page.php");  
require_once "api/Theme/Template.php";
require_once '../ext/Album/Album.php';
require_once '../ext/Image/Image.php';
require_once '../ext/Audio/Audio.php';
require_once '../ext/Video/Video.php';


if(!$_GET['uid']) {
  $uid = $_SESSION['user']['id'];
}
else {
  $uid = $_GET['uid'];
}
if ($uid == $_SESSION['user']['id']) {
  $my_page = TRUE;
}
else {
  $my_page = FALSE;
}

if ($_SESSION['user']['id']) {
  $logged_in_id = $_SESSION['user']['id'];
}
else {
  $logged_in_id = -1;
}

if ($_SESSION['user']['id']) {
  $user = new User();
  try {
    $user->load((int)$uid);
  }
  catch (PAException $e) {
    $msg = "Error occured in retreiving user information\n";
    $msg .= "<br><center><font color=\"red\">".$e->message."</font></center>";
    $error = TRUE;
  }
}

$login_name = $user->login_name;
$first_name = $user->first_name;
$last_name = $user->last_name;
$user_picture = $user->picture;

$albums = array('description'=>$_GET['album_desc'], 'collection_id'=>$_GET['album_id']);

if ($_GET['album_type'] == 'image') {
  // loading album's all information
  $new_album = new Album(IMAGE_ALBUM);
  $content_data[$j]['album_name'] = $albums['description'];
  $content_data[$j]['album_id'] = $albums['collection_id'];
  $content_data[$j]['album_type'] = $_GET['album_type'];
  $new_album->collection_id = $albums['collection_id'];
  $image_ids = $new_album->get_contents_for_collection();
  if (!empty($image_ids)) {
    $k=0;
    for ($i=0; $i<count($image_ids); $i++) {
      $ids[$i] = $image_ids[$i]['content_id'];
    }
    $new_image = new Image();
    $data = $new_image->load_many($ids);
    foreach ($data as $d) {
      $content_data[$j]['data'][$k]['content_id'] = $d['content_id'];
      $content_data[$j]['data'][$k]['file'] = $d['image_file'];
      $content_data[$j]['data'][$k]['caption'] = $d['image_caption'];
      $content_data[$j]['data'][$k]['title'] = $d['title'];
      $content_data[$j]['data'][$k]['body'] = $d['body'];
      $content_data[$j]['data'][$k]['created'] = $d['created'];
      $k++;
    }
  }
  $j++;
}

if ($_GET['album_type'] == 'audio') {
  // loading album's all information
  $new_album = new Album(AUDIO_ALBUM);
  $content_data[$j]['album_name'] = $albums['description'];
  $content_data[$j]['album_id'] = $albums['collection_id'];
  $content_data[$j]['album_type'] = $_GET['album_type'];
  $new_album->collection_id = $albums['collection_id'];
  $image_ids = $new_album->get_contents_for_collection();
  if (!empty($image_ids)) {
    $k=0;
    for ($i=0; $i<count($image_ids); $i++) {
      $ids[$i] = $image_ids[$i]['content_id'];
    }
    $new_image = new Audio();
    $data = $new_image->load_many($ids);
    foreach ($data as $d) {
      $content_data[$j]['data'][$k]['content_id'] = $d['content_id'];
      $content_data[$j]['data'][$k]['file'] = $d['audio_file'];
      $content_data[$j]['data'][$k]['caption'] = $d['audio_caption'];
      $content_data[$j]['data'][$k]['title'] = $d['title'];
      $content_data[$j]['data'][$k]['body'] = $d['body'];
      $content_data[$j]['data'][$k]['created'] = $d['created'];
      $k++;
    }
  }
  $j++;
}

if ($_GET['album_type'] == 'video') {
  $new_album = new Album(VIDEO_ALBUM);
  $content_data[$j]['album_name'] = $albums['description'];
  $content_data[$j]['album_id'] = $albums['collection_id'];
  $content_data[$j]['album_type'] = $_GET['album_type'];
  $new_album->collection_id = $albums['collection_id'];
  $image_ids = $new_album->get_contents_for_collection();
  if (!empty($image_ids)) {
    $k=0;
    for ($i=0; $i<count($image_ids); $i++) {
      $ids[$i] = $image_ids[$i]['content_id'];
    }
    $new_image = new Video();
    $data = $new_image->load_many($ids);
    foreach ($data as $d) {
      $content_data[$j]['data'][$k]['content_id'] = $d['content_id'];
      $content_data[$j]['data'][$k]['file'] = $d['video_file'];
      $content_data[$j]['data'][$k]['caption'] = $d['video_caption'];
      $content_data[$j]['data'][$k]['title'] = $d['title'];
      $content_data[$j]['data'][$k]['body'] = $d['body'];
      $content_data[$j]['data'][$k]['created'] = $d['created'];
      $k++;
    }
  }
  $j++;
}

print html_header();
?>

<?php
$content = & new Template(CURRENT_THEME_FSPATH."/gallery_album.tpl");
if ($error == TRUE) {
  $content->set('msg', $msg);
}
$content->set('content_data', $content_data);
$content->set('user_picture', $user_picture);
$content->set('uid', $uid);
$content->set('users', $users);

$header = & new Template(CURRENT_THEME_FSPATH."/header.tpl");
if (PA::$network_info) {
  $header->set_object('network_info', PA::$network_info);
}
$header->set('user_name', $_SESSION['user']['first_name'].' '.$_SESSION['user']['last_name']);
$content->set('header', $header);

echo $content->fetch();

$footer = & new Template(CURRENT_THEME_FSPATH."/footer.tpl");
echo $footer->fetch();
?>