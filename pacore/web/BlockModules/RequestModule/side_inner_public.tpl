<form name='requestform' action="<?php echo PA::$url.'/'.FILE_DYNAMIC?>?page_id=<?=PAGE_REQUEST?>&action=RequestModuleSubmit" method='post'>
<div id="register">
   <div id="class_description">
         <br />
          <h4><?= __("Welcome to PeopleAggregator") ?></h4>
          
          <h4><?= __("This is a private network") ?></h4>
          
            <?= __("You need to be member of this network to do anything. You can send a request to join this network by clicking 'Request to join'.") ?> 
           
        </div>
       <div class="button_position">
      <input type="submit" name="request" value="Request to join" /><input type="submit" name="back" value="<?= __("Return to home network") ?>" />
    </div>
</form>
</div>