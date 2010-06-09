<?php // global var $_base_url has been removed - please, use PA::$url static variable
?>
<div class="description"><?= __("In this page you can view Email Addresses of all users") ?></div>
<fieldset class="center_box">
<a href="<?php echo PA::$url .'/misreports.php?mis_type=mkt_rpt&sort_by=all'?>"><?= __("All Users") ?></a>
<a href="<?php echo PA::$url .'/misreports.php?mis_type=mkt_rpt&sort_by=frequent'?>"><?= __("Most Active Users") ?></a>
<a href="<?php echo PA::$url .'/misreports.php?mis_type=mkt_rpt&sort_by=dormant'?>"><?= __("Dormant Users") ?></a>
<br /><br />
<?php if (@$_GET['sort_by'] == 'frequent' || @$_GET['sort_by'] == 'dormant' ) {
  $ranking = TRUE;
} else {
  $ranking = FALSE;
}?>
<?php if ($ranking && is_array($parameters)) {
    $ranking_html = '<div><ul><li><?= __("Ranking Criterion") ?></li>';
    foreach ($parameters as $param) {
      $post_name = "param_".$param["id"];
      $ranking_html .='<li>'.$param["description"].'       '.$param["point"].'</li>';
    }
    $ranking_html .= '</ul></div>';
    echo $ranking_html;
}?>
<?php if ($page_links) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links;?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>
<table>
  <tr><td><?= __("User") ?></td><td><?= __("Email Addresses") ?></td><?php  if ($ranking){ ?><td><?= __("Total Points") ?></td><?php } ?></tr><tr></tr>
  <?php 
  foreach ($emails as $email) {
    $login = $email['login_name'];
    $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/'. $login;
/*    
    $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$email['user_id'];
    $url_perms = array('current_url' => $current_url,
                              'login' => $login                  
                            );
    $url = get_url(FILE_USER_BLOG, $url_perms);
*/    
  ?>
  <tr><td><a href="<?php echo $url;?>"><?php echo $email['login_name'];?></td><td><a href="mailto:<?php echo $email['email']?>"><?php echo $email['email'];?></a></td><td><?php echo @$email['site_points'];?></td></tr>
  <?php } ?>
</table>
<?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links;?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>
</fieldset>
<?php echo $config_navigation_url;?>