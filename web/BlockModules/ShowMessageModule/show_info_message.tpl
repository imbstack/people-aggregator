<!--
<div id="display_message">
-->
<div class="msg_dlg_body">
<h1><?= $window_title ?></h1>
 <div class="msg_body">
  <ul>
    <?php if (is_array($error_msg)) : ?>
      <?php foreach ($error_msg as $msg) : ?>
         <li><?= $msg ?></li>
      <?php endforeach; ?>
    <?php else : ?>
       <li><?= $error_msg ?> </li>
    <?php endif; ?>
  </ul>
 </div>
 <?php if($redirect_delay > 0) : ?>
   <div class="msg_foot">
     <?= __('You will be redirected in ') ?><input type="text" id="txt_cnt" value="" style="width: 28px"/>
     <?= __(' seconds, or you can click here to redirect now') ?>
     <input type="button" id="confirm_btn" value="Ok" onclick="javascript: redirect_to_url(); return false;" />
   </div>
 <?php else : ?>
   <div class="msg_foot">
     <input type="button" id="confirm_btn" value="Ok" onclick="javascript: redirect_to_url(); return false;" />
   </div>
 <?php endif; ?>
</div>
