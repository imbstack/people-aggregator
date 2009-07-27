<?php

$profile = &$this->user->{'general'};

$non_us_state = FALSE;
if(!empty($profile['state']) && !in_array($profile['state'], array_values(PA::getStatesList()))) {
  $non_us_state = TRUE;
}

if (isset($_POST['submit']) && ($_POST['profile_type'] == 'general')) {
  if (!empty($_POST['state']) && $_POST['state'] == 'Other') {
    $_POST['state'] = trim($_POST['state_other']);
  }

  $personal_website = @$_POST['personal_website']['value'];
  $day = @trim($_POST['dob_day']['value']);
  $month = @trim($_POST['dob_month']['value']);
  $year = @trim($_POST['dob_year']['value']);
  $_POST['dob']['value'] = $year.'-'.$month.'-'.$day; // YYYY-MM-DD

  if ($day && $month && $year && $day !=0 && $month !=0 ) {
    $day = ($day < 10) ? '0'.$day : $day;
    $month = ($month < 10) ? '0'.$month : $month;
    $dob = $year.'-'.$month.'-'.$day; // YYYY-MM-DD
    $dob_validation = checkdate($month, $day, $year);
    if (! $dob_validation) {
      $msg = __("The Date of Birth is invalid.");
      $error = TRUE;
    }
  } else {
    $dob = '';
  }

  $this->processPOST('general'); // so we get this data for display

  /* disabled 2007-10-30 by PP as many countries have non-numeric postal codes!
  //check for invalid zipcode in general profile.
  $postal_code = @trim($_POST['postal_code']['value']);
  if (!empty($postal_code) && !is_numeric($postal_code)) {
    $msg = MessagesHandler::get_message(3000);
    $error = TRUE;
  }
  */

 if(!empty($personal_website) && !Validation::isValidURL($personal_website)) {
    $msg = __('Url is invalid');
    $error = TRUE;
  }

  if ($error != TRUE) {

    $tags = explode(',', $_POST['user_tags']['value']);
    foreach ($tags as $term) {
      $tr = trim($term);
      if ($tr) {
        $terms[] = $tr;
      }
    }

    // here we can define fields which has permission to everybody
    $copy_over = Array('user_caption_image', 'desktop_image_action', 'desktop_image_display');
    foreach($copy_over as $f) {
      $_POST[$f] = $user_data_general[$f];
      $_POST[$f . '_perm'] = 1;
    }

    try {
      // $this is  DynamicProfile class instance
      $this->save('general', GENERAL);
      Tag::add_tags_to_user(PA::$user->user_id, $terms);
    } catch (PAException $e) {
      $msg = "$e->message";
      $save_error = TRUE;
    }
  }

  if ($error == TRUE || $save_error == TRUE) {
    $msg = __('Sorry: you are unable to save data.').'<br>'.__('Reason: ').$msg;
  } else {
    // invalidate the cache for user profile
    header("Location: ".PA::$url.PA_ROUTE_EDIT_PROFILE."?type=general&updated=1");
  }
}
?>
  <h1><?= __("General Info") ?></h1>
      <form enctype="multipart/form-data" name="drop_list" action="" method="post">
        <fieldset>
          <div class="field">
            <h4><label for="multiple_select"><?= __("Select for All") ?></label></h4>

            <div>
      <?php echo uihelper_get_user_access_list('select_multiple',
        'NONE',
        $other_params = ' onchange="javascript: set_all_perms(this)"');?>
            </div>
            <br />
            &nbsp;
          </div>

          <?php
            $this->textfield(__('Slogan'), 'user_caption', 'general', NULL, TRUE, __("Slogan will appear on your Public Page."));
            $this->textfield(__('Shout Out'), 'sub_caption', 'general', NULL, TRUE, __("Shout out will appear on your Public Page."));

          $sex = array();
          $sex[] = array('label'=>'Male','value'=>'Male');
          $sex[] = array('label'=>'Female','value'=>'Female');
          $this->radiobar(__("Gender"), 'sex', $sex, 'general');
          $this->dateselect(__("Date of Birth"), 'dob', 'general');
          $this->textfield(__("Address"), "homeAddress1", 'general',NULL, TRUE, NULL);
          $this->textfield(__("Address 2"), "homeAddress2", 'general',NULL, TRUE, NULL);
          $this->textfield(__("City"), "city", 'general');
//          $this->select(__('State/Province'), 'state', array_values(PA::getStatesList()), 'general');
          ?>
<!--
          <div class="field" id="other_state_div" style="display:none;">
          <?php echo $this->textfield(__('Other state'), 'state_other', 'general', NULL, FALSE); ?>
          </div>
-->
          <?php
          $this->textfield(__('State/Province'), 'state', "general");
          $this->select(__("Country"), "country", array_values(PA::getCountryList()), 'general');
//          $this->textfield(__("Country"), "country", "general");
          $this->textfield(__("Zip/Postal Code"), "postal_code", "general");
          $this->textfield(__("Phone"), "phone", 'general',NULL, TRUE, NULL);
          $this->textfield(__("Mobile Phone"), "mobilePhone", 'general',NULL, TRUE, NULL);


          $this->textarea(__("User Tags (Interests)"), "user_tags", "general", NULL, TRUE,
          __("Seperate tags with commas."));
          ?>

        </fieldset>

        <div class="button_position">
          <input type="hidden" name="profile_type" value="general" />
          <input type="submit" name="submit" value="<?= __("Apply Changes") ?>" />
        </div>


      </form>
