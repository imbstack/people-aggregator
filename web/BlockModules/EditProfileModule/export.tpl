<?php
  // make sure we can return to ourselves with all parameters intact
  function self_url ($extra_query='') {
    $s = PA::$url.PA_ROUTE_EDIT_PROFILE;
    foreach($_GET as $k=>$v) {
      $s .= (preg_match('/\?/',$s)) ? '&' : '?';
      $s .= "$k=".urlencode($v);
    }
  
    if($extra_query != '') {
      $s .= (preg_match('/\?/',$s)) ? '&' : '?';
      $s .= $extra_query;  
    }
    return $s;
  }
?>
  <h1><?= __("Export") ?></h1>
  <form enctype="multipart/form-data" action="<? 
      $_GET['type'] = 'export'; 
      echo self_url();  
      ?>" method="post" name="export_profile" id="export_profile">
    <fieldset class="export_edit">
      <h2>Your Profile as hCard/XFN</h2>
        <p><?= __("Please modify the hCard/XFN preview below to suit your taste.") ?></p>
        <p><?= __("Once you have selected what you want to display, click 'create hCard', then copy the generated HTML to your blog or wherever you want to use it.") ?></p>
         <? include_once(dirname(__FILE__).'/export.php') ?>
      </fieldset>
      </form>
