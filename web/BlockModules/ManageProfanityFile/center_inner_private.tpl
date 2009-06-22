<div id="new_ad">
  <form action="" method = "post">
    <fieldset class="center_box">
      <legend><?= __("Manage Profanity List") ?></legend>      
      <div class="field_text">
        <?= __("Please press enter after each entry") ?>
      </div>    
      <div class="field" style="height:400px; width:680px;">
        <textarea name="file_text" id="file_text"  style="height:400px; width:680px;"><?php echo $links;?></textarea>  
      <div class="field_text">
        <?= __("Please press enter after each entry") ?>
      </div>    
      </div>
      <div  class="button_position">
        <input type="submit" name="save" value="<?= __("Save") ?>" class="buttonbar" />
      </div>        
    </fieldset>
  </form>
</div>
