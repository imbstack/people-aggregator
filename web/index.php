<?php
// web/index.php: Splash page

// First check if the server is running a too-old version of PHP, and
// complain bitterly if so.

if (preg_match("/^(\d+)/", phpversion(), $m)) {
    if (intval($m[0]) < 5) {
?>
<h1>PeopleAggregator requires PHP5</h1>

<p>Your web server is running PHP version <b><?php echo phpversion(); ?></b>.  Unfortunately, PeopleAggregator requires PHP5 or later.</p>

<p><a href="http://wiki.peopleaggregator.org/PeopleAggregator_requires_PHP5">Click here for some information on installing or enabling PHP5 on typical web servers</a> (on the PeopleAggregator Wiki).</p>

<?php
        exit;
    }
}

$login_required = FALSE;
$use_theme = 'Beta';
include_once("web/includes/page.php");
require_once "api/ModuleData/ModuleData.php";


$configure = unserialize(ModuleData::get('configure'));
$module_name = 'SplashPage';
$configurable_sections = array('showcased_networks', 'network_of_moment', 'video_tours', 'register_today', 'server_announcement');
foreach ($configurable_sections as $key => $section) {
  $$section = unserialize(ModuleData::get($section));
}

// Redirect straight to the homepage if logged in
if ( PA::logged_in() || (!isset($configure['show_splash_page'])) || $configure['show_splash_page'] == INACTIVE) {
  $location =  PA_ROUTE_HOME_PAGE;
  header("Location: $location");
  exit;
}

$parameter = js_includes("all");
$mothership_info = mothership_info();

?>

<html>

  <head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <meta name="generator" content="Adobe GoLive">
    <title>Welcome to PeopleAggregator</title>
    <link href="<?php echo PA::$theme_url;?>/style_index.css" rel="stylesheet" type="text/css" media="all">
    <?
    echo $parameter;
    ?>

  </head>
  <body>
    <div id="wrapper">
      <div class="header">
        <h1>
          A place to create and run your own social network
        </h1>
        <div class="login">
          Already a member?<br>
          <a href="<?php echo PA::$url?>/login.php">Login now</a> or <a href="<?php echo PA::$url?>/register.php">register</a>
        </div>
      </div>
      <div class="first_row">
        <div class="cel_one">
          <span class="spanone"><a href="<?= PA::$url;?>/register.php"><div class="number1">1</div><div class="text1 cursor-pointer">join</div></a></span><div class="info_text1">open an account on the PeopleAggregator Home network</div>
          <span class="spantwo"><a href="<?= PA::$url;?>/register.php"><div class="number2">2</div><div class="text2 cursor-pointer">create</div></a></span><div class="info_text2">create your own social network. Pick a name, upload a logo, set the rules and start the community blog</div>
          <span class="spanthree"><a href="<?= PA::$url;?>/register.php"><div class="number3">3</div><div class="text3 cursor-pointer">invite</div></a></span><div class="info_text3">Invite your friends to blog, create groups, upload media and meet each other. All in your own social network</div>
        </div>
        <div class="cel_two">
          <h2>Register today</h2>
          <?php
            if (!empty($register_today)) {
          ?>
          <div class="text">
            <?php echo $register_today['description']?><br /><br />
          </div>
          <div class="text" align="center">
            <a href="<?= PA::$url;?>/register.php">
              <?php echo uihelper_resize_mk_img($register_today['network_image'], 193, 67, 'images/default.png', 'alt="PeopleAggregator"') ?>
            </a>
          </div>
          <?php
            }
          ?>
        </div>
        <div class="cel_three">
          <h2>Video Tours</h2>
          <?php
            if (!empty($video_tours)) {
          ?>
          <div class="text">
            <?php echo $video_tours['description']?><br><br>
          </div>
          <div class="text" align="center">
            <a href="<?php echo $video_tours['video_url']?>">
              <?php echo uihelper_resize_mk_img($video_tours['network_image'], 193, 67, 'images/default.png', 'alt="PeopleAggregator"') ?>
            </a>
          </div>
          </div>
          <?php
            }
          ?>
      </div>
      <div class="second_row">
        <div class="cel_one">
          <h2>Network Showcase</h2>
          <div class="text">
            <?php
              if ($showcased_networks) {
            ?>
              Here are a random collection of PeopleAggregator networks. Each network has its own membership, community blog, media galleries, groups, message system, etc. Rollover to view details, click to jump there.
              <p><span class="showcase">
              <?php
                foreach ($showcased_networks as $network) {
              ?>
                <a href="<?php echo $network['network_url']?>" target="_blank">
                  <?php echo uihelper_resize_mk_img($network['network_image'], 145, 145, 'images/default.png', 'alt="PeopleAggregator"')?>
                </a>
              <?php
                }
              ?>
              </span></p>
            <?php
              } else {
                echo 'There are no showcased networks';
              }
            ?>
            <p>Visit our <a href="<?= PA::$url;?>/networks_home.php">network directory</a></p>
          </div>
        </div>
        <div class="cel_two">
                  <h2>Network of the Moment</h2>
          <div class="text">
          <?php
            if (!empty($network_of_moment)) {
          ?>
            <a href="<?php echo $network_of_moment['network_url']?>" target="_blank"><?php echo $network_of_moment['network_caption']?></a> - <?php echo $network_of_moment['description']?></div>
          <div class="text" align="center">
            <p><a href="<?php echo $network_of_moment['network_url'];?>" target="_blank">
              <?php echo uihelper_resize_mk_img($network_of_moment['network_image'], 193, 67, 'images/default.png', 'alt="PeopleAggregator"') ?>
            </a></p>
          </div>
          <?php
            }
          ?>
        </div>
        <div class="cel_three">
          <h2>On your server?</h2>



<?php
            if (!empty($server_announcement)) {
          ?>
          <div class="text">
            <?php echo $server_announcement['description']?><br><br>
          </div>
          <div class="text" align="center">
            <a href="http://wiki.peopleaggregator.org/Main_Page">
              <?php echo uihelper_resize_mk_img($server_announcement['network_image'], 185, 110, 'images/default.png', 'alt="PeopleAggregator"') ?>
            </a>
          </div>

          <?php
            }
          ?>

</div>






 </div>
      <div class="footer">
        <div class="footer_text">copyright 2006 Broadband Mechanics <a href="http://www.broadbandmechanics.com/" target="_blank">About Us</a> | <a href="<?= PA::$url?>/features.php" target="_blank">Features</a>| <a href="<?= PA::$url?>/faq.php" target="_blank">FAQ</a> | <a href="<?php echo PA::$url .'/roadmap.php';?>" target="_blank">Roadmap</a> | <a href="http://wiki.peopleaggregator.org/Main_Page" target="_blank">Developer Wiki</a></div>
      </div>
    </div>
  </body>

</html>
