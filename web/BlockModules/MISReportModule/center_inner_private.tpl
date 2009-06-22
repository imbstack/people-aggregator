<div class="description"><?= __("In this page you can view stats about system usage") ?></div>
<fieldset class="center_box">
<table>
  <tr>
    <td colspan="55"><?= __("Registered Users") ?></td>
    <td colspan="55"><?php echo $links['registered_users']?></td>
  </tr>
  <tr>
    <td colspan="55"><?= __("Number of blog post") ?></td>
    <td colspan="55"><?php echo $blog_post;?></td>
  </tr>
  <tr>
    <td colspan="55"><?= __("Number of Images") ?></td>
    <td colspan="55"><?php echo $images;?></td>
  </tr>
  <tr>
    <tr><td colspan="55"><?= __("Profile Views") ?><td/><td><?= __("Maximum") ?></td><td><?= __("Minimum") ?></td><td><?= __("Average") ?></td></tr>
    <tr><td colspan="55"><td/><td><?php echo $profile_views['max'];?></td>
                 <td><?php echo $profile_views['min'];?></td>
                 <td><?php echo floor($profile_views['avg']);?></td>
    </tr>
  </tr>
  <tr>
    <tr><td colspan="55"><?= __("Profile Visited by User") ?><td/><td><?= __("Maximum") ?></td><td><?= __("Minimum") ?></td><td><?= __("Average") ?></td></tr>
    <tr><td colspan="55"><td/><td><?php echo $profile_visits_by_user['max'];?></td>
                 <td><?php echo $profile_visits_by_user['min'];?></td>
                 <td><?php echo floor($profile_visits_by_user['avg']);?></td>
    </tr>
  </tr>
  <tr>
    <tr><td colspan="55"><?= __("Relations Statistics") ?><td/><td><?= __("Maximum") ?></td><td><?= __("Minimum") ?></td><td><?= __("Average") ?></td></tr>
    <tr><td colspan="55"><td/><td><?php echo $relationship_stats['max'];?></td>
                 <td><?php echo $relationship_stats['min'];?></td>
                 <td><?php echo floor($relationship_stats['avg']);?></td>
    </tr>
  </tr>
  <tr>
    <table>
      <tr><td><?= __("Number of Users by Email Domain:") ?></td><td><?= __("Domain Name") ?></td><td><?= __("Number of Users") ?></td></tr>
      <?php foreach ($email_domain_array as $domain) { ?>
        <tr>
          <td></td>
          <td><?php echo $domain['caption']; ?></td>
          <td><?php echo $domain['count']; ?></td>        
        </tr>
      <?php } ?>
    </table>
  </tr>  
</table>
</fieldset>
<?php echo $config_navigation_url;?>