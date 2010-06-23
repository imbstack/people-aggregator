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
error_reporting(E_ALL);
include_once(dirname(__FILE__)."/../../../config.inc");
include_once("web/Widgets/Comment/ChannelComment.php");
require_once "web/includes/classes/Pagination.php";
global $paging;
  //spliting the path_info to get the id and the channel id.
  $param = preg_split("|/|", $path_info);
  for($i = 2;$i<count($param);$i++) {
    list($k, $v) = explode('=', $param[$i]);
    $url_param[$k] = $v;
  }
    $channel_id = ChannelComment::convert_slug_to_channel_id($url_param['slug']);
    $paging_new['count'] = ChannelComment::get_channel_comments($channel_id, NULL, TRUE);
    $paging_new['show'] = 5;
    $paging_new['page'] = $paging['page'];
    $comments = ChannelComment::get_channel_comments($channel_id, NULL, FALSE, $paging_new['show'], $paging_new['page']);
    $paging_new['extra'] = "onclick='javascript:ajax_pagination(this,$channel_id);return false;'";
    //setting pagination
    $Pagination = new Pagination;
    $Pagination->setPaging($paging_new);
    $page_links = $Pagination->getPageLinks();
    
    $template_file = 'web/Widgets/'.$widget_name.'/widget.tpl';
    $template = & new Template($template_file);
    $template->set('url_param', $url_param);
    $template->set('login_uid', PA::$login_uid);
    $template->set('comments', $comments);
    $template->set('channel_id', $channel_id);
    $template->set('comments_count', $paging_new['count']);
    $template->set('page_links', $page_links);
    $html .= $template->fetch();
    header("Content-Type: application/x-javascript");
    echo "document.getElementById('pa_widget_comment').innerHTML = ".js_quote($html).";";

?>