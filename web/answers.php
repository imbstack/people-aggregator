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
/**
 * Project:     PeopleAggregator: a social network developement platform
 * File:        write_testimonial.php, web file to write testimonials
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file displays the main page of the site. It uses
 *              page renderer to display the block modules
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;//for session protection pages 
$use_theme = 'Beta';
include_once("web/includes/page.php");
$parameter = '';

$parameter .= js_includes('common.js');
global $query_count_on_page, $page_uid;
$query_count_on_page = 0;

$error_message = NULL;
if($_POST) {
  $error_msg = NULL;
  filter_all_post($_POST);
  
  if ( trim($_POST['answer']) == '' ) {
    $error_message = __("Answer can not be left blank")."<br>";
  }
  
  if (empty($error_message)) {
    $comment = new Comment();
    // setting some variables
    $usr = get_user();
    $comment->comment = $comment->subject = $_POST['answer'];

    $comment->parent_type = TYPE_ANSWER;
    $id = $comment->parent_id = $comment->content_id = $_POST['id'];
    
    $comment->user_id = $usr->user_id;
    $comment->name = $usr->login_name;
    $comment->email = $usr->email;
     
    
    if ($comment->spam_check()) {
      $error_message = __("Sorry, your Answer cannot be posted as it looks like spam. Try removing any links to possibly suspect sites, and re-submitting.");
      Logger::log("Comment rejected by spam filter", LOGGER_ACTION);
    }
    else {
      $error_message = __('Your Answer has been posted successfully');
      $comment->save_comment();
      if ($comment->spam_state != SPAM_STATE_OK) {
        $error_message = __("Sorry, your answer cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites. Please check the links in your post, and that your name and e-mail address are correct.");
      }
      else {
        unset($_POST);
        //invalidate cache of content block as it is modified now
        if(PA::$network_info) {
          $nid = '_network_'.PA::$network_info->network_id;
        } else {
          $nid='';
        }
        //unique name
        $cache_id = 'content_'.$id.$nid; 
        CachedTemplate::invalidate_cache($cache_id);
      }
    }
    
    
  }
  
//   p($_POST);
}
/**
 *  Function : setup_module()
 *  Purpose  : call back function to set up variables 
 *             used in PageRenderer class
 *             To see how it is used see api/PageRenderer/PageRenderer.php 
 *  @param    $column - string - contains left, middle, right
 *            position of the block module 
 *  @param    $moduleName - string - contains name of the block module
 *  @param    $obj - object - object reference of the block module
 *  @return   type string - returns skip means skip the block module
 *            returns rendered html code of block module
 */

function setup_module($column, $moduleName, $obj) {
         $obj->Paging["page"] = 1;
         $obj->Paging["show"] = 10;
}   

$page = new PageRenderer("setup_module", PAGE_ANSWERS, 'Write answers',
"container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, PA::$network_info);


uihelper_error_msg($error_message);

$page->add_header_html($parameter);

$page->html_body_attributes = 'class="no_second_tier" id="pg_homepage"';

uihelper_set_user_heading($page);
echo $page->render();

?>