<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
// web/index.php: Splash page
// First check if the server is running a too-old version of PHP, and
// complain bitterly if so.
if(preg_match("/^(\d+)/", phpversion(), $m)) {
    if(intval($m[0]) < 5) {
        ?>
<h1>PeopleAggregator requires PHP5</h1>

<p>Your web server is running PHP version <b><?php echo phpversion();?></b>.  Unfortunately, PeopleAggregator requires PHP5 or later.</p>

<p><a href="http://wiki.peopleaggregator.org/PeopleAggregator_requires_PHP5">Click here for some information on installing or enabling PHP5 on typical web servers</a> (on the PeopleAggregator Wiki).</p>

<?php
        exit;
    }
}
$login_required = FALSE;
$use_theme = 'Beta';
include_once("web/includes/page.php");
require_once "api/ModuleData/ModuleData.php";
require_once("web/dologin.php");
$configure = unserialize(ModuleData::get('configure'));
if((!isset($configure['show_splash_page'])) || $configure['show_splash_page'] == INACTIVE) {
    $location = PA_ROUTE_HOME_PAGE;
    header("Location: $location");
    exit;
}
$module_name = 'SplashPage';
$configurable_sections = array(
    'info_boxes',
    'network_of_moment',
    'video_tours',
    'register_today',
    'server_announcement',
    'survey',
);
foreach($configurable_sections as $key => $section) {
    $$section = unserialize(ModuleData::get($section));
}
// Display welcome Message if logged in, otherwise show login prompt
if(PA::logged_in() || (!isset($configure['show_splash_page'])) || $configure['show_splash_page'] == INACTIVE) {
    $uname = $user->get_name();
    $message = "Welcome, $uname! <a href='logout.php'>Logout</a>";
}
else {
    $message = ' <form action="dologin.php?action=login" method="post" style="margin: 0px;">
         <input type="hidden" name="InvID" value=""/>
         <input type="hidden" name="GInvID" value=""/>
         Username<input type="text" name="username"/>
         Password<input type="password" name="password"/>
         <input type="submit" value="login"/>
         or
         <a href="register.php">REGISTER</a></form>';
}
$parameter = js_includes("all");
$mothership_info = mothership_info();
?>

<html>

  <head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>Welcome to PeopleAggregator</title>
<script type="text/javascript" src="<?php echo PA::$theme_url;?>javascript/jquery.js"></script>
    <link href="<?php echo PA::$theme_url;?>/style_index.css" rel="stylesheet" type="text/css" media="all">
    <?
    echo $parameter;
?>
  </head>
  <body>
<div id="everythingIsInHere">
    <div id="header">
      <div id="titlebox">
       <img border="none" src="/Themes/Default/images/title.png">
          </div>
      <div id="login" class="centerbox">

          <?php
echo $message;
?>
        </form>   
          </div>
        </div>
      <div id="mainbody">
      <div id="announcement">
          <span id="atext">Announcement:</span> <br>
          <!--THIS CANT BE TURNED OFF FROM THE INTERFACE YET, IMPLEMENT LATER!!!
          <?php echo uihelper_resize_mk_img($server_announcement['network_image'], 185, 100, 'images/default.png', 'alt="PeopleAggregator"')?>  -->
          <h3 class="<?php echo $server_announcement['importance']?>"><?php echo $server_announcement['description']?></h3><hr></div>
        <div id="infocontainer">
<div style="padding: 20px;">
      <div id="news" class="infobox">
      <span class="caption"><?php echo $info_boxes[0]['caption']?> </span>
      <a href=<?php echo $info_boxes[0]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[0]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>
          </div>
      <div id="safety" class="infobox">
<span class="caption"> <?php echo $info_boxes[2]['caption']?></span>

         <a href=<?php echo $info_boxes[2]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[2]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>

          </div>
      <div id="health" class="infobox">
<span class="caption"> <?php echo $info_boxes[1]['caption']?>  </span>

         <a href=<?php echo $info_boxes[1]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[1]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>

</div>
</div><div style="padding: 20px;">
      <div id="energy" class="infobox">
<span class="caption"> <?php echo $info_boxes[3]['caption']?>  </span>

         <a href=<?php echo $info_boxes[3]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[3]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>

      </div>
      <div id="me" class="infobox">
<span class="caption"> <?php echo $info_boxes[5]['caption']?>  </span>

         <a href=<?php echo $info_boxes[5]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[5]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>

      </div>
      <div id="education" class="infobox">
<span class="caption"> <?php echo $info_boxes[4]['caption']?>  </span>

         <a href=<?php echo $info_boxes[4]['network_url']?>><?php echo uihelper_resize_mk_img($info_boxes[4]['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?></a>
      </div>
</div>
    </div>

<div id="survey" class="wide_content">
<?php
require_once("BlockModules/PollModule/PollModule.php");
$p = new PollModule();
echo $p->render();
?>
</div>
 </div>
      <div class="footer">
        <div class="footer_text">copyright 2006 Broadband Mechanics <a href="http://www.broadbandmechanics.com/" target="_blank">About Us</a> | <a href="<?=PA::$url?>/features.php" target="_blank">Features</a>| <a href="<?=PA::$url?>/faq.php" target="_blank">FAQ</a> | <a href="<?php echo PA::$url.'/roadmap.php';?>" target="_blank">Roadmap</a> | <a href="http://wiki.peopleaggregator.org/Main_Page" target="_blank">Developer Wiki</a></div>
      </div>
    </div>
  </body>

</html>
