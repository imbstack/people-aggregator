<?php
  // global var $_base_url has been removed - please, use PA::$url static variable
  
  $search_val = __("Search ...");//value of the search text box.
  if (!empty($search_string)) {
    $search_val = $search_string;
  }
?>
<div id="message_col_a">
  <ul id="folder_mail">
    <?php
      if ($folder_name == INBOX) {
    ?>
    <li class="active"><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE ?>"><?= __("Inbox") ?></a></li>
    <?php
      } else {
    ?>
    <li><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE ?>"><?= __("Inbox") ?></a></li>
    <?php
      }
    ?>
    <?php
      if ($folder_name == SENT) {
    ?>
    <li class="active"><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder=Sent'?>"><?= __("Sent") ?></a></li>
    <?php
      } else {
    ?>
    <li><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder=Sent'?>"><?= __("Sent") ?></a></li>
    <?php
      }

          // link to the conversation view
          $class = null;
          if (@$_REQUEST['action'] == "conversation_view") {
          	$class = ' class="active"';
          }
        ?>
        <li<?php echo $class?>><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/action=conversation_view'?>"><?php echo __('Conversations View')?></a></li>
    
    <li>My folders
      <ul>
        <?php                  
          if (count($folders)) {
            foreach ($folders as $folder) {
              $class = null;
              if ($folder['name'] == $folder_name) {
                $class = ' class="active"';
              }
        ?>
        <li<?php echo $class?>><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder='.$folder['name']?>"><?php echo $folder['name']?></a></li>
        <?php
            }
          } else {
        ?>
        <li><?= __("You have not created any folders.") ?></li>
        <?php
          } ?>
      </ul>
    </li>
  </ul>
  <form name="searchMessages" method="get" action="<?php echo PA::$url.PA_ROUTE_MYMESSAGE?>" >
    <input name="q" value="<?php echo $search_val?>" type="text" id="q" onfocus="javascript: search_action.onfocus('q');">
    <span>
      <a href="#" onclick="javascript: document.forms['searchMessages'].submit();"><?= __("Go") ?></a>
    </span>
  </form>
</div>