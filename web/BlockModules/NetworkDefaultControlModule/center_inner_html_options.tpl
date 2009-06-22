<?php // global var $_base_url has been removed - please, use PA::$url static variable
?>
<h1><?= __("Configure Network") ?></h1>
<h3><?= __("Network links") ?>:</h3>
<ul>
  <li><a href="<?php echo PA::$url .PA_ROUTE_CONFIGURE_NETWORK;?>"><?= __("Configure Basic Information") ?></a></li>
  <li><a href="<?php echo PA::$url .'/email_notification.php';?>"><?= __("Configure Email Notifications") ?> </a></li>
  <li><a href="<?php echo PA::$url .'/network_user_defaults.php';?>"><?= __("Configure User defaults") ?></a></li>
  <li><a href="<?php echo PA::$url .'/network_announcement.php';?>"><?= __("Send Announcements") ?></a></li>
  <li><a href="<?php echo PA::$url .'/relationship_settings.php';?>"><?= __("Configure Relationships") ?> </a></li>
  <li><a href="<?php echo PA::$url .'/manage_user.php';?>"><?= __("Manage users") ?></a> </li>
  <li><a href="<?php echo PA::$url .'/network_manage_content.php';?>"><?= __("Manage contents") ?> </a></li>
</ul>
<p>&nbsp;</p>

