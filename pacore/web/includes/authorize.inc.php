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

/*
   this script used by:
     /opt/lampp/htdocs/pa/pacore/web/assign_tasks.php
     /opt/lampp/htdocs/pa/pacore/web/configure_splash_page.php
     /opt/lampp/htdocs/pa/pacore/web/manage_ad_center.php
     /opt/lampp/htdocs/pa/pacore/web/manage_category.php
     /opt/lampp/htdocs/pa/pacore/web/manage_comments.php
     /opt/lampp/htdocs/pa/pacore/web/manage_domain_name.php
     /opt/lampp/htdocs/pa/pacore/web/manage_emblem.php
     /opt/lampp/htdocs/pa/pacore/web/manage_footer_links.php
     /opt/lampp/htdocs/pa/pacore/web/manage_groups_forum.php
     /opt/lampp/htdocs/pa/pacore/web/manage_groups.php
     /opt/lampp/htdocs/pa/pacore/web/manage_profanity.php
     /opt/lampp/htdocs/pa/pacore/web/manage_questions.php
     /opt/lampp/htdocs/pa/pacore/web/manage_taketour.php
     /opt/lampp/htdocs/pa/pacore/web/manage_textpads.php
     /opt/lampp/htdocs/pa/pacore/web/manage_user.php
     /opt/lampp/htdocs/pa/pacore/web/network_bulletins.php
     /opt/lampp/htdocs/pa/pacore/web/network_calendar.php
     /opt/lampp/htdocs/pa/pacore/web/network_feature.php
     /opt/lampp/htdocs/pa/pacore/web/network_links.php
     /opt/lampp/htdocs/pa/pacore/web/network_manage_content.php
     /opt/lampp/htdocs/pa/pacore/web/network_moderate_content.php
     /opt/lampp/htdocs/pa/pacore/web/includes/blocks/network_module_selector.php
     /opt/lampp/htdocs/pa/pacore/web/new_user_by_admin.php
     /opt/lampp/htdocs/pa/pacore/web/poll.php
     /opt/lampp/htdocs/pa/pacore/web/config/pages/poll.xml
     /opt/lampp/htdocs/pa/pacore/web/post_content.php
     /opt/lampp/htdocs/pa/pacore/web/roles.php
*/

/*

    function current_user_can() used by:
      /opt/lampp/htdocs/pa/pacore/web/assign_tasks.php           **
      /opt/lampp/htdocs/pa/pacore/web/configure_network.php      ** obsolete page ??
      /opt/lampp/htdocs/pa/pacore/web/configure_splash_page.php  **
      /opt/lampp/htdocs/pa/pacore/web/manage_ad_center.php       **
      /opt/lampp/htdocs/pa/pacore/web/manage_category.php        **
      /opt/lampp/htdocs/pa/pacore/web/manage_comments.php        **
      /opt/lampp/htdocs/pa/pacore/web/manage_domain_name.php     ** 
      /opt/lampp/htdocs/pa/pacore/web/manage_emblem.php          **
      /opt/lampp/htdocs/pa/pacore/web/manage_footer_links.php    ** 
      /opt/lampp/htdocs/pa/pacore/web/manage_group_content.php   **
      /opt/lampp/htdocs/pa/pacore/web/manage_groups_forum.php    **
      /opt/lampp/htdocs/pa/pacore/web/manage_groups.php          **
      /opt/lampp/htdocs/pa/pacore/web/manage_profanity.php       **
      /opt/lampp/htdocs/pa/pacore/web/manage_questions.php       **
      /opt/lampp/htdocs/pa/pacore/web/manage_static_pages.php    **
      /opt/lampp/htdocs/pa/pacore/web/manage_taketour.php        **
      /opt/lampp/htdocs/pa/pacore/web/manage_textpads.php         **
      /opt/lampp/htdocs/pa/pacore/web/manage_user.php            **
      /opt/lampp/htdocs/pa/pacore/web/misreports.php             **
      /opt/lampp/htdocs/pa/pacore/web/moderate_users.php         **
      /opt/lampp/htdocs/pa/pacore/web/module_selector.php        ** obsolete page ??
      /opt/lampp/htdocs/pa/pacore/web/network_announcement.php   ** not used - obsolete??
      /opt/lampp/htdocs/pa/pacore/web/network_bulletins.php      **
      /opt/lampp/htdocs/pa/pacore/web/network_calendar.php       ** 
      /opt/lampp/htdocs/pa/pacore/web/network_feature.php        **
      /opt/lampp/htdocs/pa/pacore/web/includes/network.inc.php   **
      /opt/lampp/htdocs/pa/pacore/web/network_links.php          **
      /opt/lampp/htdocs/pa/pacore/web/network_manage_content.php **
      /opt/lampp/htdocs/pa/pacore/web/network_moderate_content.php **
      /opt/lampp/htdocs/pa/pacore/web/includes/blocks/network_module_selector.php **
      /opt/lampp/htdocs/pa/pacore/web/network_statistics.php      **
      /opt/lampp/htdocs/pa/pacore/web/new_user_by_admin.php       **
      /opt/lampp/htdocs/pa/pacore/web/poll.php                    **
      /opt/lampp/htdocs/pa/pacore/web/config/pages/poll.xml       **
      /opt/lampp/htdocs/pa/pacore/web/ranking.php                 ** obsolete page??
      /opt/lampp/htdocs/pa/pacore/web/roles.php                   ** not used - obsolete!  
*/



/*
//$authorization_required variable should be set to TRUE or FALSE before
//including this file
//this file is used to check the roles - tasks permissions 
if (!isset($authorization_required)) {
    throw new PAException("", "The \$authorization_required variable must be set before include()ing page.php!");
}
require_once "api/Roles/Roles.php";
require_once "api/Tasks/Tasks.php";
$task = Tasks::get_instance();
$tasks = $task->get_tasks();//getting list of all the tasks
$task_id = '';
//find task id
foreach ($tasks as $task_obj) {
  if($page_task == $task_obj->task_value){
    $task_id = $task_obj->id;
  }
}
*/
/*
  var $page_task used by:
      /opt/lampp/htdocs/pa/pacore/web/assign_tasks.php
      /opt/lampp/htdocs/pa/pacore/web/configure_splash_page.php
      /opt/lampp/htdocs/pa/pacore/web/manage_ad_center.php
      /opt/lampp/htdocs/pa/pacore/web/manage_category.php
      /opt/lampp/htdocs/pa/pacore/web/manage_comments.php
      /opt/lampp/htdocs/pa/pacore/web/manage_domain_name.php
      /opt/lampp/htdocs/pa/pacore/web/manage_emblem.php
      /opt/lampp/htdocs/pa/pacore/web/manage_footer_links.php
      /opt/lampp/htdocs/pa/pacore/web/manage_groups_forum.php
      /opt/lampp/htdocs/pa/pacore/web/manage_groups.php
      /opt/lampp/htdocs/pa/pacore/web/manage_profanity.php
      /opt/lampp/htdocs/pa/pacore/web/manage_questions.php
      /opt/lampp/htdocs/pa/pacore/web/manage_taketour.php
      /opt/lampp/htdocs/pa/pacore/web/manage_textpads.php
      /opt/lampp/htdocs/pa/pacore/web/manage_user.php
      /opt/lampp/htdocs/pa/pacore/web/network_bulletins.php
      /opt/lampp/htdocs/pa/pacore/web/network_calendar.php
      /opt/lampp/htdocs/pa/pacore/web/network_feature.php
      /opt/lampp/htdocs/pa/pacore/web/network_links.php
      /opt/lampp/htdocs/pa/pacore/web/network_manage_content.php
      /opt/lampp/htdocs/pa/pacore/web/network_moderate_content.php
      /opt/lampp/htdocs/pa/pacore/web/includes/blocks/network_module_selector.php
      /opt/lampp/htdocs/pa/pacore/web/new_user_by_admin.php
      /opt/lampp/htdocs/pa/pacore/web/poll.php
      /opt/lampp/htdocs/pa/pacore/web/config/pages/poll.xml
      /opt/lampp/htdocs/pa/pacore/web/post_content.php
      /opt/lampp/htdocs/pa/pacore/web/roles.php
*/
/*

$task_perm = Roles::check_permission(PA::$login_uid, $task_id);

// deprecated - use Roles::check_permission_by_value(PA::$login_uid, 'task value') instead.
function check_user_permission($task_id_or_value) {
  if(empty(PA::$login_uid)) return FALSE;
  
  if(is_numeric($task_id_or_value)) {
    $task_id = $task_id_or_value;
  } else {
    $task_id = Tasks::get_id_from_task_value($task_id_or_value);
  }

  if (empty($task_id)) throw new PAException(INVALID_ID, "Invalid task ID or value: ".print_r($task_id_or_value, TRUE));
  
  return Roles::check_permission(PA::$login_uid, $task_id);
}
*/
?>