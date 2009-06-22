<?php
global $facebook_api_key;

ob_start();
?>
        <div id="facebook_invite" style="margin-left:0px;">
          <fb:login-button length="long" autologoutlink="true"></fb:login-button>
          <fb:serverfbml style="width: 755px;">
            <script type="text/fbml">
            <fb:fbml>
              <fb:request-form action="<?= htmlspecialchars(PA::$url) ?>/invitation" method="POST" invite="true" type="<?= htmlspecialchars(PA::$site_name) ?>" content="<?= htmlspecialchars(PA::$facebook_request_string) ?> <fb:req-choice url='<?= htmlspecialchars(PA::$url) ?>/register.php' label='Join <?= htmlspecialchars(PA::$site_name) ?>!' />  " >
                <fb:multi-friend-selector showborder="false" actiontext="Invite your friends to use <?= htmlspecialchars(PA::$site_name) ?>.">
              </fb:request-form>
            </fb:fbml>
            </script>
          </fb:serverfbml>
        </div>
<?php
$invite_xfbml = ob_get_clean();

?><script>
  var FACEBOOK_API_KEY = <?= json_encode($facebook_api_key) ?>, XD_RECEIVER_URL = <?= json_encode(PA::$local_url."/xd_receiver.html") ?>, INVITE_XFBML = <?= json_encode($invite_xfbml) ?>;
</script>
<?php 
?>
  <fieldset style="border: none">
      <h4><label for="username" class="invite_label"><?= __("Choose a program or service below to import your contacts:") ?></label></h4>
      <div class="facebook_invite_box">
        <div class="invite_logo_img" id="button_invite_facebook">
          <!--<a href="<?php echo PA::$url ?>/facebook.php">--><img src="<?php echo PA::$theme_url.'/images/facebook_logo.png'?>" height="32" width="115" style="cursor:pointer" title="Facebook" alt="Facebook" /><!--</a>-->
        </div>
        <div class="signin_facebook" id="facebook_logo">
          <!-- dynamic xfbml goes here -->
        </div>
      </div>
      <div class="plaxo_invite_box">
        <div class="invite_logo_img" id="button_invite_plaxo">
          <img src="<?php echo PA::$theme_url.'/images/plaxo_logo.gif'?>" style="cursor:pointer" title="Plaxo" alt="Plaxo" />
        </div>
        <div class="signin_plaxo" id="plaxo_logo">
          <form class="contacts_signin" action="">
            <div class="input_fields_holder blue_bkgr">
              <label for="username" class="invite_label"><?= __('Your Plaxo ID'). ':'?></label>
              <div>
                <input type="text" size="15" name="plaxo_username" id="plaxo_username" value="">
              </div>
              <div class="field_descr">
                <?= __('(Enter your email or AIM screen name here)') ?>
              </div>
            </div>
            <div class="input_fields_holder blue_bkgr">
              <label for="password" class="invite_label"><?= __('Enter your password'). ':'?></label>
              <div>
                <input type="password" size="15" name="plaxo_password" id="plaxo_password" />
                <input type="hidden" name="action" id="action" value="login" />
              </div>
            </div>
            <div class="input_fields_holder blue_bkgr">
              <img src="<?php echo PA::$theme_url.'/images/contacts.gif'?>" style="cursor: pointer" name="get_contacts_plaxo" id="get_contacts_plaxo" alt="PlaxoSignIn" title="PlaxoSignIn" />
            </div>
          </form>
          <div class="text_info">
            <?=__("We will not store your password. It will only be temporarily used to access your Plaxo address book.") ?>
          </div>
        </div>
      </div>
      <div class="wlive_invite_box">
        <div class="invite_logo_img" id="button_invite_wlive">
          <img src="<?php echo PA::$theme_url.'/images/WindowsLive_logo.gif'?>" style="cursor:pointer" title="Windows Live" alt="Windows Live" />
        </div>
        <div class="signin_wlive" id="wlive_sign_in">
          <form class="contacts_signin" action="">
            <div class="input_fields_holder silver_bkgr">
              <label for="username" class="invite_label"><?= __('Your WindowsLive ID'). ':'?></label>
              <div>
                <input type="text" size="15" name="WindowsLive_username" id="WindowsLive_username" value="">
              </div>
              <div class="field_descr">
                <?= __('(Enter your WindowsLive ID here)') ?>
              </div>
            </div>
            <div class="input_fields_holder silver_bkgr">
              <label for="password" class="invite_label"><?= __('Your password'). ':'?></label>
              <div>
                <input type="password" size="15" name="WindowsLive_password" id="WindowsLive_password" />
                <input type="hidden" name="action" id="action" value="login" />
              </div>
            </div>
            <div class="input_fields_holder silver_bkgr">
              <img src="<?php echo PA::$theme_url.'/images/contacts.gif'?>" name="get_contacts_WindowsLive" id="get_contacts_WindowsLive" style="cursor: pointer" alt="WindowsLive" title="WindowsLive" />
            </div>
          </form>
          <div class="text_info">
            <?=__("We will not store your password. It will only be temporarily used to access your Windows Live address book.") ?>
          </div>
        </div>
      </div>
      <div class="linkedin_invite_box">
        <div class="invite_logo_img" id="button_invite_linkedin">
          <img src="<?php echo PA::$theme_url.'/images/LinkedIn_logo.gif'?>" style="cursor:pointer" title="LinkedIn" alt="LinkedIn" />
        </div>
        <div class="signin_linkedin" id="linkedin_sign_in">
          <form enctype="multipart/form-data" class="contacts_signin" method="POST" action="" id="linkedin_contact">
            <div class="input_fields_holder silver_bkgr">
              <label for="filename" class="invite_label"><?= __('Select CSV file'). ':'?></label>
              <div>
                <input type="file" size="15" name="linkedin_csv" id="linkedin_csv" value="">
              </div>
              <div class="field_descr">
                <?= __('(Select CSV file you want to load)') ?>
              </div>
            </div>
            <div class="input_fields_holder silver_bkgr">
              <input type="hidden" name="action" id="action" value="importLinkedInCSV" />
              <img src="<?php echo PA::$theme_url.'/images/contacts.gif'?>" name="linkedin_contacts" id="linkedin_contacts" style="cursor: pointer" alt="LinkedIn" title="LinkedIn" />
            </div>
          </form>
        </div>
      </div>
      <div class="outlook_invite_box">
        <div class="invite_logo_img" id="button_invite_outlook">
          <img src="<?php echo PA::$theme_url.'/images/Outlook_logo.gif'?>" style="cursor:pointer" title="outlook" alt="outlook" />
        </div>
        <div class="signin_outlook" id="outlook_sign_in">
          <form enctype="multipart/form-data" class="contacts_signin" method="POST" action="" id="outlook_contact">
            <div class="input_fields_holder silver_bkgr">
              <label for="filename" class="invite_label"><?= __('Select CSV file'). ':'?></label>
              <div>
                <input type="file" size="15" name="outlook_csv" id="outlook_csv" value="">
              </div>
              <div class="field_descr">
                <?= __('(Select CSV file you want to load)') ?>
              </div>
            </div>
            <div class="input_fields_holder silver_bkgr">
              <input type="hidden" name="action" id="action" value="importoutlookCSV" />
<!--
              <input type="submit" name="submit_outlook" id="submit_outlook" value="submit_outlook" />
-->
              <img src="<?php echo PA::$theme_url.'/images/contacts.gif'?>" name="outlook_contacts" id="outlook_contacts" style="cursor: pointer" alt="outlook" title="outlook" />
            </div>
          </form>
        </div>
      </div>
  </fieldset>

<script src="http://static.ak.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>