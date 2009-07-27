<?php

/*

 The $db_table_mappings contains an entry for each table that needs to
 have its name rewritten when accessing a network other than the
 default.  (e.g. 'users' is shared by all networks, so there is no
 entry for 'users', but there is a different 'contents' for each
 network - 'one_contents', 'two_contents', etc - so it has an entry.)

 If you want to override any of these (e.g. if you want to move a
 table into another database), create a
 networks/networkname/local_config.php file for your network and add a
 $user_table_mappings array, like this:

  $user_table_mappings = array(
   'tablename' => 'newtablename',
  );

 e.g.

  $user_table_mappings = array(
   'contents' => 'myotherdatabase.othercontents',
  );

 See db/Dal/Dal.php for the code that uses $db_table_mappings (and
 $user_table_mappings).

*/

global $db_table_mappings; // we need to declare this explicitly as occasionally this file is included inside a function!

$db_table_mappings = array(
'activity_types' =>'`/%db%/`.`/%network_name_%/activity_types`',
'advertisements' =>'`/%db%/`.`/%network_name_%/advertisements`',
'announcements' =>'`/%db%/`.`/%network_name_%/announcements`',
'audios' =>'`/%db%/`.`/%network_name_%/audios`',
'blog_badges' =>'`/%db%/`.`/%network_name_%/blog_badges`',
//'boardmessages' =>'`/%db%/`.`/%network_name_%/boardmessages`',
'categories' =>'`/%db%/`.`/%network_name_%/categories`',
//'categories_boardmessages' =>'`/%db%/`.`/%network_name_%/categories_boardmessages`',
'comments' =>'`/%db%/`.`/%network_name_%/comments`',
'contentcollections' =>'`/%db%/`.`/%network_name_%/contentcollections`',
'contentcollections_albumtype' =>'`/%db%/`.`/%network_name_%/contentcollections_albumtype`',
'contentcollection_types' =>'`/%db%/`.`/%network_name_%/contentcollection_types`',
'contents' =>'`/%db%/`.`/%network_name_%/contents`',
'contents_sbmicrocontents' =>'`/%db%/`.`/%network_name_%/contents_sbmicrocontents`',
'content_routing_destinations' =>'`/%db%/`.`/%network_name_%/content_routing_destinations`',
'content_types' =>'`/%db%/`.`/%network_name_%/content_types`',
//'email_messages' =>'`/%db%/`.`/%network_name_%/email_messages`',
'entities'=>'`/%db%/`.`/%network_name_%/entities`',
'entityattributes'=>'`/%db%/`.`/%network_name_%/entityattributes`',
'entityrelationattributes'=>'`/%db%/`.`/%network_name_%/entityrelationattributes`',
'entityrelations'=>'`/%db%/`.`/%network_name_%/entityrelations`',
'footer_links' =>'`/%db%/`.`/%network_name_%/footer_links`',
//'forgot_password' =>'`/%db%/`.forgot_password`',
'groups' =>'`/%db%/`.`/%network_name_%/groups`',
'groups_users' =>'`/%db%/`.`/%network_name_%/groups_users`',
'images' =>'`/%db%/`.`/%network_name_%/images`',
'invitations' =>'`/%db%/`.`/%network_name_%/invitations`',
'items'=>'`/%db%/`.`/%network_name_%/items`',
'items_collection'=>'`/%db%/`.`/%network_name_%/items_collection`',
'items_content'=>'`/%db%/`.`/%network_name_%/items_content`',
'items_data'=>'`/%db%/`.`/%network_name_%/items_data`',
'items_user'=>'`/%db%/`.`/%network_name_%/items_user`',
'item_messages'=>'`/%db%/`.`/%network_name_%/item_messages`',
//'links' =>'`/%db%/`.links`',
//'linkcategories' =>'`/%db%/`.linkcategories`',
//'message_folder' =>'`/%db%/`.message_folder`',
'media_videos'  =>'`/%db%/`.`/%network_name_%/media_videos`',
'moderation_queue' =>'`/%db%/`.`/%network_name_%/moderation_queue`',
'modules_settings' =>'`/%db%/`.`/%network_name_%/modules_settings`',
//'moduledata'=>'`/%db%/`.`/%network_name_%/moduledata`',
//'networks' =>'`/%db%/`.networks`',
//'networks_users'=>'`/%db%/`.networks_users`',
'network_links'=>'`/%db%/`.`/%network_name_%/network_links`',
'network_linkcategories'=>'`/%db%/`.`/%network_name_%/network_linkcategories`',
'page_default_settings' =>'`/%db%/`.`/%network_name_%/page_default_settings`',
'page_settings' =>'`/%db%/`.`/%network_name_%/page_settings`',
// Not sure at the moment, but personas and persona_properties might
// want to be per-network.  Currently we just have one of each table
// per installation.  To change to per-network, uncomment the
// following two lines:
//'personas' => '`/%db%/`.`/%network_name_%/personas`',
//'persona_properties' => '`/%db%/`.`/%network_name_%/persona_properties`',
//'private_messages' =>'`/%db%/`.private_messages`',
//'relation_classifications' =>'`/%db%/`.relation_classifications`',
//'relations' =>'`/%db%/`.relations`',
//'roles' =>'`/%db%/`.roles`',
'rating'=>'`/%db%/`.`/%network_name_%/rating`',
'recent_media_track'=>'`/%db%/`.`/%network_name_%/recent_media_track`',
'reviews'=>'`/%db%/`.`/%network_name_%/reviews`',
'report_abuse' =>'`/%db%/`.`/%network_name_%/report_abuse`',
'roles'  =>'`/%db%/`.`/%network_name_%/roles`',
//'sbmicrocontent_types' =>'`/%db%/`.sbmicrocontent_types`',
'static_pages'=>'`/%db%/`.`/%network_name_%/static_pages`',
'supergroup_data'=>'`/%db%/`.`/%network_name_%/supergroup_data`',
'supergroup_settings'=>'`/%db%/`.`/%network_name_%/supergroup_settings`',
//'tags' =>'`/%db%/`.tags`',
//'tags_users' =>'`/%db%/`.tags_users`',
'tags_contentcollections' =>'`/%db%/`.`/%network_name_%/tags_contentcollections`',
'tags_contents' =>'`/%db%/`.`/%network_name_%/tags_contents`',
'tags_item'=>'`/%db%/`.`/%network_name_%/tags_item`',
'tasks_roles'  =>'`/%db%/`.`/%network_name_%/tasks_roles`',
'trackback_contents' =>'`/%db%/`.`/%network_name_%/trackback_contents`',
//'user_message_folder' =>'`/%db%/`.user_message_folder`',
//'user_profile_data'  =>'`/%db%/`.user_profile_data`',
//'users' =>'`/%db%/`.users`',
'users_roles'  =>'`/%db%/`.`/%network_name_%/users_roles`',
'user_popularity'=>'`/%db%/`.`/%network_name_%/user_popularity`',
'videos' =>'`/%db%/`.`/%network_name_%/videos`',
);
?>