<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
  error_reporting(E_ALL); // for testing only!

?>
<?php
  //----------------------- profile and company types: added by Zoran Hron ---------------------
  $companyType = '-1';
  if(isset($_POST['ptype'])) {
    $profileType = $_POST['ptype'];         /* 0=Personal; 1=Professional */
    if($profileType == '0') {               // Personal profile!
      unset($_POST['exposeCompany']);       // we don't need the company info here!
      unset($_POST['exposeSummary']);       // exposeSummary was used for summary company description
      $companyType = '-1';                  // No company info! 
    } else {
      unset($_POST['exposePersonal']);       // we don't need the Personal interests in Professional profile
      $companyType = (isset($_POST['exposeCompany']))
                   ? $_POST['exposeCompany']
                   : '0';                   /* '0'=Current; '1'=Prior; '-1'=No company info */
    }
  } else {
    $profileType = '0';                     // default: 0-Personal!
  }
  //--------------------------------------------------------------------------------------------

  $merger = new ProfileMerger(PA::$login_user, NULL);
  $sections = array_merge( array('core'), array_keys($merger->userSections) );

  $normalizer = new Normalizer('pa2hcard');

  // setting parameters for the XSLT to include/exclude sections etc
  $normalizer->setParameter('','paUid',PA::$login_user->user_id);
  $normalizer->setParameter('','paUrl',PA::$url);
  
  //---- profile and compyny types: added by Zoran Hron -----
  $normalizer->setParameter('','paType',$profileType);
  $normalizer->setParameter('','paCompanyType',$companyType);
 
  //---------------------------------------------------------


/* ------------------------ old code ------------------------

  $allExposes = array(
        'exposeCore', 'exposeAddress', 
        'exposeSummary', 'exposePersonal',
         'exposeEducation', 'exposeCompany', 'exposePriorCompany',
        'exposeEducation',
        'exposeFlickrFriends', 'exposeFacebookFriends'
      );
------------------------------------------------------------ */      
      
/* --- changed by Zoran Hron -------------------------------- */
  $allExposes = array(
        'exposeCore', 'exposeAddress', 
        'exposeSummary', 'exposePersonal',
        'exposeEducation',
        'exposeFlickrFriends', 'exposeFacebookFriends', 'exposeInternalFriends',
        'exposeOtherFriends'
      );
/*------------------------------------------------------------ */      

  $user_image = uihelper_resize_img(PA::$login_user->picture, 35, 35, DEFAULT_USER_PHOTO_REL, 'alt="User image."');
  $user_photo = $user_image['url'];
  $normalizer->setParameter('', 'userPic', $user_photo);
// show the form with previews
// filter the permissions
  /*
  values currently used in PA
  Nobody="0"
  Everybody="1"
  Immediate Relations="2"
  so to show all info a friend would see
  we need to allow perms 1 AND 2
  while friends only would be 2
  */
  $perms = (isset($_POST['perms'])) ? ($_POST['perms']) : "1,2";
  $filter = new Normalizer('filterPerms');
  foreach(preg_split('/,\s*/', $perms) as $p) {
    $parr[] = "perm".$p;
  }
  $filter->setParams( 
    $parr, 
    TRUE );
  $filteredDOM = $filter->transformToDoc( $merger->currentProfileDOM );
    
  $exps = Array();
  foreach($_POST as $k=>$v) {
    if (preg_match('/^expose/', $k)) {
      $exps[] = $k;
    }
  }
  if(! count($exps)) {
    $exps = $allExposes;
    foreach ($exps as $e) $_POST[$e] = 1;
  }

  // generate the hCard
  displayNone($normalizer, $allExposes);
  $normalizer->setParams( $exps, TRUE );
  $hCardXHTML = $normalizer->transformToDoc( $filteredDOM )->saveXML();
//   $filteredDOM->save('/opt/lampp/htdocs/pa/web/BetaBlockModules/EditProfileModule/UserProfile.xml');

// echo htmlspecialchars($hCardXHTML);

?>

<div id="user_info">
   <b><?= __("Personal and professional info") ?></b>
      <ul style="list-style:none;">

<!-- BEGIN: added by Zoran Hron: select profile type -->
        <li>
          <select name="ptype" id="ptype" onchange="document.getElementById('export_profile').submit();">
             <option <?php if ($profileType=='0') echo 'selected="selected"' ?> value="0"> <?= __("Personal") ?> </option>
             <option <?php if ($profileType=='1') echo 'selected="selected"' ?> value="1"> <?= __("Professional") ?> </option>
          </select><b>Profile type</b>:
        </li><br />
<!-- END -->
        
        <li><input type="checkbox" <? if ($_POST['exposeCore']) echo 'checked="checked"' ?> name="exposeCore" value="1" /> Basic info</li>
        <li><input type="checkbox" <? if (isset($_POST['exposeEmail'])) echo 'checked="checked"' ?> name="exposeEmail" value="1" />Email address</li>

<!-- BEGIN: changed by Zoran Hron:
            profile and company types, at this time we don't have company addres details in our database
            and we must use Postal adress! Once time when we will have the company address we must uncomment PHP code below
-->
        <?php // if ($profileType=='0') : ?>
          <li><input type="checkbox" <? if (isset($_POST['exposeAddress'])) echo 'checked="checked"' ?> name="exposeAddress" value="1" /> Postal address</li>
        <?php // endif; ?>      
        <?php if ($profileType=='1') : ?> <!-- profile type = Professional -->
<!--
          <li><input type="checkbox" <? // if (isset($_POST['exposeAddress'])) echo 'checked="checked"' ?> name="exposeAddress" value="1" /> Company address</li>
-->
        <li><input type="radio" <?php if ($companyType=='0') echo 'checked="checked"' ?> name="exposeCompany" value="0" > Current company info</li>
        <li><input type="radio" <?php if ($companyType=='1') echo 'checked="checked"' ?> name="exposeCompany" value="1" > Prior company info</li>
          <li><input type="checkbox"  <? if (isset($_POST['exposeSummary'])) echo 'checked="checked"' ?> name="exposeSummary" value="1" /> Professional summary</li>
        <?php endif; ?>      
<!-- END -->
        <li><input type="checkbox" <? if (isset($_POST['exposeEducation'])) echo 'checked="checked"' ?> name="exposeEducation" value="1" />Education and awards</li>

<!-- BEGIN changed by Zoran Hron:
          1. show personal interests only if profile type is personal
          2. added checkbox for "exposeInternalFriends" option
-->
        <?php if ($profileType=='0') : ?>
          <li><input type="checkbox"  <? if (isset($_POST['exposePersonal'])) echo 'checked="checked"' ?> name="exposePersonal" value="1" /> Personal interests and beliefs</li>
        <?php endif; ?>
        <li><input type="checkbox"  <? if (isset($_POST['exposeInternalFriends'])) echo 'checked="checked"' ?> name="exposeInternalFriends" value="1" />Friends on this network</li>
<!-- END -->
        <li><input type="checkbox"  <? if (isset($_POST['exposeFlickrFriends'])) echo 'checked="checked"' ?> name="exposeFlickrFriends" value="1" />Contacts from Flickr.com</li>
        <li>  <input type="checkbox"  <? if (isset($_POST['exposeFacebookFriends'])) echo 'checked="checked"' ?> name="exposeFacebookFriends" value="1" />Facebook.com friends</li>
        <li>  <input type="checkbox"  <? if (isset($_POST['exposeOtherFriends'])) echo 'checked="checked"' ?> name="exposeOtherFriends" value="1" />Friends from other networks</li>
        <li><p>Filter your data by the visibility you set on each field</p>
        <p>
          <select name="perms">
             <option <? if ($perms=='0,1,2') echo 'selected="selected"' ?> value="0,1,2"> <?= __("show everything") ?></option>
             <option <? if ($perms=='1,2') echo 'selected="selected"' ?> value="1,2"> <?= __("show what your friends see") ?></option> 
             <option <? if ($perms=='1') echo 'selected="selected"' ?> value="1"> <?= __("show only public info") ?></option>
         </select>
         </p></li><br />
        <li><p>
          <input type="submit" value="<?= __("Preview") ?>" />
          </p>
        </li>        
        <li><p>
          <textarea class="copyme" rows="60" cols="40"><? echo htmlspecialchars($hCardXHTML); ?></textarea>
          </p>
        </li>
      </ul>
      </div>
      <div id="user_detail">
        <b><?= __("Sidebar preview of your hCard/XFN profile") ?></b>
          <?php echo $hCardXHTML; ?>
      </div>
<?php
function displayNone($normalizer, $allExposes) {
  $normalizer->setParams(
    $allExposes, FALSE);
}
?>