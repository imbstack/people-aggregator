<div id="forgot_pass">
  <form name="forgot_password" action="" method="post" onsubmit="return validate_form();">
  <fieldset class="center_box">
  <h3><?= __("Forgotten your PeopleAggregator account information?") ?></h3>
  <p><?= __("Just enter the e-mail address or login name you signed up with, and we'll mail you a link to change your password.") ?></p>

    <div class="field">
      <h5><label for="select file"><span class="required"> * </span> <?= __("E-mail address:") ?></label></h5>
      <input class="text longer" type="text" name="email" <?if (!empty($email)) {?> value="<?php echo htmlspecialchars($email);?>"<?php }?> />
    </div>
   <p> Or enter login name</p> 
    <div class="field">
      <h5><label for="select file"><span class="required"> * </span> <?= __("Login Name") ?></label></h5>
      <input class="text longer" type="text" name="login_name" value="" />
    </div>
        
     <div class="button_position"><input type="submit" class="button-submit" name="submit" value="<?= __("Get new password") ?>" /></div>
     <input type="hidden" name="action" id="action" value="forgotPasswordSubmit" />
    </fieldset>
  </form>
</div>