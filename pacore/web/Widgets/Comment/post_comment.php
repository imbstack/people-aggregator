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
$login_required = FALSE;
//error_reporting(E_ALL);
$use_theme = 'Beta';
include_once("web/includes/page.php");
include_once("web/includes/image_resize.php");
include_once("api/User/User.php");
include_once("web/Widgets/Comment/ChannelComment.php");
if(!empty($_POST)) {
    if(!empty($_POST['uid'])) {
        $msg = NULL;
        if(empty($_POST['comment'])) {
            $msg = __('Comment can not be empty');
        }
        if(empty($msg)) {
            $obj             = new ChannelComment();
            $obj->user_id    = $_POST['uid'];
            $obj->comment    = $_POST['comment'];
            $obj->slug       = $_POST['slug'];
            $obj->channel_id = $_POST['c_id'];
            $obj->is_active  = 1;
            $obj->save();
            //if the comment is posted then need to print the new set of comments.
            $channel_id   = ChannelComment::convert_slug_to_channel_id($_POST['slug']);
            $new_comments = ChannelComment::get_channel_comments($channel_id, NULL, FALSE, 5, 1);
            $cnt          = count($new_comments);
            $result       = NULL;
            if($cnt > 0) {
                $i = 1;
                $result .= '<div class="mheader_t"><span class="mh1 blue">'.$cnt.'</span></div><br><br><ul>';
                foreach($new_comments as $comment) {
                    $img     = uihelper_resize_mk_user_img($comment['author_details']->picture, 35, 35);
                    $login   = User::get_login_name_from_id($comment['author_details']->user_id);
                    $result .= '<li><div class="asc_i"><a href="'.PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login.'" target="_blank">'.$img.'</a></div><div><div class="asc_t"><span class="mh3b"><a href="'.PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login.'" target="_blank">'.$login.'</a></span><br><span class="nh_body2">'.$comment["comment"].'</span></div><div class="clear"></div></li>';
                    $i++;
                }
                $result .= '</ul>';
            }
            print($result);
            //      print(__('comment posted sucessfully'));
        }
        else {
            print($msg);
        }
    }
    else {
        print(__('Please login to comment'));
    }
}?>