<?php
?>

<div class="module_tagcloud">
<ul>
  <li>
    <a href="<?=PA::$url . PA_ROUTE_MYMESSAGE?>/folder_name=Inbox"><?= _n(";%d new messages
1;One new message
0;No new messages", $links['unread_msg']) ?></a>
  </li>
  <li>
    <a href="<?=PA::$url . PA_ROUTE_MYMESSAGE?>/folder_name=Inbox"><?= _n(";%d messages in total
1;One message in total
0;No messages", $links['total']) ?></a>
  </li>
</ul>
</div>