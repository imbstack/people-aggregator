<h1>Create New Network Member </h1>
  <form method="post" action="new_user_by_admin.php" enctype="multipart/form-data">
    <fieldset class="center_box">    
      <p><?= __("Provide a login name, email address and basic information for a new network member.") ?>
        <?= __("Upon hitting the create button, the new member will be emailed a greeting including an auto-generated password.") ?></p>
      <p><?= __("Fields marked with an asterisk (*) are mandatory.") ?></p>
      <div class="field">
        <h4><label><?= __("Login name") ?></label><span class="required"> * </span></h4>
        <input name="login_name" type="text" class="text longer" value="<?php
        echo htmlspecialchars(@$form_data['login_name']);?>" /> 
      </div>
      <div class="field">    
        <h4><label><?= __("Email address") ?></label><span class="required"> * </span></h4>
        <input name="email" type="text" class="text longer" value="<?php echo
        htmlspecialchars(@$form_data['email']);?>"/>
      </div>     
      <div class="field">
        <h4><label><?= __("First name") ?></label><span class="required"> * </span></h4>
        <input name="first_name" type="text" class="text longer" value="<?php
        echo htmlspecialchars(@$form_data['first_name']);?>"/>
      </div>
      <div class="field">  
        <h4><label><?= __("Last name") ?></label></h4>
        <input name="last_name" type="text" class="text longer" value="<?php
        echo htmlspecialchars(@$form_data['last_name']);?>"/>
      </div>  
      <div class="field">
        <h4><label><?= __("Photo/avatar") ?></label></h4>
        <input type="file" class="text longer" name="userfile"/>
      </div>
      <?php $checked_auto = $checked_manual = "";
      if (!empty($form_data['radiobutton']) && $form_data['radiobutton'] ==
      'manual_pass') {
        $checked_manual = "checked=\"checked\"";
      } else {
        $checked_auto = "checked=\"checked\"";
      }?>
      <div class="field">
        <div class="autogen">
          <input name="radiobutton" type="radio" value="auto_pass" <?php echo $checked_auto;?> />  
          <?= __("Auto-generate new password for network member") ?>
        </div>
      </div>
      <div class="field">
	<input style="float: left" name="radiobutton" type="radio" value="manual_pass" <?php echo $checked_manual;?> />
        <h4><label><?= __("Manual password") ?>:</label></h4><input name="password" type="password" class="text longer" />     
      </div>
      <div class="field_bigger" style="height:100px">
        <div class="field_text">
          <?= __("modify greeting if desired and click the create user button below tocomplete process") ?>
        </div>
        <?php if(empty($_POST)) {?>
        <textarea name="greeting_msg" cols="60" rows="4"><?= __("Hi,") ?>

<?= __("I've created my own place on PeopleAggregator and I'd like you to") ?>
<?= __("connect to me so we can stay in touch.") ?>

<?= __("You can view my blog, my reviews, my ratings, my photos, my lists -") ?>
<?= __("and all sorts of things that matter to me.") ?>

<?= __("You can create a personal page and blog of your own, which will be") ?>
<?= __("linked to mine.") ?>

<?= __("That way you'll always know when I post something new!") ?>


        </textarea>
        <?php } else {?>
        <textarea name="greeting_msg" cols="60" rows="4"><?php echo htmlspecialchars($_POST['greeting_msg'])?></textarea>
        <?php } ?>
      </div>
      <div class="button_position">
        <input name="CreateUser" type="submit" id="create_user" value="<?= __("Create User") ?>" />
      </div>   
    </fieldset>
    <?php echo $config_navigation_url; ?>
  </form>
  