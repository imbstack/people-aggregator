<?php  

require_once "web/includes/tinymce.php";
//install_tinymce('minimal');

?>
<h1>Upload <?php echo $media_type?></h1>
<div id="buttonbar">
  <ul>
    <li><a href="<?php echo $back;?>"><?= __("Back") ?></a></li>
  </ul>  
</div>
  <?php 
   if (isset($_GET['gid']) && (!empty($_GET['gid']))) {
   // --------------- Handling Groups Upload ----------------
    switch ($_GET['type']) {
       case 'Images':
         require "group_image_upload.tpl";
       break;
       case 'Audios':
         require "group_audio_upload.tpl";
       break;
       case 'Videos':
         require "upload_video.tpl";
       break;            
     }
   } else { 
   // ------------------ Handling User's Media Gallery --------------
     switch ($_GET['type']) {
       case 'Images':
         require "upload_image.tpl" ;
       break;
       case 'Audios':
         require "upload_audio.tpl" ;
       break;
       case 'Videos':
         require "upload_video.tpl" ;
       break;            
     }
   }
  ?>