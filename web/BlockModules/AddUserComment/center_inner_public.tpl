<?php
  global $page_uid;
  $param = array();
  $param['type'] = TYPE_USER;
  $param['div_id'] = "display_comment";
  $param['id'] = $page_uid;
  $param['show'] = 'show';
  $param['action'] = 'user_comment.php?uid='.$page_uid;
  $param['module_name'] = 'AddUserComment';
  echo uihelper_create_comment_form($param);
?>