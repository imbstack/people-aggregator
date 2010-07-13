<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <link rel="stylesheet" type="text/css" href="/install/install.css" media="screen" />
  <script type="text/javascript" src="<?php echo PA::$theme_url;?>/javascript/jquery.js"></script>
  <script type="text/javascript" src="<?php echo PA::$theme_url;?>/javascript/password_strength/password_strength_plugin.js"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo PA::$theme_url;?>/javascript/password_strength/style.css"/>
  <script type="text/javascript"> 
	$(document).ready( function() {
		$(".admin_password").passStrength({
			userid:	"#admin_username",
			messageloc: 1	
		});
		$("#create_db").click(function(){
			$("#mysql_root_username").attr("disabled", false);
			$("#mysql_root_password").attr("disabled", false);
		});
		$("#use_existing_db").click(function(){
			$("#mysql_root_username").attr("disabled", true);
			$("#mysql_root_password").attr("disabled", true);
		});
	});	
	
  </script>
</head>

<body>
    <div id="inst_main">
    <div id="inst_top"></div>
      <div id="inst_logo"><img src="/install/step<?= $step ?>.gif" alt="step" /></div>
      <div id="inst_header">
        <label class="title"><?=$title?></label>
      </div>


      <?php if(!empty($message)) : $msg = $message['msg']; $class = $message['class'] ?>
        <div id="inst_msg" class="<?= $class ?>">
          <?= $msg ?>
        </div>
      <?php endif; ?>
         <?php if(!empty($content)) : ?>
         <div id="inst_center">
            <div id="inst_form_data">
               <?= $content ?>
           </div>
         </div>
         <?php endif; ?>
          <div id="inst_navig">
              <input type="hidden" name="pa_inst[step]" id="pa_inst_step" value="<?= $step ?>" />
              <?= $navig ?>
              <div class="clear"></div>
          </div>
      <div id="inst_bottom"></div>
    </div>
</body>
</html>
