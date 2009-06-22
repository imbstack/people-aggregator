<?php 
error_reporting(E_ALL);
$login_required = FALSE;
include_once("web/includes/page.php");
include_once("web/Widgets/Comment/ChannelComment.php");
require_once "web/includes/classes/Pagination.php";
global $paging;
$paging_new['count'] = ChannelComment::get_channel_comments($_POST['cid'], NULL, TRUE);
$paging_new['show'] = 5;
$paging_new['page'] = $_POST['page'];
$comments = ChannelComment::get_channel_comments($_POST['cid'], NULL, FALSE, $paging_new['show'], $paging_new['page']);
$cid = $_POST['cid'];
$paging_new['extra'] = "onclick='javascript:ajax_pagination(this,$cid);return false;'";
//setting pagination
$Pagination = new Pagination;
$Pagination->setPaging($paging_new);
$page_links = $Pagination->getPageLinks();
?>

<div  id="posted_comment">
  <div class="mheader_t">
    <span class="mh1 blue"><?php echo $paging_new['count'];?></span>
  </div>
  <br><br>
  <ul>
  <?php $cnt = count($comments);
            if ($cnt > 0 ) {
            $i = 1;
              foreach ($comments as $comment) {
              $img = uihelper_resize_mk_user_img($comment['author_details']->picture, 30, 30);
              $login = User::get_login_name_from_id($comment['author_details']->user_id);
            ?>
            <li>
              <div class="asc_i"><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login ?>" target="_blank"><?php echo $img;?></a></div>
              <div>
              <div class="asc_t">
                <span class="mh3b"><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' .  $login ?>" target="_blank"><?php echo $login;?></a></span><br>
                <span class="nh4"><?php echo date('m/d/y h:i A', $comment['created']);?></span><br> 
                <span class="nh_body2"><?php echo $comment['comment'];?></span>
            </div>
             <div class="clear"></div>
           </li> 
        <?php $i++; }}?>    
      
 </ul>
</div>
<div class="page_num_holder">
  <center>
    <?php if (!empty($page_links)) { echo $page_links;}?>
    </center>
  <br><br><br>
  <img src="<?php echo PA::$url;?>/Widgets/Comment/images/stats_divider.jpg" alt="/">
  <br><br><br>              
</div>