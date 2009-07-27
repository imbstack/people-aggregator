<?php 
require_once "api/Login/PA_Login.class.php";

  $showId = 'pa';

  $action = "dologin.php?action=login";  
  if (@$_REQUEST['return']) {
    $action.="&amp;return=".urlencode($_REQUEST['return']);
  }
  $req = '';
  if (@$_REQUEST['GInvID']) {
    $req = '&amp;GInvID='. $_REQUEST['GInvID'];
  } else if ( @$_REQUEST['InvID'] ) {
    $req = '&amp;InvID='. $_REQUEST['InvID'];
  } 
  if (@$_REQUEST['token']) {
    $action .= '&amp;token='. $_REQUEST['token'];
  }
  $action .=$req;
  $tabindex = 0;
?>
<?php 
    $message = NULL;
    if (@$_GET['error']==1) {
      $message =  __('Sorry - you are not logged in or you have been logged out due to inactivity.<br />Please, log in again.<br />');
    } else if( @$_GET['msg'] ) {
      $message  = strip_tags($_GET['msg']);
    }         
?>
<h1><?=__('Login')?></h1>
<div class="description" style="text-align: center">

  <?=sprintf(__("%s login."),  PA::$network_info->name)?> <br />
  <?= __('You can login directly if you have an account,<br />or you can <b><a href="/register.php">sign up right now</a></b> to create a new account.')?>

</div>
<?php
  if( $message ) {
?>
  <p class="required">                
    <?php echo $message; ?>                  
  </p>
    
<?php  
  }
?>     
<div id="box_login">

          <form name="login_form" action="<?=$action;?>" method="post" id="login_simple" class="loginform active">
            <input type="hidden" name="InvID" value="" />
            <input type="hidden" name="GInvID" value="" />
            <ul class="login_box_simple">
              <li>
                <ul class="row_simple">
                  <li class="label_simple"><label for="username"><?= __('User name: ') ?></label></li>
                  <li class="input_simple"><input tabindex="<? echo $tabindex + 10;?>" type="text" size="15" name="username" class="input_box" id="username" value="<?= empty($_SESSION['user']['name']) ? '' : htmlspecialchars($_SESSION['user']['name']) ?>" /></li>
                </ul>
              </li>  
              <li>
                <ul class="row_simple">
                  <li class="label_simple"><label for="password"><?= __('Password: ') ?></label></li>
                  <li class="input_simple"><input tabindex="<? echo $tabindex + 11;?>" type="password" size="15" name="password" class="input_box" id="password" /></li>
                </ul>
              </li>  
              <li>
                <ul class="box_simple">
                  <li><input tabindex="<? echo $tabindex + 12;?>" type="checkbox"  name="remember" value="1" <?= empty($_COOKIE[PA_Login::$cookie_name]) ? '' : 'checked="checked"' ?> /><?= __("Remember me on this computer") ?> </li>
                  <li><input tabindex="<? echo $tabindex + 13;?>" type="image" id="loginbutton" alt="log in" value="log in" src="<?=PA::$theme_url;?>/images/login-butt.gif" /><br />
                  <a tabindex="<? echo $tabindex + 14;?>" href="<?= PA::$url . PA_ROUTE_FORGET_PASSWORD ?>"><?= __("Forgot your password?") ?></a></li>
                </ul>
              </li>
            </ul>  
          </form>

      </div>

<script language="javascript">
var n = document.getElementById("username");
if (n.value) {
	n = document.getElementById("password");
}
n.focus();
</script>

<?php 



 ?>     