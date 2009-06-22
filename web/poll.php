<?php 
  //anonymous user can not view this page;
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta';
  include_once("web/includes/page.php");  
  require_once "web/includes/network.inc.php";
  require_once "api/Validation/Validation.php";
  require_once "ext/Poll/Poll.php";
  $error = FALSE;
  $authorization_required = TRUE;

  $message = @$_REQUEST['msg'];

global $flag , $type;
if (!$error ) {
 if(!empty($_GET['type'])) {
   $type = htmlspecialchars($_GET['type']);
 }
 if(!empty($_POST['submit'])) {
   $obj = new Poll();
   $obj->poll_id = $_POST['poll'];
   $obj->prev_changed = $_POST['prev_poll_changed'];
   $obj->prev_poll_id = $_POST['prev_poll_id'];
   $obj->save_current();
   header("Location: ". PA::$url . PA_ROUTE_HOME_PAGE);
 }
 if(!empty($_GET['action']) && $_GET['action']== "delete") {
   $obj = new poll();
   $p_id = $_GET['id'];
   $c_id = $_GET['cid'];
   $obj->delete_poll($p_id, $c_id);
   header("Location: ". PA::$url ."/poll.php?type=select");
 }
 if(!empty($_POST['create'])){
   $poll_topic = $_POST['topic'];
   $cnt = $_POST['num_option'];
   $poll_option = array();
   for ($i =1;$i<=$cnt;$i++) {
   $poll_option['option'.$i] = $_POST['option'.$i];
   }
   $option = serialize($poll_option);
   $obj = new POll(); 
   $obj->author_id = $login_uid;
   $obj->type = POLL;
   $obj->title = $poll_topic;
   $obj->body = $option;
   $obj->parent_collection_id = -1;
   $obj->user_id = $login_uid;
   $obj->options = $option;
   $obj->is_active = INACTIVE;
   $obj->save_poll();
   header("Location: ". PA::$url ."/poll.php?type=select");
 }
}
function setup_module($column, $moduleName, $obj) {
  global $flag , $type;
  switch ($moduleName) {
    case 'NetworkLeftLinksModule':
     break;
    case 'SelectPollModule':
      $obj->mode = $type;
      break;
  }
}

$onload = '';
if ($type != 'select') {
  $onload = 'javascript: ajax_method_poll_options();';
}

$page = new PageRenderer("setup_module",PAGE_POLL, "Poll", "container_two_column.tpl", "header.tpl", PUB, HOMEPAGE, $network_info, $onload);

uihelper_error_msg(@$message);
$page->html_body_attributes = 'class="no_second_tier" ';
$css_array = get_network_css();
if (is_array($css_array)) {
  foreach ($css_array as $key => $value) {
    $page->add_header_css($value);
  }
}
echo $page->render();

?>