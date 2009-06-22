<h1><?= __("Basic info") ?></h1>
<form enctype="multipart/form-data" action="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=basic" method="post" name="formBasicProfile">
  <fieldset>
    <div class="field">
      <h4><?= __("Login Name") ?></h4>
      <div class="center"><?php echo $user_info->login_name;?></div>
    </div>

    <div class="field">
      <h4><span class="required"> * </span><label for="password"> <?= __("Password") ?></label></h4>
      <input type="password" id="password" name="pass" class="text short" value="" />
    </div>

    <div class="field">
      <h4><span class="required"> * </span><label for="confirm-password"> <?= __("Confirm Password") ?></label></h4>
      <input class="text short" id="confirm-password" type="password" name="conpass" value=""/>
    </div>

    <div class="field">
      <h4><span class="required"> * </span><label for="first-name"> <?= __("First Name") ?></label></h4>
      <?php
        if (!empty($request_data['first_name'])) {
      ?>
      <input type="text" name="first_name" value="<?php echo $request_data['first_name']?>" class="text short" id="first-name" maxlength="45" />
      <?php
        } else {
      ?>
      <input type="text" name="first_name" value="<?php echo $user_info->first_name?>" class="text short" id="first-name" maxlength="45" />
      <?php
        }
      ?>
    </div>
    
    <div class="field">
      <h4><label for="last-name"> <?= __("Last Name") ?></label></h4>
      <?php
        if (!empty($request_data['last_name'])) {
      ?>
      <input type="text" name="last_name" value="<?php echo $request_data['last_name']?>" class="text short" id="last-name" maxlength="45" />
      <?php
        } else {
      ?>
      <input type="text" name="last_name" value="<?php echo $user_info->last_name?>" class="text short" id="last-name" maxlength="45" />
      <?php
        }
      ?>
    </div>
    
    <div class="field">
      <h4><span class="required"> * </span><label for="user-email"><?= __("Email") ?></label></h4>
      <?php
        if (!empty($request_data['last_name'])) {
      ?>
      <input class="text short" id="user-email" type="text" name="email_address" value="<?php echo $request_data['email_address'];?>" />
      <?php
        } else {
      ?>
      <input class="text short" id="user-email" type="text" name="email_address" value="<?php echo $user_info->email?>" />
      <?php
        }
      ?>
    </div>

    <div class="field">
      <h4><label for="upload_user_image"><?= __("Upload Photo") ?></label></h4>
      <input name="userfile" type="file" class="text short" id="upload_user_image"/>
      <input type="hidden" name="uid" value="<?php echo $uid?>" />
      <input type="hidden" name="profile_type" value="basic" /><br />
    </div>

    <div class="field_bigger">
      <h4><?= __("Current Image") ?></h4>
      <div class="curr_image">
        <?php print "<a href=\"". PA::$url . PA_ROUTE_USER_PUBLIC . "/$uid\">".uihelper_resize_mk_user_img($user_info->picture, 75, 80, 'alt="Current Image"')."</a>"; ?>
        <span class="remove_picture">
          <?php
            if (!empty($user_info->picture)) {
              echo '<a href="'. PA::$url . PA_ROUTE_EDIT_PROFILE . '/type=basic&action=DeleteUserPic" >'.__("Remove Picture").'</a>';
            }
          ?>
        </span>
      </div>
    </div>

  </fieldset>

  <div class="button_position">
    <input type="hidden" name="action" value="SaveProfile" />
    <input type="submit" name="submit" value="<?= __("Apply Changes") ?>" />
  </div>
</form>