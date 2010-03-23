<?php
?>
<div id="configure_network"><h4><?= __('Configure') ?> <?php echo chop_string(PA::$network_info->name, 12);?></h4></div>
<ul>
  <?php if ($task_perms['manage_settings'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_SYSTEM;?>"><?= __('System Settings') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_SYSTEM;?>"><?= __('Configuration manager') ?></a></li>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_DEFENDER;?>"><?= __('PA Defender') ?></a></li>
     </ul>
  </li>
  <?}?>

  <?php if ($task_perms['manage_settings'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_NETWORK;?>"><?= __('Network Settings') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_NETWORK;?>"><?= __('Network settings') ?></a></li>
     </ul>
  </li>
  <?}?>

  <?php if ($task_perms['manage_ads'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url.PA_ROUTE_MANAGE_AD_CENTER;?>"><?= __('Ad Center') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url.PA_ROUTE_MANAGE_AD_CENTER;?>"><?= __('Manage Ad Center') ?></a></li>
    </ul>
  </li>
  <li><a href="<?php echo PA::$url;?>/manage_textpads.php"><?= __('Textpads') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url;?>/manage_textpads.php"><?= __('Manage Textpads') ?></a></li>
    </ul>
  </li>
  <li><a href="<?php echo PA::$url;?><?php echo '/'.FILE_CONFIGURE_EMAIL;?>"><?= __('Configure Email') ?></a>
    <ul>
     <li><a href="<?php echo PA::$url;?><?php echo '/'.FILE_CONFIGURE_EMAIL;?>"><?= __('Configure Email') ?></a></li>
    </ul>
  </li>
  <?}?>
<?php if ($task_perms['meta_networks'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/network_feature.php"><?= __('Meta network') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url;?>/network_feature.php"><?= __('Set featured network') ?></a></li>
      <li><a href="<?php echo PA::$url;?>/manage_emblem.php"><?= __('Manage emblems') ?></a></li>
      <li><a  id="show_hide_splash_page_options"><?= __('Manage splash') ?></a>
        <ul id="splash_page_options">
          <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=configure"><?= __('Configure') ?></a></li>
          <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=info_boxes"><?= __('Info Boxes') ?></a></li>
          <!--<li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=network_of_moment"><?= __('Network of Moment') ?></a></li>-->
        <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=showcase"><?= __('Showcase Modules') ?></a></li>

        <!-- <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=video_tours"><?= __('Video Tours') ?></a></li>-->
         <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=server_announcement"><?= __('Server Announcement') ?></a></li>
   <?php if ($task_perms['user_defaults'] == TRUE) { ?>
   <li><a href="<?php echo PA::$url.PA_ROUTE_CONFIG_POLL;?>"><?= __('Survey') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url.PA_ROUTE_CONFIG_POLL;?>?type=create"><?= __('Create Survey') ?></a></li>
       <li><a href="<?php echo PA::$url.PA_ROUTE_CONFIG_POLL;?>?type=select"><?= __('Select Survey') ?></a></li>
     </ul>
   </li>
   <?php }?>

         
         <li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=register_today"><?= __('Register Today') ?></a></li>
        </ul>
      </li>
      <li><a href="<?php echo PA::$url;?>/manage_taketour.php"><?= __('Take a Tour Video') ?></a></li>
      <li><a><?= __('MIS Reports') ?></a>
        <ul id="splash_page_options">
          <li><a href="http://www.<?php echo PA::$domain_suffix;?>/awstats/awstats.pl?config=www.<?php echo PA::$domain_suffix;?>" target="_blank"><?= __('Usage') ?></a></li>
          <li><a href="<?php echo PA::$url;?>/misreports.php"><?= __('Counts') ?></a></li>
          <li><a href="<?php echo PA::$url;?>/misreports.php?mis_type=mkt_rpt"><?= __('Marketing reports') ?></a></li>
        </ul>
      </li>
    </ul>
  </li>
<?php } ?>
<?php if ($task_perms['manage_themes'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme";?>"><?= __('Themes') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme";?>"><?= __('Theme selector') ?></a></li>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/module" ?>"><?= __('Module selector') ?></a></li>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/bg_image"?>"><?= __('Background image') ?></a></li>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/desktop_image"?>"><?= __('Header image') ?></a></li>
      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/style"?>"><?= __('Customize theme') ?></a></li>
    </ul>
  </li>
  <?}?>
    <?php if ($task_perms['user_defaults'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/network_user_defaults.php"><?= __('User defaults') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url;?>/network_user_defaults.php"><?= __('Default settings') ?></a></li>
      <li><a href="<?php echo PA::$url;?>/relationship_settings.php"><?= __('Relationship settings') ?></a></li>
    </ul>
  </li>

  <li><a href="<?php echo PA::$url;?>/manage_user.php"><?= __('Users Settings') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url;?>/new_user_by_admin.php"><?= __('Create users') ?></a></li>
      <li><a href="<?php echo PA::$url;?>/manage_user.php"><?= __('Manage users') ?></a></li>
      <?php
        if (PA::$network_info->type == PRIVATE_NETWORK_TYPE) {
      ?>
      <li><a href="<?php echo PA::$url;?>/moderate_users.php"><?= __('Moderate users') ?></li>
      <?php
        }
      ?>
    </ul>
  </li>

  <?}?>
  <?php if ($task_perms['manage_content'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/network_manage_content.php"><?= __('Content') ?></a>
    <ul>
      <?php echo '';// if ($network_content_moderation) : // this condition removed on Vishal's request ?>
        <li><a href="<?php echo PA::$url;?>/network_moderate_content.php"><?= __('Moderate contents') ?></a></li>
      <?php echo ''; // endif; ?>
      <li><a href="<?php echo PA::$url;?>/network_manage_content.php"><?= __('Manage contents') ?></a></li>
      <li><a href="<?php echo PA::$url;?>/manage_comments.php"><?= __('Manage comments') ?></a></li>
    </ul>
  </li>
  <?}?>

   <?php if ($task_perms['manage_links'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/network_links.php"><?= __('Manage links') ?></a>
     <ul>
      <li><a href="<?php echo PA::$url;?>/network_links.php"><?= __('Network links') ?></a></li>
     </ul>
   </li>
   <li><a href="<?php echo PA::$url;?>/manage_profanity.php"><?= __('Manage Profanity') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url;?>/manage_profanity.php"><?= __('Profane Word List') ?></a></li>
     </ul>
   </li>
   <?}?>
   <?php if ($task_perms['manage_settings'] == TRUE) { ?>
   <li><a href="<?php echo PA::$url;?>/manage_domain_name.php"><?= __('Manage Signup Access') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url;?>/manage_domain_name.php"><?= __('Manage Domain Names') ?></a></li>
     </ul>
   </li>
   <?php }?>
   <?php if ($task_perms['manage_events'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/network_calendar.php"><?= __('Manage Events') ?></a>
     <ul>
      <li><a href="<?php echo PA::$url;?>/network_calendar.php"><?= __('Network Events') ?></a></li>
      <li><a href="<?php echo PA::$url;?>/manage_questions.php"><?= __('Manage Questions') ?></a></li>
     </ul>
   </li>
   <?}?>

     <?php if ($task_perms['user_defaults'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/manage_groups.php"><?= __('Manage Groups') ?></a>
     <ul>
      <li><a href="<?php echo PA::$url;?>/manage_groups.php"><?= __('Manage Groups') ?></a></li>
     </ul>
   </li>
   <?}?>
       <?php if ($task_perms['user_defaults'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url;?>/manage_category.php"><?= __('Manage Category') ?></a>
     <ul>
      <li><a href="<?php echo PA::$url;?>/manage_category.php"><?= __('Manage Category') ?></a></li>
     </ul>
   </li>
   <?}?>
    <?php if ($task_perms['notifications'] == TRUE) { ?>
   <li><a href="<?php echo PA::$url;?>/email_notification.php"><?= __('Notifications') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url;?>/network_bulletins.php"><?= __('Bulletins') ?></a></li>
       <li><a href="<?php echo PA::$url;?>/email_notification.php"><?= __('Email notifications') ?></a></li>
     </ul>
   </li>
   <?}?>
    <?php if ($task_perms['manage_settings'] == TRUE) { ?>
   <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIG_ROLES ;?>"><?= __('Roles') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url . PA_ROUTE_CONFIG_ROLES;?>"><?= __('Manage Roles & Tasks') ?></a></li>
<!--
       <li><a href="<?php echo PA::$url;?>/assign_tasks.php"><?= __('Manage Tasks Relationship') ?></a></li>
-->
     </ul>
   </li>
   <?}?>

<?php if ($task_perms['manage_settings'] == TRUE) { ?>
  <li><a href="<?php echo PA::$url .PA_ROUTE_RANKING_POINTS;?>"><?= __('Points') ?></a>
    <ul>
      <li><a href="<?php echo PA::$url .PA_ROUTE_RANKING_POINTS;?>"><?= __('Manage Ranking points') ?></a></li>
     </ul>
  </li>
  <?}?>


   <?php if ($task_perms['manage_links'] == TRUE) { ?>
   <li><a href="<?php echo PA::$url;?>/manage_footer_links.php"><?= __('Static Pages') ?></a>
     <ul>
       <li><a href="<?php echo PA::$url;?>/manage_footer_links.php"><?= __('Manage Footer Links') ?></a></li>
       <li><a href="<?php echo PA::$url;?>/manage_static_pages.php"><?= __('Manage Static Pages') ?></a></li>
     </ul>
   </li>

   <?php } ?>
</ul>
