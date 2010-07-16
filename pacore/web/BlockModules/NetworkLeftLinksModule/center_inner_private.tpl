<link rel="stylesheet" href="/Themes/Default/jquery-ui.css" type="text/css" media="all" /> 
<link rel="stylesheet" href="/Themes/Default/ui.theme.css" type="text/css" media="all" /> 
<script src="/Themes/Default/javascript/jquery-ui.min.js" type="text/javascript"></script>
<script src="/Themes/Default/javascript/jquery.bgiframe-2.1.1.js" type="text/javascript"></script> 

<script type="text/javascript">
	$(document).ready(function() {
		$("#accordion").accordion({autoHeight: false,collapsible: true});
	});
	</script>


<div id="accordion">
	<h3><a href="#">User Settings</a></h3>
	<div>
        <?php if ($task_perms['user_defaults'] == TRUE) { ?>		
	<h5><?=__('User defaults') ?></h5>
    		<ul>
      			<li><a href="<?php echo PA::$url;?>/network_user_defaults.php"><?= __('Default settings') ?></a></li>
      			<li><a href="<?php echo PA::$url;?>/relationship_settings.php"><?= __('Relationship settings') ?></a></li>
    		</ul>
	<h5><?=__('User Accounts')?></h5>
		<ul>
			<li><a href="<?php echo PA::$url;?>/new_user_by_admin.php"><?= __('Create users') ?></a></li>
      			<li><a href="<?php echo PA::$url;?>/manage_user.php"><?= __('Manage users') ?></a></li>
		</ul>
	<?php        if (PA::$network_info->type == PRIVATE_NETWORK_TYPE) {  ?>
      		<h5><a href="<?php echo PA::$url;?>/moderate_users.php"><?= __('Moderate users') ?></h5>
     	<?php  }} ?>
 	<?php if ($task_perms['notifications'] == TRUE) { ?>
		  <h5><a href="<?php echo PA::$url;?><?php echo '/'.FILE_CONFIGURE_EMAIL;?>"><?= __('Notification Templates') ?></a></h5>
		  <h5><?= __('Notification Settings') ?></h5>
		     <ul>
		       <li><a href="<?php echo PA::$url;?>/network_bulletins.php"><?= __('Bulletins') ?></a></li>
		       <li><a href="<?php echo PA::$url;?>/email_notification.php"><?= __('Email notifications') ?></a></li>
		     </ul>
   	<?}?>
    	<?php if ($task_perms['manage_settings'] == TRUE) { ?>
   		<h5><a href="<?php echo PA::$url . PA_ROUTE_CONFIG_ROLES ;?>"><?= __('Roles and Tasks') ?></a></h5>
        <?}?>	
	<?php if ($task_perms['manage_settings'] == TRUE) { ?>
		  <h5><a href="<?php echo PA::$url .PA_ROUTE_RANKING_POINTS;?>"><?= __('Ranking Points') ?></a></h5>
  	<?}?>
	</div>
	<h3><a href="#">Content</a></h3>
	<div>
	 <?php if ($task_perms['manage_content'] == TRUE) { ?>
  			<h5><?= __('Content') ?></h5>
				<ul>
      				<li><a href="<?php echo PA::$url;?>/network_moderate_content.php"><?= __('Moderate') ?></a></li>
      				<li><a href="<?php echo PA::$url;?>/network_manage_content.php"><?= __('Manage') ?></a></li>
      				<li><a href="<?php echo PA::$url;?>/manage_comments.php"><?= __('Comments') ?></a></li>
				</ul>
  	<?}?>
	<?php if ($task_perms['manage_ads'] == TRUE) { ?>
		<h5><a href="<?php echo PA::$url.PA_ROUTE_MANAGE_AD_CENTER;?>"><?= __('Ad Center') ?></a></h5>
  		<h5><a href="<?php echo PA::$url;?>/manage_textpads.php"><?= __('Textpads') ?></a></h5>
  	<?}?>
	<?php if ($task_perms['manage_links'] == TRUE) { ?>
		  <h5><a href="<?php echo PA::$url;?>/network_links.php"><?= __('Manage links') ?></a></h5>
	<?}?>
   	<?php if ($task_perms['manage_events'] == TRUE) { ?>
      		<h5><a href="<?php echo PA::$url;?>/network_calendar.php"><?= __('Network Events') ?></a></h5>
      		<h5><a href="<?php echo PA::$url;?>/manage_questions.php"><?= __('Manage Questions') ?></a></h5>
   	<?}?>
     	<?php if ($task_perms['user_defaults'] == TRUE) { ?>
  		<h5><a href="<?php echo PA::$url;?>/manage_groups.php"><?= __('Manage Groups') ?></a></h5>
   	<?}?>
  	<?php if ($task_perms['manage_links'] == TRUE) { ?>
       		<h5><a href="<?php echo PA::$url;?>/manage_footer_links.php"><?= __('Manage Footer Links') ?></a></h5>
       		<h5><a href="<?php echo PA::$url;?>/manage_static_pages.php"><?= __('Manage Static Pages') ?></a></h5>
   	<?php } ?>
	</div>
	<h3><a href="#">Network Settings</a></h3>
	<div>
	<?php if ($task_perms['manage_settings'] == TRUE) { ?>
  		<h5><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_NETWORK;?>"><?= __('Basic Settings') ?></a></h5>
   		<h5><a href="<?php echo PA::$url;?>/manage_profanity.php"><?= __('Manage Profanity') ?></a></h5>
	   	<h5><a href="<?php echo PA::$url;?>/manage_domain_name.php"><?= __('Manage Signup Access') ?></a></h5>
		<h5><a href="<?php echo PA::$url;?>/manage_category.php"><?= __('Manage Category') ?></a></h5>
  		<h5><a href="<?php echo PA::$url . PA_ROUTE_CONFIGURE_SYSTEM;?>"><?= __('System Settings') ?></a></h5>
  	<?}?>
	<?php if ($task_perms['manage_themes'] == TRUE) { ?>
  		<h5><?= __('Themes') ?></h5>
		      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme";?>"><?= __('Theme selector') ?></a></li>
		      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/module" ?>"><?= __('Module selector') ?></a></li>
		      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/bg_image"?>"><?= __('Background image') ?></a></li>
		      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/desktop_image"?>"><?= __('Header image') ?></a></li>
		      <li><a href="<?php echo PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/style"?>"><?= __('Customize theme') ?></a></li>
  	<?}?>	
	</div>
 	<?php if ($task_perms['meta_networks'] == TRUE) { ?>
	<h3><a href="#">Meta Network</a></h3>
	<div>
  		<li><a href="<?php echo PA::$url;?>/network_feature.php"><?= __('Meta network') ?></a>
    			<ul>
      			<li><a href="<?php echo PA::$url;?>/network_feature.php"><?= __('Set featured network') ?></a></li>
      			<li><a href="<?php echo PA::$url;?>/manage_emblem.php"><?= __('Manage emblems') ?></a></li>
      			<li><a  id="show_hide_splash_page_options"><?= __('Manage splash') ?></a>
        		<ul id="splash_page_options">
          		<li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=configure"><?= __('Configure') ?></a></li>
          		<li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=info_boxes"><?= __('Info Boxes') ?></a></li>
        		<li><a href="<?php echo PA::$url;?>/configure_splash_page.php?section=showcase"><?= __('Showcase Modules') ?></a></li>
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
	</div>
</div>

