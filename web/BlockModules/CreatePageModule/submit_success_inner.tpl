<?php

?>

<fieldset class="center_box">
  <div class="field_bigger" >
    <label for="form_data_page_id"><b><?= __("Your page created sucessfull. Page data") ?>:</b></label>
    <pre><?= print_r($page_settings,1) ?></pre>
    <div class="field_text"><br />
      NOTE: This array could be serialized and sent in a $_POST parameter to dynamic.php script<br />
       to create a page "in fly" and could be called on the following way:<br/>
       <b><?= PA_BASE_URL . "/dynamic.php?page_id=". $page_settings['page_id'] ?></b>
    </div>
  </div>
  <div class="field_big" >
   <form name="preview_page" action="/dynamic.php?page_id=<?= $page_settings['page_id'] ?>&save=<?= $save_page ?>" method="POST">
    <input type="hidden" name="page_settings" id="page_settings" value='<?= urlencode($serialized_settings) ?>' />
    <div class="button_position">
       <input type="submit" name="previewPage" value="<?= __("Click here for preview") ?>" />
    </div>
   </form>
  </div>
</fieldset>
