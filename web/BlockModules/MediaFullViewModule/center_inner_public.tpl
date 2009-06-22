<?php global  $login_uid, $current_theme_path;
require_once "api/Permissions/PermissionsHandler.class.php";

      $param_array = array('permissions' => 'view_abuse_report_form');
      // output filtering
      $links->title = _out($links->title);
      $links->description = _out($links->body);
      switch($links->type) {
        case IMAGE: $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES;  break;
        case AUDIO: $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS; break;
        case TEK_VIDEO: $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_VIDEOS; break;
        default:
          $ret_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES;
      }

?>
 <?php
     if($links->type == TEK_VIDEO) {
      $image_show = '<script src="'.PA::$tekmedia_site_url.'/Integration/remotePlayer.php?video_id='.$links->video_id.'&preroll=true"></script>';
     } elseif ( ($links->type == AUDIO) || ($links->type == VIDEO)) {
      if (strstr($links->file_name, "http://")) {
        $src = $links->file_name;
        $file = $links->file_name;
      }
      else {
        $file = $links->file_name;
        $src = Storage::getURL($file);
      }
     $ext = explode('.',$links->file_name);
     $ext = strtolower(end($ext));

?>
<?php if ($param) { ?>

<?php if ($ext == 'mov') { ?>
    <embed src="<?=$src;?>" width=160 height=120 autoplay=false controller=true loop=false pluginspage=http://www.apple.com/quicktime/>
    </embed>
    <?php } else { ?>

     <OBJECT  ID="WinMedia" classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" CODEBASE="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"  width=160 height=120 standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">
    <PARAM NAME="FileName"  VALUE="<?=$src;?>" >
    <PARAM NAME="AutoStart" Value="false">
    <PARAM NAME="ShowControls" Value="true">
      <embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/Windows/MediaPlayer/"   src="<?=$src;?>" Name=MediaPlayer AutoStart=0 Width=160 Height=120 autostart=1  ShowControls=1>
    </embed>
    </OBJECT>

<?php }

     if (preg_match("/^http/", $file)) {
         $download_url = $file;
     } else {
	 $download_url = Storage::getDownloadURL($file);
     }

?>

    <a href="<?=$download_url?>">
     <b> Download </b>
    </a>
 <? } ?>

  <?php } else if ($links->type == IMAGE) {
     // global var $path_prefix has been removed - please, use PA::$path static variable
      if(!empty($links->image_file)) {
        if (strstr($links->image_file, "http://")) {
          $tt = $links->image_file;
	         $image_show = getimagehtml($tt, 600, 500, "", $tt);
        } else {
	         $image_show = uihelper_resize_mk_img($links->image_file, 600, 500, NULL, "", RESIZE_FIT_NO_EXPAND);
        }
      } else {
        echo "<br />" . __("Media file has been deleted from album or gallery.");
      }  
   } ?>
   <?php if ($param) { ?>
   <h1><?=stripslashes($links->title);?></h1>
   <div class="description"><?= $links->is_html ? $links->body : nl2br($links->body) ?></div>
   <?php if(isset($image_show))  echo $image_show;?>
   <div class="post_info">
      <?php if ($links->tags) { ?>
        <?=$links->tags;?>
      <? } ?>
      <?=content_date($links->created);?>
        <?php if(isset($is_author_member)) {
                if(!$is_author_member) echo '<br /><b>'. __('Author of this content is no longer a member of this group'). '</b>';
        } ?>
   </div>

   <div id="buttonbar">
     <ul>
       <?php if ($links->author_id == $login_uid) { ?>
       <li>
         <a href="<?= PA::$url . "/edit_media.php?cid=$links->content_id&type=$links->type" ?>"><?= __("Edit") ?></a>
       </li>
       <?php } ?>

       <?php if (!empty($_GET['gid'])) { ?>
       <li>
           <a href="<?= PA::$url . PA_ROUTE_GROUP . "/gid=" . $links->parent_collection_id;?>"><?= sprintf(__("Return to %s"), PA::$group_noun) ?></a>
       </li>
       <li>
           <a href="<?= $ret_url . "/view=groups_media&gid=" . $links->parent_collection_id ?>"><?= sprintf(__("Return to %s gallery"), PA::$group_noun) ?></a>
       </li>
       <?php } else { ?>
         <?php 
         $author_name = chop_string($links->author->first_name,20) . ' ' . chop_string($links->author->last_name,20);
         
         if (!isset($_GET['media'])) { // for avoiding the Redirection when user comes from recent media page 
         ?>
       <li>
         <a href="<?= $ret_url . "/uid=" . $links->author->user_id ?>">
           <?= sprintf(__("Return to %s's gallery"), $author_name) ?>
         </a>
       </li>
       <? } ?>
      <li>
      <?php if ($links->author_id != PA::$login_uid) {
      	?>
        <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links->author_id ?>"><?= sprintf(__("%s's Page"), $author_name)?></a>
      	<?php
      } else {
      	?>
        <a href="<?= PA::$url . PA_ROUTE_USER_PRIVATE ?>"><?= __('My Page')?></a>
      	<?php
      } ?>
     </li>
     <? } ?>
     <li><a href="<?php echo $back;?>">Back</a></li>
     <?php if(PermissionsHandler::can_user(PA::$login_uid, $param_array) && ($links->author_id != PA::$login_uid)) {?>
       <li><a href="javascript: return void();" onclick="javascript: showhide_block('report_abuse_div');" > Report Abuse </a></li>
     <? } ?>
     <?php if(!empty($login_uid)) { ?>
     <li><a href="javascript:" onclick="showhide_block('display_comment');" >Comment</a></li>
     <? } ?>
    </ul>
  </div>
  <? } else { // when User is not autherzied ?>
    <div class="description">You are not authorized to view this Media </div>
  <? } ?>
<form name='abuse_form' action="" method='post' >
    <div id='report_abuse_div' style="display:none">
      <fieldset class="center_box">
        <legend>Report abuse</legend>
        <div class="field_bigger">
          <label ><span class="required"> * </span> Comment</label>
          <textarea rows="5" cols="67" name="abuse"><?php echo @$_POST['abuse'];
          ?></textarea>
        </div>
        <div class="button_position"><input type='submit' name='rptabuse' value='Submit Abuse' /></div>
      </fieldset>
    </div>
 </form>

<?php
  $param = array();
  $param['type'] = 'content';
  $param['div_id'] = "display_comment";
  $param['id'] = $links->content_id;
  echo uihelper_create_comment_form($param);
?>
<?php if(!empty($comments)) { ?>
<div>
<h2> Comments </h2>
<ul class="media_comments">
<?php
$cnt = count($comments);
for ($i = 0; $i < $cnt; $i++) {
  $comment_author = new User();
  $comment_author->load((int)$comments[$i]['user_id']);
  $login = User::get_login_name_from_id($comments[$i]['user_id']);
  $current_url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;

?>
<li>
 <p>
<a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login ?>"><?= uihelper_resize_mk_user_img($comment_author->picture, 32, 32, 'alt="User Picture"')?> </a>
<?php echo $comments[$i]['comment'];?> <br />
Posted by: <a href="<?php echo $current_url;?>"><?php echo ucfirst($comment_author->display_name);?></a>, on <?=date("l, F d Y", $comments[$i]['created']);?>
<?php
  $pram_array = array();
  $pram_array['permissions'] = 'delete_comment_authorization';
  $pram_array['content_owner'] = $links->author_id;
  $pram_array['comment_owner'] = $comments[$i]['user_id'];
?>
<?php if(PermissionsHandler::can_user(PA::$login_uid, $pram_array)) { ?> <a href="<?php echo PA::$url;?>/deletecomment.php?comment_id=<?php echo $comments[$i]['comment_id']?>">delete</a><? } ?>
 </p>
</li>
<hr />
<? } ?>
</ul>
</div>

<?php if ($page_links) { ?>
  <div class="prev_next">
    <?php if ($page_first) { echo $page_first; }?>
    <?php echo $page_links?>
    <?php if ($page_last) { echo $page_last;}?>
  </div>
<?php } ?>
<? } ?>
