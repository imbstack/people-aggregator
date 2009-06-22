<?php 
global $_PA, $network_info;

$can_manage_network = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => 'manage_settings'));;

?>
        <div class="id_list" id="id_list">
<!--          This div is added for open and close script -->
         <div id="open_close" class="display_false">
         <?php $target = (!empty($_REQUEST['gid'])) ? '?ccid='.$_REQUEST['gid'] : null; ?>
         <div class="edit_logoff" id="shortcut_post">
            <a href="<?= PA::$url;?>/post_content.php<?=$target?>"><?= __("Create Post") ?></a>
          </div>
          <div class="edit_logoff" id="shortcut_messages">
            <a href="<?= PA::$url .PA_ROUTE_MYMESSAGE;?>"><?= __("Messages ") ?><?= "($message_count)";?></a>
          </div>
          <div class="edit_logoff" id="shortcut_edit">
            <a href="<?= PA::$url.PA_ROUTE_EDIT_PROFILE?>"><?= __("Edit my account") ?></a>
          </div>
         <? if($can_manage_network) { ?>
           <div class="edit_logoff" id="shortcut_configure">
            <a href="<?= PA::$url . PA_ROUTE_CONFIGURE_NETWORK;?>"><?= __("Configure") ?></a>
          </div>
         <? } ?>
           </div>
        <b></b><div class="col_end"></div>
      </div>
