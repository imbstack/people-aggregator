<?php
require_once dirname(__FILE__)."/../config.inc";
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "web/includes/functions/html_generate.php";
require_once "web/includes/functions/validations.php";
require_once "api/User/User.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>PA</title>
    <link rel="stylesheet" href="<?php echo $current_theme_path?>/style.css" />
    <link rel="dix:/homesite" href="<?php echo PA::$url ?>/homesite.php/sxip" class="dix:/core#1 dix://sxip.net/simple#1" />
</head>
<body topmargin="0" style="background:white">
<div class="auto middle-content-block-width">
<form name="formAlphaLogin" method="post">  
    <div class="fleft login-parent-width margin-top-33">
      <div class="fleft login-top login-parent-width"><img src="<?php echo $current_theme_path?>/images/spacer.gif" alt="" /></div>
      <div class="fleft login-middle login-parent-width">
        <div class="auto login-middle-parent-width login-middle-parent-padding"><img src="<?php echo $current_theme_path?>/images/login-logo.jpg" alt="PA" />
        </div>
        <div class="auto login-child-width bold text-align-centre font-size-18">Site Under Maintenance</div>
        <div class="auto login-child-width verdana people-child-text font-white" style="line-height:15px; padding-bottom:20px;" >The <?=PA::$site_name?> Network is temporarily closed. We apologize for any inconvenience and thank you for your patience as we improve the system. Please try after some time. </div>
        
        
      
      </div>
      <div class="fleft login-bottom login-parent-width"></div>
    </div>
</form>    

    
  </div>

</body>
</html>