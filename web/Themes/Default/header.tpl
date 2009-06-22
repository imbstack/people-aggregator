<?php
  global $_PA, $current_theme_path, $uploaddir, $current_theme_rel_path, $app;
  $level_2 = $navigation_links['level_2'];
  if (!empty($_PA->simple['use_simplenav'])) {
    $level_3 = array();
	  $left_user_public_links = array();
  } else {
    $level_3 = $navigation_links['level_3'];
	  $left_user_public_links = $navigation_links['left_user_public_links'];
  }
  $mothership_info = mothership_info();

 ?>
<?php
   $img_desktop_info = get_network_image();
   $style = null;
   $extra = unserialize($network_info->extra);
   if (!empty($img_desktop_info) && @$extra['basic']['header_image']['display'] == DESKTOP_IMAGE_DISPLAY) {
       $img_desktop_info = manage_user_desktop_image($extra['basic']['header_image']['name'], $extra['basic']['header_image']['option']);
       $style = ' style="background: url('.$img_desktop_info['url'].') '.$img_desktop_info['repeat'].'"';
   }
?>
<div id="header"<?php echo $style?>>
  <?php if(@PA::$extra['language_bar_enabled']) : ?>
    <div class="language_bar">
      <?php foreach(array_keys($app->installed_languages) as $lang) {
        $src_url = add_querystring_var($app->request_uri, "lang", $lang);
        echo "<a href=\"$src_url\"><img src= \"$current_theme_path/images/flags/$lang.png\" /></a> ";
      } ?>
    </div>
  <?php endif; ?>
    <div class="title_box">
      <h1><?php echo wordwrap(strip_tags($network_info->name),60,'<BR />',1); // Don't add anchor tag here?></h1>
      <?php
        if ($network_info->tagline) {
      ?>
        <h2><?php echo wordwrap(strip_tags($network_info->tagline),60,'<BR />',1);?></h2>
      <?php } ?>
    </div>

     <?  if(!isset($_SESSION['user'])) { ?>
      <div class="login_box">
        <b><?= __("Already a member?") ?></b><br />
        <a href="<?php echo PA::$url;?>/login.php"><b><?= __("Login now") ?></b></a> <?= __("or") ?> <a href="<?php echo PA::$url;?>/register.php"><b><?= __("Register") ?></b></a>
      </div>
      <? } else {
             $login_user = new User();
             $login_user->load((int)$_SESSION['user']['id']);
             $user_name = $login_user->first_name." ".$login_user->last_name;
             $style = ' style="width:185px"';
      ?>
      <div class="login_box" onmouseover="javascript:show_hide_shortcuts.onmouseover('open_close');" onmouseout="javascript:show_hide_shortcuts.onmouseout('open_close');"<?php echo $style?>>
        <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login_user->user_id ?>">
          <?php echo uihelper_resize_mk_user_img($login_user->picture, 35, 35, 'alt="User Picture"'); ?>
        </a>
        <span>
          <b><?php echo $login_user->login_name; ?></b>
          <br />
          <a href="#" onclick="javascript:show_hide_network_categories('open_close','arrow_close'); return false;"><?= __("shortcuts") ?></a> | <a href="<?php echo PA::$url;?>/logout.php"><?= __("logoff") ?></a>
       </span>
       <?php include("web/includes/shortcuts_menu.php"); ?>
     </div>
       <div class="page_button">
   <?php $current_skin = get_skin_details();
              //to get the path of current skin image path
              $current_skin_path = $current_skin['path'];
              $level_1 = $navigation_links['level_1'];
              unset($level_1['highlight']); 
   if(is_array($level_1) && !array_key_exists("join_network",$level_1)) { ?>
       <a href="<?php echo PA::$url . PA_ROUTE_INVITE;?>"><img src="<?php echo $current_theme_path?>/images/invite.gif" alt="" height="32" width="185" border="0" /></a>
   <?php } else { ?>
       <a href="<?php echo $level_1["join_network"]["url"];?>"><img src="<?php echo $current_theme_path ?>/images/networkjoin.gif" alt="" height="32" width="185" border="0"></a>
   <? }  ?>
      </div>
      <? } ?>
     <div id="header_navbar_back"></div>
    <?php
      if( !empty($left_user_public_links) ) {
    ?>
    <div id="navbar_left">
      <ul>
        <?php
          $cnt = count($left_user_public_links);
          $i=0;
          $links_string = NULL;
          foreach ($left_user_public_links as $key=>$value) {
            $i++;
            $link_string = '<a href="'.$value['url'].'"'. $value['extra'] . '>'.$value['caption'].'</a> ';
            $link_string = ( $cnt == $i ) ? $link_string : $link_string.' | ';
        ?>
          <li><?php echo $link_string; ?></li>
        <?php
          }
        ?>
      </ul>
    </div>
    <?php
      }
    ?>

    <?php if($level_2) {?>
      <ul id="first_tier_navbar">
        <?php
          $highlight = @$level_2['highlight'];
          unset($level_2['highlight']);
          $cnt = count($level_2);
          $i=0;
          $links_string = NULL;
          foreach ($level_2 as $key=>$value) {
            $id = '';
            $id2 = '';
            $i++;
            if ( $key == $highlight ) {
              $id = ' id="current"';
              $id2 = ' id="active"';
            }
            $link_string = '<a href="'.$value['url'].'"'.$id.'>'.$value['caption'].'</a>';
            $link_string = ( $cnt == $i ) ? $link_string : $link_string.' | ';
        ?>
          <li<?php echo $id2;?>><?php echo $link_string; ?></li>
        <?php
          }
        ?>
        </ul>
      <?php } ?>
</div>

<div id="navbar_header">
  <ul id="second_tier_navbar">
<?php
  $highlight = @$level_3['highlight'];
  unset($level_3['highlight']);
  if( count( $level_3 ) ) {
?>

  <?php
    $links_string = NULL;
    $cnt = count($level_3);
    $i = 0;
    foreach ($level_3 as $key=>$value) {
      $id = '';
      $i++;
      if ( $key == $highlight ) {
        $id = ' class="active"';
      }
      $extra_info = (!empty($value['extra'])) ? $value['extra'] : null;
      $link_string = '<a href="'.$value['url'].'" '. $extra_info . '>'.$value['caption'].'</a>';
      $link_string = ( $cnt == $i ) ? $link_string : $link_string.' | ';
  ?>
    <li<?php echo $id;?>><?php echo $link_string; ?></li>
  <?php
    }
  ?>

<?php
  } else { // For validate the page
    echo '<li></li>';
  }
?>
  </ul>
</div>