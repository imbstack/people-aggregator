<?php include 'folders_list.tpl' ?>
<div id="message_col_b">
  <div id="buttonbar">
    <form name="messageList" method="post" action="<?php echo PA::$url.PA_ROUTE_MYMESSAGE ?>" >
    <input type="hidden" name="form_handler" value="MessageModule">
      <ul>
        <?php
          if (!empty($search_string)) {
        ?>
        <li><a href="<?php echo PA::$url.PA_ROUTE_MYMESSAGE?>/q=<?php echo $search_string?>"><?= __("Back to search results") ?></a></li>
        <?php
          }
        ?>
        <li><a href="<?php echo PA::$url .PA_ROUTE_ADDMESSAGE;?>"><?= __("Compose message") ?></a></li>
      </ul>
    </form>
  </div>
<style>
ul.message,
ul.message li {
	list-style: none;
}
ul.message {
	clear: both;
	margin-top: 10px;
	margin-bottom: 30px;
}
.message .message_info {
	margin-right: 20px;
}
ul.reply {
	margin-left: 40px;
	border-left: 5px solid red;
}
</style>
<?php
	foreach ($conversations as $mid => $message_details) {
		$extclass = '';
		if ($message_details->in_reply_to > 0) $extclass = ' reply';
	?>
    <ul class="message <?=$extclass?>">
      <li class="message_info">
	    <h4>
	      <a href="<?= PA::$url . PA_ROUTE_MYMESSAGE . "/action=view_message&mid=" . 
	      $message_details->message_id?>">
	      <?php echo chop_string($message_details->subject, 80);?></a>
	    </h4>
      	<p><strong>
      	<?php
      	// display the sender's name if it is NOT us
      	// otherwise display the To:
      	if ($message_details->sender_id == PA::$login_uid) { ?>
      	To: <?=uihelper_lookupnames($message_details->all_recipients)?>
      	<!-- (From:       	<?=$message_details->sender_name ?>) -->
      	<? } else { ?>
      	From: <a href="<? echo PA::$url . PA_ROUTE_USER_PUBLIC . "/" . $message_details->sender_id ?>">
      	<?=$message_details->sender_name ?>
      	</a>
      	<? } ?>
      	</strong></p>

      	<p><?php echo PA::date($message_details->sent_time, 'short') // date("d M Y", $message_details->sent_time) ?>,
           <?php echo PA::time($message_details->sent_time, 'short') // date("h:i", $message_details->sent_time) ?></p>
      </li>
      <li class="message_text">

      <p><?php echo nl2br($message_details->body) ?></p>
      </li>
    </ul>
    <form name="message_form" action="" method="post">
      <input type="hidden" name="form_handler" value="MessageModule">
      <input type="hidden" name="mid" value="<?php echo $mid?>">
      <input type="hidden" name="do_action" value="">
    </form>
<?php } ?>

</div>
