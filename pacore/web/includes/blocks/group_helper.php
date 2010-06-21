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
<?
/*this is inside file included in following files
1. group.php

This file contains common code to get group information
*/
$is_member          = FALSE;
$is_admin           = FALSE;
$content_access     = TRUE;
$is_invite          = FALSE;
$skip_group_modules = FALSE;
$gid                = NULL;
if(!empty($_REQUEST['gid'])) {
    $gid = (int) $_REQUEST['gid'];
}
if(strstr($_SERVER['PHP_SELF'], FILE_FORUM_MESSAGES)) {
    $gid = (int) $_REQUEST['ccid'];
}
$collection = $group = ContentCollection::load_collection((int) $gid, PA::$login_uid);
if($collection->type == GROUP_COLLECTION_TYPE) {
    $access = $group->access_type;
    if($group->access_type == $group->ACCESS_PRIVATE) {
        if(PA::$login_uid) {
            //if private group
            if(GROUP::member_exists($gid, PA::$login_uid)) {
                $skip_group_modules = FALSE;
            }
            else {
                // haha no way for non member of group
                $skip_group_modules = TRUE;
            }
        }
        else {
            //haha no way for anonymous user
            $skip_group_modules = TRUE;
        }
        $access_type = 'Private';
    }
    else {
        $access_type = 'Public';
    }
    if($group->reg_type == $group->REG_OPEN) {
        $access_type .= ' Open';
    }
    else {
        $access_type .= ' Moderated';
    }
    if(Group::is_admin((int) $gid, (int) PA::$login_uid)) {
        $is_admin = TRUE;
    }
    $members = $group->get_members($cnt = FALSE, 5, 1, 'created', 'DESC', FALSE);
    //$members = $group->get_members();
    $group_details                  = array();
    $group_details['collection_id'] = $group->collection_id;
    $group_details['type']          = $group->type;
    $group_details['author_id']     = $group->author_id;
    $user                           = new User();
    $user->load((int) $group->author_id);
    $first_name                       = $user->first_name;
    $last_name                        = $user->last_name;
    $login_name                       = $user->login_name;
    $group_details['author_name']     = $login_name;
    $group_details['author_picture']  = $user->picture;
    $group_details['title']           = $group->title;
    $group_details['description']     = $group->description;
    $group_details['is_active']       = $group->is_active;
    $group_details['picture']         = $group->picture;
    $group_details['desktop_picture'] = @$group->desktop_picture;
    $group_details['created']         = PA::datetime($group->created, 'long', 'short');
    // date("F d, Y h:i A", $group->created);
    $group_details['changed']     = $group->changed;
    $group_details['category_id'] = $group->category_id;
    $cat_obj                      = new Category();
    $cat_obj->set_category_id($group->category_id);
    $cat_obj->load();
    $cat_name                              = stripslashes($cat_obj->name);
    $cat_description                       = stripslashes($cat_obj->description);
    $group_details['category_name']        = $cat_name;
    $group_details['category_description'] = $cat_description;
    $group_details['members']              = Group::get_member_count($gid);
    $group_details['access_type']          = $access_type;
    $group_details['is_admin']             = $is_admin;
    //////////////////get details of group EOF
    if(is_array($members)) {
        $count = count($members);
        $users_data = array();
        foreach($members as $member) {
            $count_relations = Relation::get_relations($member['user_id'], APPROVED, PA::$network_info->network_id);
            $user = new User();
            $user->load((int) $member['user_id']);
            $login_name = $user->login_name;
            $user_picture = $user->picture;
            $users_data[] = array(
                'user_id'         => $member['user_id'],
                'picture'         => $user_picture,
                'login_name'      => $login_name,
                'no_of_relations' => count($count_relations),
            );
        }
        $final_array = array(
            'users_data' => $users_data,
            'total_users' => $count,
        );
    }
    $users = $final_array;
    if(Group::member_exists((int) $group->collection_id, (int) PA::$login_uid)) {
        $is_member = TRUE;
    }
    $group_details['is_member'] = $is_member;
    //..get details of group ends
}

function members_to_array($members) {
    $out = array();
    foreach($members as $member) {
        $out[] = $member['user_id'];
    }
    return $out;
}

function get_networks_users_id() {
    $users     = array();
    $users_ids = array();
    $users     = Network::get_members(array('network_id' => PA::$network_info->network_id));
    if($users['total_users']) {
        for($i = 0; $i < $users['total_users']; $i++) {
            $users_ids[] = $users['users_data'][$i]['user_id'];
        }
    }
    return $users_ids;
}
?>
