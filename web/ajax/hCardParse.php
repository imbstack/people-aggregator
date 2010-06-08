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
/**
 *
 * @name hCardParse.php
 * @author Zoran Hron
 *
 *
 * @brief : Parse and display hCard/XFN identity and relations data. This script must be called trough AJAX
 *
 **/
 
require_once dirname(__FILE__).'/../../config.inc';
require_once "web/includes/classes/pAhCardXfn.class.php";



if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
  
  $curr_theme_path = $_REQUEST['current_theme_path'];
  $mode = $_REQUEST['mode'];
  $hCardParser = new pAhCardXfn();
  
  if($mode == 'url') {
    $url  = $_REQUEST['source'];
    $is_url = preg_match('|^http(s)?://[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    if($is_url) {
      $hCardParser->getFromUrl($url, false); 
    } else {
      echo '<p>' . __('Please enter a valid URL!') . '</p>';
    }  
  }
  
  if($mode == 'text') {
      $text  = $_REQUEST['source'];
      $text = html_entity_decode($text, ENT_QUOTES);
      $hCardParser->getFromString(utf8_encode($text)); 
  }
  $hCards_personal = $hCardParser->getCardsByType('personal');
  $hCards_profess  = $hCardParser->getCardsByType('professional');
  $hCards_XFN      = $hCardParser->getRelations();
  $nPers = count($hCards_personal);
  $nProf = count($hCards_profess);
  $nXFN  = count($hCards_XFN);
    
  $gd_instaled = false;  
  if(function_exists("gd_info") &&
     function_exists("imagecreatefromjpeg") &&
     function_exists("imagecreatefromgif")) {
     
     $gd_instaled = true;  
  }    
  
  $serialized_obj = base64_encode(serialize($hCardParser));          // post back hCardXFN serialized object
}  
?>

<?php if($nPers || $nProf || $nXFN) : ?>
    <?php if($nPers) : ?>
      <div class="profile_conteiner">
        <h4>Please select one of the following Personal profiles: </h4>
        <?php foreach($hCards_personal as $k => $card) : ?>
          <div class="profile_content">
            <div class="profile_image">
              <?php if(isset($card['value']['photo'])) : ?>
                <?php if($gd_instaled) : ?> 
                  <img src="<?php echo PA::$url.'/resize_img.php?src='. $card['value']['photo'] .'&height=98&width=98'?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php else: ?>
                  <img src="<?php echo $card['value']['photo']?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php endif; ?>
              <?php else: ?>
                <img src="<?php echo $curr_theme_path . '/images/default.png' ?>" alt ="no image" />                   
              <?php endif; ?>
            </div> 
            <div class="profile_text">
               <?php echo $card['value']['fn']?> 
            </div> 
            <div class="profile_select">
               <label class="label_profile" for="prof_selected_<?php echo $k ?>">Select </label>
               <input type="radio" name="profile_selected" id="prof_selected_<?php echo $k ?>" value="<?php echo $k ?>" />
            </div>  
          </div>   
        <?php endforeach; ?>
      </div>  
    <?php endif; ?>
    <?php if($nProf) : ?>
      <div class="profile_conteiner">
        <h4><?php echo __('Please, select one of the following Professional profiles: ') ?></h4>
        <?php foreach($hCards_profess as $k => $card) : ?>
          <div class="profile_content">
            <div class="profile_image">
              <?php if(isset($card['value']['photo'])) : ?>
                <?php if($gd_instaled) : ?> 
                  <img src="<?php echo PA::$url.'/resize_img.php?src='. $card['value']['photo'] .'&height=98&width=98'?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php else: ?>
                  <img src="<?php echo $card['value']['photo']?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php endif; ?>
              <?php else: ?>
                <img src="<?php echo $curr_theme_path . '/images/default.png' ?>" alt ="no image" />                   
              <?php endif; ?>
            </div> 
            <div class="profile_text">
               <?php echo $card['value']['fn']?> 
            </div> 
            <div class="profile_select">
               <label class="label_profile" for="prof_selected_<?php echo $k ?>">Select </label>
               <input type="radio" class="{required:true}" name="profile_selected" id="prof_selected_<?php echo $k ?>" value="<?php echo $k ?>" />
            </div>  
          </div>   
        <?php endforeach; ?>
      </div>   
    <?php endif; ?>
    <?php if($nXFN) : ?>
      <div class="profile_conteiner">
        <h4><?php echo __('Please, select the XFN relations you want to import: ') ?></h4>
        <?php foreach($hCards_XFN as $kr => $card) : ?>
          <div class="profile_content">
            <div class="profile_image">
              <?php if(isset($card['value']['photo'])) : ?>
                <?php if($gd_instaled) : ?> 
                  <img src="<?php echo PA::$url.'/resize_img.php?src='. $card['value']['photo'] .'&height=98&width=98'?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php else: ?>
                  <img src="<?php echo $card['value']['photo']?>" alt ="<?php echo $card['value']['fn']?>" />
                <?php endif; ?>
              <?php else: ?>
                <img src="<?php echo $curr_theme_path . '/images/default.png' ?>" alt ="no image" />                   
              <?php endif; ?>
            </div> 
            <div class="profile_text">
               <?php echo $card['value']['fn']?> 
            </div> 
            <div class="profile_select">
               <label class="label_profile" for="relation_<?php echo $kr ?>">Select </label>
               <input type="checkbox" name="rel_selected[<?php echo $kr ?>]" id="relation_<?php echo $kr ?>" />
            </div>  
          </div>   
        <?php endforeach; ?>
      </div>  
    <?php endif; ?>
    <div class="buttonbar" style="float: right; margin: 12px;">
      <input type="hidden" name="hcards_obj" id="hcards_obj" value="<?php echo $serialized_obj ?>" />
      <input type="hidden" name="submit_type" id="submit_type" value="hcard" />
      <ul><li>
        <input type="image" src="<?php echo $curr_theme_path.'/images/bt_submit.gif'?>" height="20" name="form_submit" alt="Submit" />
      </li></ul>
    </div>
    <?php else : ?>
  <p>
    <?php echo  __("Sorry, we couldn't any identity informations ") ?>
    <?php echo (($mode=='url') ? __('at: ') . $url : __('in your HTML text.')) ?>
  </p>  
  <p>
     <?php foreach($hCardParser->getErrors() as $k => $v) : ?> 
        <?php echo 'Error: ' . $v . '<br/>' ?>
     <?php endforeach; ?>   
  </p>
<?php endif; ?>

