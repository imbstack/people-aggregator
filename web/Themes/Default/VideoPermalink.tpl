<?php global $login_uid;
 $rating = rating('content', $contents->content_id);
?>
<div class="single_post">
<div class="video">
  <h1><?php echo $contents->title;?></h1>
  <table cellspacing="0">
   <tr valign="top">
   <td class="author" width="85">
    <?php global $current_theme_path;  ?>
    <?php echo '<a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id . '">'.uihelper_resize_mk_user_img($picture_name, 80, 80, 'alt=""').'</a>'; ?><br /><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id ?>"><?php echo wordwrap(chop_string($user_name,40),20);?></a>
     </td>
     <td class="message" width="100%">
    <p class="message_start">
      <?php echo $contents->body;?>
      <?php
        if (!empty($media_gallery_content)) {
          
          $video_url = Storage::getURL($contents->file_name);
                    
          $extension = explode( '.',$contents->file_name );
          $extension = strtolower(end( $extension ));
      
        if( $extension == 'mov') {
      ?>
          <embed src="<?=$video_url;?>" width="280" height="250" autoplay="true" controller="true" loop="false" pluginspage="http://www.apple.com/quicktime">
          </embed>
      <?php
        }
        else {
      ?>
      
      <div id = "embed_video">
          <OBJECT  ID="WinMedia" classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" CODEBASE="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"  width=160 height=120 standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">
                <PARAM NAME="FileName"  VALUE="<?=$video_url;?>" >
                <PARAM NAME="AutoStart" Value="false">
                <PARAM NAME="ShowControls" Value="true">
                <param name="wmode" value="transparent">
                <embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/Windows/MediaPlayer/" src="<?=$video_url;?>" Name=MediaPlayer  AutoStart=0  Width=160 Height=120 autostart=1  ShowControls=1>
                </embed>
           </OBJECT>
           </div>
           <script>
        if (document.getElementById('display_message')) {
          document.getElementById('embed_video').style.display='none';
        }
      </script>
      <?php
          }      
        }
      ?>
    </p>
    <div class="rating">
        <b><?= __("Overall Rating") ?>:</b>
        <span id="overall_rating_<?php echo $contents->content_id?>"><?php echo $rating['overall']?></span>
      </div>
      <?php
        if (!empty($login_uid)) {
      ?>

      <div class="rating">
        <b><?= __("Your Rating") ?>:</b>
        <span><?php echo $rating['new']?></span>
      </div>
    
      <?php
        } else {
      ?>
      <div class="rating"><?= __("Please") ?> <a href="<?php echo PA::$url?>/login.php?return=<?php echo PA::$url . PA_ROUTE_CONTENT . '/cid='.$contents->content_id?>"><?= __("log in") ?></a> <?= __("to rate this video") ?>
      </div>
      <?php
        }
      ?>
   <?php
     require "common_permalink_content.tpl";
   ?>
   
</div>
</div>