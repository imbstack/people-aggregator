<div class="single_post">
<div class="events blog">
  <h1><?php echo $contents->title;?></h1>
  <table cellspacing="0">
   <tr valign="top">
   <td class="author" width="85">
    <?php global $current_theme_path;  ?>
    <?php echo '<a href="' . PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id.'">'.uihelper_resize_mk_user_img($picture_name, 80, 80, 'alt=""').'</a>'; ?><br /><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id ?>"><?php echo wordwrap(chop_string($user_name,40),20)?></a>
     </td>
     <td class="message" width="100%">

<?php if (!empty($contents->event_data['banner'])) { ?>
	<div class="event" style="width:430px;">
		<?php echo uihelper_resize_mk_img($contents->event_data['banner'], 430, 80, 
	NULL, 'style="margin:0;float:none;" alt="Event Banner"', RESIZE_FIT); ?>
	</div>
<? } ?>
	
	<div class="event">
		<b><?=__("Location")?></b>:
		<span class="location">
		<p><?=_out($contents->event_data['venue'])?></p>
		</span>
	</div>
	<div class="event">
		<p><b><?=__("Begins")?></b>:
		<abbr title="<?=$contents->start_time?>" class="dtstart">
		<?php echo PA::datetime($contents->start_time) ?>
		</abbr></p>
		<p><b><?=__("Ends")?></b>: 
		<abbr title="<?=$contents->end_time?>" class="dtend">
		<?php echo PA::datetime($contents->end_time) ?>
		</abbr></p>
	</div>
  <div class="desc">
  <?=_out($contents->body); ?>
  </div>
   <?php
     require "common_permalink_content.tpl";
   ?>
  
</div>
</div>