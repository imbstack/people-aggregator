<?php global $login_uid;
 $rating = rating('content', $contents->content_id);
?>
<div class="single_post">
<div class="audio">
  <h1><?php echo $contents->title;?></h1>
   <table cellspacing="0">
   <tr valign="top">
   <td class="author" width="85">
    <?php global $current_theme_path;  ?>
    <?php echo '<a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id . '">'.uihelper_resize_mk_user_img($picture_name, 80, 80, 'alt=""').'</a>'; ?><br /><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC  . '/' . $user_id ?>"><?php echo wordwrap(chop_string($user_name,40),20)?></a>
     </td>
     <td class="message" width="100%">
    <p class="message_start">

      <?php echo $contents->body;?>
      <?php
        if( $media_gallery_content ) {
          if ( strstr($contents->file_name, "http://") ) {
            $audio_file = $audio_download_url = $contents->file_name;
            $download_text = __("Right-click to download");
          }
          else {
            $audio_file = Storage::getURL($contents->file_name);
            $audio_download_url = PA::$url .'/download.php?file='.urlencode($contents->file_name);
            $download_text = __("Download");
          }
      ?>

<script type="text/javascript" src="<?=CURRENT_THEME_REL_URL?>/javascript/ufo.js"></script>
<p id="audio_player"><a href="http://www.macromedia.com/go/getflashplayer">Get Flash</a> to see this player.</p>
<script type="text/javascript">
  var FO = { movie:"<?=CURRENT_THEME_REL_URL?>/flash/xspf_player.swf",width:"300",height:"20",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
             flashvars:"playlist_url=<?=htmlspecialchars(PA::$url . "/xspf.php?single=$audio_file")?>&autostart=true" };
  UFO.create(FO,"audio_player");
</script>

        <a href="<?= htmlspecialchars($audio_download_url) ?>"><?= $download_text ?></a>

      <?php
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
      <div class="rating"><?= __("Please") ?> <a href="<?php echo PA::$url?>/login.php?return=<?php echo PA::$url . PA_ROUTE_CONTENT . '/cid='.$contents->content_id?>"><?= __("log in") ?></a> <?= __("to rate this audio") ?>
      </div>
      <?php
        }
      ?>
   <?php
     require "common_permalink_content.tpl";
   ?>

</div>
</div>
