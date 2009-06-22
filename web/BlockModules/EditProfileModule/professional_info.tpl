<?php
global $_PA;

// echo "<pre>" . print_r($this->user,1) . "</pre>";
$profile = &$this->user->{'professional'};

if (isset($_POST['submit']) && ($_POST['profile_type'] == 'professional')) {
  global $msg, $nsg2, $msg_pro, $uploaddir;
  
  require_once "web/includes/classes/file_uploader.php";
  if (!empty($_FILES['user_cv']['name'])) {
     $myUploadobj = new FileUploader; //creating instance of file.
     $file_type = 'doc';
     $file = $myUploadobj->upload_file($uploaddir,'user_cv',true,true,$file_type);
     if( $file == false) {
       $msg = $myUploadobj->error;
       $error = TRUE;
     } else {
       $user_cv = $file;
       $_POST['user_cv']['value'] = $user_cv;
       Storage::link($user_cv, array("role" => "cv", "user" => PA::$login_user->user_id));
     }
   } else {
     $user_cv = $this->user->{'professional'}['user_cv']['value'];
     $_POST['user_cv']['value'] = $user_cv;
   }

  // $this is  DynamicProfile class instance  
  $this->processPOST('professional');
  if (! $error) {
    try {
      // $this is  DynamicProfile class instance  
      $this->save('professional', PROFESSIONAL);
    } catch (PAException $e) {
      $msg = "$e->message";
      $save_error = TRUE;
    }
  }
  
  if ($error == TRUE || $save_error == TRUE) {
    $msg = __('Sorry: you are unable to save data').'<br>'.__('Reason: ')."$msg";
  } else {
		global $error_msg;
		$error_msg = __('Profile updated successfully.');
  }
}
?>

  <h1><?= __("Professional Info") ?></h1>
      <form enctype="multipart/form-data" action="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=professional&action=save" method="post" >
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
    $label = __("Upload CV");
    $fieldname = "user_cv";
    $f = @$this->user->{'professional'}[$fieldname];
    $v = @$f['value'];    
  ?>
      <div class="field_medium">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <input type="file" class="text normal" id="user_cv" name="user_cv" />
        </div>
        <div>
        <?php 
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
            <div class="field_text">
              <?= __("Valid file types are .doc and .pdf") ?>.
              <?php if (!empty($v)) { 
              ?><span class="required"><?= __("This will replace your current CV") ?> (<a href="<?= htmlspecialchars(Storage::getURL($v)) ?>">click here to download</a>)<span> 
              <? } ?>
            </div>
      </div>

          <?php
            $this->textfield(__("Headline"), "headline", "professional");
            $this->textfield(__("Industry"), "industry", "professional");
            $this->textfield(__("Company"), "company", "professional");
            $this->textfield(__("Title"), "title", "professional");
            $this->textfield(__("Work Phone"), "workPhone", 'professional');
            $this->textfield(__("Website"), "website", "professional");
            $this->textarea(__("Career Skills"), "career_skill", "professional", NULL, true, "Enter Career Skill separated by commas");
            $this->textfield(__("Prior Company"), "prior_company", "professional");
            $this->textfield(__("City"), "prior_company_city", "professional");
            $this->textfield(__("Prior Title"), "prior_company_title", "professional");
            $this->textfield(__("College Name"), "college_name", "professional");
            $this->textfield(__("Degree"), "degree", "professional");
            $this->textarea(__("Summary"), "summary", "professional", NULL, true, __("Your full Professional biography here. Please note there is a 2,500 character limit."));
            $this->textarea(__("Languages"), "languages", "professional");
            $this->textarea(__("Honors &amp; Awards"), "awards", "professional");
          ?>
        </fieldset>
        
        <div class="button_position">
          <input type="hidden" name="profile_type" value="professional" />
          <input type="submit" name="submit" value="<?= __("Apply Changes") ?>" />
        </div>
        
      </form>