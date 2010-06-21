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
 * Project:     PeopleAggregator: a social network developement platform
 * File:        ajax_links.php, ajax file to display links module
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file gets called from links module. It renders html
 *              for the links(inner html)
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * TODO:      Need to call here link module to generate inner html
 */
require_once dirname(__FILE__)."/../config.inc";
$login_required = TRUE;
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once 'web/includes/functions/functions.php';
require_once 'web/includes/uihelper.php';
require_once "api/Links/Links.php";
include_once "api/Validation/Validation.php";
check_session(1);
$message = array();
if(!empty($_POST)) {
    filter_all_post($_POST);
}

/**
* Deleting link(s) from a particular category.
*/
if(!empty($_POST) && $_POST['form_action'] == 'remove_links') {
    $Links          = new Links();
    $Links->link_id = explode(",", $_POST['link_id']);
    $Links->user_id = $_SESSION['user']['id'];
    try {
        $Links->delete_link();
        $links_count = count($Links->link_id);
        $msg         = ($links_count > 1) ? $links_count.' links have been' : '1 link has been';
        $msg        .= ' deleted successfully';
        $json_string = '{ "errors" : "'.$msg.'", "updated_category_id" : '.$_POST['category_id'].'}';
    }
    catch(PAException$e) {
        $json_string = '{ "is_error" : true, "errors" : "'.$e->message.'"}';
    }
    print $json_string;

    /**
    * Creating a new link under the list
    */
}
elseif(!empty($_POST) && $_POST['form_action'] == 'create_link') {
    //TODO: check for empty fields
    $Links              = new Links();
    $Links->user_id     = $_SESSION['user']['id'];
    $Links->category_id = $_POST['category_id'];
    $Links->title       = $_POST['title'];
    $Links->url         = Validation::validate_url($_POST['url']);
    try {
        $Links->save_link();
        $json_string = '{ "errors" : "Link has been added successfully", "updated_category_id" : '.$_POST['category_id'].'}';
    }
    catch(PAException$e) {
        $json_string = '{ "is_error" : true, "errors" : "'.$e->message.'"}';
    }
    print $json_string;
}
elseif(!empty($_POST) && $_POST['form_action'] == 'remove_list') {
    $Links              = new Links();
    $Links->user_id     = $_SESSION['user']['id'];
    $Links->category_id = $_POST['category_id'];
    try {
        $Links->delete_category(true);
        $json_string = '{ "errors" : "List has been deleted successfully"}';
    }
    catch(PAException$e) {
        $json_string = '{ "is_error" : true, "errors" : "'.$e->message.'"}';
    }
    print $json_string;

    /**
    * Updating the list or category of links
    */
}
elseif(!empty($_POST) && $_POST['form_action'] == 'edit_list') {
    if(!empty($_POST['updated_category_name'])) {
        $Links                = new Links();
        $Links->user_id       = $_SESSION['user']['id'];
        $Links->category_name = $_POST['updated_category_name'];
        $Links->category_id   = $_POST['category_id'];
        try {
            $Links->update_category();
            $json_string = '{ "errors" : "List has been updated successfully", "updated_category_id" : '.$_POST['category_id'].'}';
        }
        catch(PAException$e) {
            $json_string = '{ "is_error" : true, "errors" : "List with specified name already exists"}';
        }
        print $json_string;
    }
}
elseif(!empty($_POST) && $_POST['form_action'] == 'create_list') {
    $json_string = null;
    if(!empty($_POST['category_name'])) {
        $Links                = new Links();
        $Links->user_id       = $_SESSION['user']['id'];
        $Links->category_name = $_POST['category_name'];
        try {
            $category_id = $Links->save_category();
            $json_string = '{ "errors" : "List has saved successfully", "updated_category_id" : '.$category_id.'}';
        }
        catch(PAException$e) {
            $json_string = '{ "is_error" : true, "errors" : "List with specified name already exists"}';
        }
        print $json_string;
    }

    /**
    * Code for updating the selected links
    */
}
elseif(!empty($_POST) && $_POST['form_action'] == 'update_links') {
    $link_count          = count($_POST['link_id_updated']);
    $Links               = new Links();
    $updated_links_count = 0;
    $Links->user_id      = $_SESSION['user']['id'];
    $Links->category_id  = $_POST['category_id'];
    for($counter = 0; $counter < $link_count; $counter++) {
        $Links->title   = $_POST['title_updated'][$counter];
        $Links->link_id = $_POST['link_id_updated'][$counter];
        $Links->url     = $_POST['url_updated'][$counter];
        try {
            $Links->update_link();
            ++$updated_links_count;
        }
        catch(PAException$e) {
            $message[] = 'Link with title '.$_POST['title_updated'][$counter].' already exists';
        }
    }
    $json_string = '{ "errors" : "'.uihelper_plural($updated_links_count, ' link').' out of '.$link_count.' updated successfully,'.implode(",", $message).'", "updated_category_id" : '.$_POST['category_id'].'}';
    $message[] = array(
        'category_id' => $_POST['category_id'],
    );
    array_unshift($message, uihelper_plural($updated_links_count, ' link').' out of '.$link_count.' updated successfully');
    print_r($json_string);

    /**
    * Code for displaying the selected links for editing.
    */
}
elseif(!empty($_GET['action']) && $_GET['action'] == 'edit_list_link') {
    if(!empty($_GET['link_ids'])) {
        $Links          = new Links();
        $Links->user_id = $_SESSION['user']['id'];
        $Links->link_id = explode("_", $_GET['link_ids']);
        $links          = $Links->load_link();
        $edit_form      = '<fieldset class="center_box">
      <legend>Edit links</legend>';
        $links_count    = count($links);
        $category_id    = null;
        if($links_count > 0) {
            foreach($links as $link) {
                $category_id = $link->category_id;
                $edit_form .= '<div class="field">
            <h4><label>Link caption</label></h4>
            <input type="text" name="title_updated[]" class="text longer" value="'.$link->title.'"/>
          </div>          
          <div class="field_big">
            <h4><label>URL</label></h4>
            <input type="text" name="url_updated[]" class="text longer" value="'.$link->url.'"/>            
            <input type="hidden" name="link_id_updated[]" value="'.$link->link_id.'" />
          </div>';
            }
        }
        $edit_form .= '<div class="button_position">
        <input type="hidden" name="category_id" value="'.$category_id.'" />
        <input type="button" name="edit_list_links" value="Update" class="buttonbar"  onclick="javascript: list_links.edit_action()" />
      </div>
    </fieldset>';
        print $edit_form;
    }
}
elseif(!empty($_GET['action']) && $_GET['action'] == 'edit_list') {
    $Links = new Links();
    $params = array(
        'category_id' => $_GET['category_id'],
    );
    $category = $Links->load_category($params);
    $edit_form = '<fieldset class="center_box">
      <legend>Edit list</legend>
      <div class="field">
        <h4><label>List name</label></h4>
        <input type="text" name="updated_category_name" class="text longer" value="'.$category[0]->category_name.'" id="updated_category_name" />
      </div>
      <div class="button_position">
        <input type="hidden" name="category_id" value="'.$category[0]->category_id.'" />
        <input type="button" name="edit_category" value="Save" class="buttonbar" onclick="javascript: list.update(true)" />
      </div>
    </fieldset>';
    print $edit_form;
}
elseif(!empty($_GET['category_id'])) {
    $condition = array(
        'category_id' => $_GET['category_id'],
        'is_active' => 1,
    );
    $params_array = array(
        'user_id' => $_SESSION['user']['id'],
    );
    $Links = new Links();
    $Links->set_params($params_array);
    $result_array = $Links->load_link($condition);
    $return_string = '';
    if(count($result_array) > 0) {
        for($counter = 0; $counter < count($result_array); $counter++) {
            $return_string .= '<div class="field"><h4><label><input type="checkbox" name="link_id[]" id="link_id_'.$result_array[$counter]->link_id.'" value="'.$result_array[$counter]->link_id.'" /></label><a href="'.$result_array[$counter]->url.'" target="_blank">'.$result_array[$counter]->title.'</a></h4><span style="font-size:12px;">'.$result_array[$counter]->url.'</span></div>';
        }
        $return_string .= '<div class="button_position">
            <input type="button" name="btn_new_list"  class="buttonbar" value="Create new"  onclick="javascript: list_links.create();" />            
            <input type="button" name="btn_edit_list" value="Edit selected links"  onclick="javascript: list_links.edit();" class="buttonbar" />
            <input type="button" name="btn_delete_list" value="Delete selected links"  onclick="javascript: list_links.remove()" class="buttonbar"  />
           </div>';
    }
    else {
        $return_string = "<center>There are no links under this list. Click <a href=\"javascript: list_links.create();\">here</a> to add</center> ";
    }
    print $return_string;
}
?>