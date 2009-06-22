<div class="events blog" id="<?php echo $outer_block_id; ?>">
 <?php if (!isset($_GET['gid'])) {?> <h1><?php echo $contents->title; ?></h1>
 <? } else { ?><h2><?php echo $contents->title; ?></h2><? } ?>

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
  <?php require "common_content_detail.tpl" ?> 
</div>