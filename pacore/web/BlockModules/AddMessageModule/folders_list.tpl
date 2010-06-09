<?php
  // global var $_base_url has been removed - please, use PA::$url static variable
  
  $search_val = 'search..';//value of the search text box.
  if (!empty($search_string)) {
    $search_val = $search_string;
  }
?>
<div id="message_col_a">
  <ul id="folder_mail">    
    <li><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder=Inbox'?>">Inbox</a></li>
    <li><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder=Sent'?>">Sent</a></li>
    <li>My folders
      <ul>
        <?php                  
          if (count($folders)) {
            foreach ($folders as $folder) {
        ?>
        <li><a href="<?php echo PA::$url .PA_ROUTE_MYMESSAGE.'/folder='.$folder['name']?>"><?php echo $folder['name']?></a></li>
        <?php
            }
          } else {
        ?>
        <li><?= __("You have not created any folders.") ?></li>
        <?php
          }
        ?>
      </ul>
    </li>
  </ul>
  <form name="searchMessages" method="get" action="<?php echo PA::$url.'/'.FILE_MYMESSAGE?>">
    <input name="q" value="<?php echo $search_val?>" type="text" id="q" onfocus="javascript: search_action.onfocus('q');" />
    <span>
      <a href="#" onclick="javascript: document.forms['searchMessages'].submit();">Go</a>
    </span>
  </form>
</div>