<?php
   
   
  require_once "web/includes/classes/xHtml.class.php";
  
  $req = '';
  if (isset($_REQUEST['GInvID'])) {
    $req = '?GInvID='. $_REQUEST['GInvID'];
  } else if (isset($_REQUEST['InvID'])) {
    $req = '?InvID='. $_REQUEST['InvID'];
  }
  if (isset($_REQUEST['token']) && !empty($req)) {
    $req .= '&amp;token='. $_REQUEST['token'];
  }
  $mother_network_info = Network::get_mothership_info();
  $extra = unserialize($mother_network_info->extra);
?>
<div id="register">


        <div id="class_description">
          <br />
          <h4><?= sprintf(__("Welcome to %s!"), PA::$site_name) ?></h4>
          <div><?= sprintf(__("With your %s account you will be able to:"), PA::$site_name) ?></div>
          <ul>
            <li><?= __("Edit, share and store all your digital stuff in one place.") ?></li>
            <li><?= __("Manage all your friends, groups and content.") ?></li>
            <li><?= __("Unite your online life by connecting all your presences together.") ?></li>
          </ul>
          <div><?= __("Provide the following information to join and start connecting all your digital worlds!") ?></div>
        </div>

        <form name="formRegisterUser" enctype="multipart/form-data" action="register.php<?php echo $req;?>" method="post" onsubmit="return validateForm12(document.formRegisterUser);">
          <fieldset class="center_box">
            <div id="validation_error"></div>
            <input type="hidden" name="group_id" value="<?= @$group_id?>" />
            <div class="field_medium">
              <h4><label><span class="required"> * </span> <?= __("Login name") ?>:</label></h4>
              <input id="login_name" type="text" name="login_name" value="<?= htmlspecialchars(@$_POST['login_name']) ?>" class="text" />
              <div class="field_text">
                <?= __("Login name may contain letters, numbers and underscores (_).") ?>
              </div>
            </div>
            
            <div class="field_medium">
              <h4><label><span class="required"> * </span> <?= __("First name") ?>:</label></h4>
              <input type="text" class="text" name="first_name" value="<?= htmlspecialchars(@$_POST['first_name']) ?>" />
            </div>
          
          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Last name") ?>:</label></h4>
            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars(@$_POST['last_name']) ?>" class="text" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Date of birth") ?>:</label></h4>
            <?php $days = array(); for($i = 1; $i < 32; $i++) $days[$i] = $i; ?>
            <?= xHtml::selectTag($days, array('name' => 'dob_day', 'id' => 'dob_day'), htmlspecialchars(@$_POST['dob_day'])) ?>
            <?= xHtml::selectTag(array_flip(PA::getMonthsList()), array('name' => 'dob_month', 'id' => 'dob_month'), (!empty($_POST['dob_month'])) ? htmlspecialchars($_POST['dob_month']) : "1") ?>
            <?= xHtml::selectTag(array_flip(PA::getYearsList()), array('name' => 'dob_year', 'id' => 'dob_year'), htmlspecialchars(@$_POST['dob_year'])) ?>
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Street address 1") ?>:</label></h4>
            <input type="text" name="homeAddress1" id="homeAddress1" value="<?= htmlspecialchars(@$_POST['homeAddress1']) ?>" class="text short" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Street address 2") ?>:</label></h4>
            <input type="text" name="homeAddress2" id="homeAddress2" value="<?= htmlspecialchars(@$_POST['homeAddress2']) ?>" class="text short" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("City") ?>:</label></h4>
            <input type="text" name="city" id="city" value="<?= htmlspecialchars(@$_POST['city']) ?>" class="text" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("State/Province") ?>:</label></h4>
            <?= xHtml::selectTag(array_flip($states), array('name' => 'state', 'id' => 'state_1'), (isset($_POST['state'])) ? htmlspecialchars(@$_POST['state']) : -2) ?>
            <input type="text" name="stateOther" id="stateOther" value="<?= htmlspecialchars(@$_POST['stateOther']) ?>" class="text display_false" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Country") ?>:</label></h4>
            <?= xHtml::selectTag(array_flip($countries), array('name' => 'country', 'id' => 'country'), (isset($_POST['country'])) ? htmlspecialchars(@$_POST['country']) : -1) ?>
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("ZIP Code") ?>:</label></h4>
            <input type="text" name="postal_code" id="postal_code" value="<?= htmlspecialchars(@$_POST['postal_code']) ?>" class="text" />
          </div>

          <div class="field_medium">
            <h4><label><span class="required"> &nbsp; </span><?= __("Phone #") ?>:</label></h4>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars(@$_POST['phone']) ?>" class="text" />
          </div>
          <div class="field_medium">
            <h4><label><span class="required"> * </span> <?= __("Password") ?>:</label></h4>
            <input type="password" class="text" name="password" id="password"/>
            <div class="field_text">
      <?= __("Password can be 5-15 characters long, and is case sensitive.") ?>
     </div>
          </div>
          
          <div class="field_medium">
            <h4><label><span class="required"> * </span> <?= __("Confirm password") ?>:</label></h4>
            <input type="password" class="text" name="confirm_password" id="confirm_password"/>
          </div>
          
          <div class="field_medium">
            <h4><label><span class="required"> * </span> <?= __("Email") ?>:</label></h4>
            <input type="text" name="email" class="text" value="<?= htmlspecialchars(@$_POST['email']) ?>" />
          </div>

        <div class="field_medium" style="height: auto">
         <div>
           <h4><label><?= __("Upload a photo") ?>:<span class="required"> &nbsp; </span></label></h4>
           <div id="userfile_wrapper">
           <input name="userfile" id="userfile" type="file" class="text" />
           </div>
         </div>
         <div style="clear: left; display:none">
           <h4><label><?= __("Or enter a photo URL") ?>:<span class="required"> &nbsp; </span></label></h4>
           <input type="text" name="avatar_url" id="avatar_url" class="text short" value="<?= htmlspecialchars(@$_POST['avatar_url']) ?>" />
         </div>
         <div class="field_text" style="margin-top:8px;">
           <a href ="#" id="prev_image" title="<?= __("Preview Image") ?>" /><?= __("Preview Image") ?></a> |
           <a href ="#" id="clear_image" title="<?= __("Clear Image") ?>" /><?= __("Clear Image") ?></a>
         </div>
        </div>
        <div id="image_preview">
          <div id="loading_preview"></div>
          <? if (!empty($_POST['user_filename'])) { /* validated in register.php */ ?>
            <div id="your_photo" class="field_medium" style="height: auto">
              <h4><label><?= __("Your photo") ?>:<span class="required"> &nbsp; </span></label></h4>
              <input type="hidden" name="user_filename" value="<?= htmlspecialchars(@$_POST['user_filename']) ?>" />
            <div><?= uihelper_resize_mk_img($_POST['user_filename'], 200, 200, NULL, 'style="margin-left: 10px"') ?></div>
              <div><b><?= __("To use a different image, click Clear Image and select a new image.") ?></b></div>
            </div>
          <? } ?>
        </div>

          <div class="field_medium">
            <input type="checkbox" name="chkbox_agree" id="chkbox_agree" checked="checked" value="1"/><?= sprintf(__("I have read and agree to the %s <a href='%s/terms.php' target='_blank'>Terms and Conditions</a>"), PA::$site_name, PA::$url) ?>.
          </div>
          <?php if (@$extra['captcha_required'] == NET_YES) { // added by Z.Hron - if captcha is required ?>
          <div class="capcha_image" style="margin-left:36px;">
            <img src="comment_verification.php"><br />Enter the text as above in the box&nbsp;
            <input name="txtNumber" type="text" size="12">
          </div>
          <? } ?>
          <input type = "hidden" name = "op" value = "register" />
           <div class="button_position"> <input type="image" name="submit" id="joinbutton" value="<?= __("Join now") ?>" src="<?=PA::$theme_url;?>/images/join-now.gif" alt="Join now"/></div>

<!--        </body> -->
       </fieldset>
      </form>


</div>
