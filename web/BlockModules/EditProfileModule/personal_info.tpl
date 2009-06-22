<?php
global $_PA, $msg1, $user_personal_data, $login_uid;
global $error_msg;

// echo "<pre>" . print_r($this->user,1) . "</pre>";
$profile = &$this->user->{'personal'};

if (isset($_POST['submit']) && ($_POST['profile_type'] == 'personal')) {
  // $this is  DynamicProfile class instance  
  $this->processPOST('personal');
  $save_error = false;
  try {
    // $this is  DynamicProfile class instance  
    $this->save('personal', PERSONAL);
  } catch (PAException $e) {
    $msg = "$e->message";
    $save_error = TRUE;
  }
  if ($save_error == TRUE) {
    $error_msg = __('Sorry: you are unable to save data.').'<br>'.__(' Reason: ').$msg;
  } else {
		$error_msg = __('Profile updated successfully.');
  }
}


?>
  <h1><?= __("Personal Info") ?></h1>

    <form enctype="multipart/form-data" name="drop_list" action="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=personal&action=save" method="post">
      <fieldset>
    <div class="field">
      <h4><label for="multiple_select"><?= __("Select for All") ?></label></h4>            
      <div>
      <?php echo uihelper_get_user_access_list('select_multiple', 
        'NONE', 
        $other_params = ' onchange="javascript: set_all_perms(this)"');?>
    </div><br />
  </div>
  <?php 
    $this->select(__('Ethnicity'), 'ethnicity', $_PA->ethnicities, 'personal');
    $this->select(__('Religion'), 'religion', $_PA->religions, 'personal'); 
    $this->select(__('Political View'), 'political_view', $_PA->political_views, 'personal'); 
    $this->textarea(__("Passion"), "passion", "personal"); 
    $this->textarea(__("Activities"), "activities", "personal"); 
    $this->textarea(__("Books"), "books", "personal"); 
    $this->textarea(__("Movies"), "movies", "personal"); 
    $this->textarea(__("Music"), "music", "personal"); 
    $this->textarea(__("TV Shows"), "tv_shows", "personal"); 
    $this->textarea(__("Cuisines"), "cusines", "personal"); 
  ?>
      </fieldset>
          
      <div class="button_position">
        <input type="hidden" name="profile_type" value="personal" />
        <input type="submit" name="submit" value="<?= __("Apply Changes") ?>" />
      </div>
      
    </form>