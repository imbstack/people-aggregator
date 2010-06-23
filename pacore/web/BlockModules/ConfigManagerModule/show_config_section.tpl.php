<?php
?>
  <div class="wrapper">
    <div>
      <form name="show_mode" method="get" action="">
        <fieldset class="center_box">
        <br />
        <legend><?=__("Select edit mode and section name")?></legend>
        <label><?=__("Mode")?>:</label><?=$mode_tag?>
        <label><?=__("Section")?>:</label><?=$sect_tag?>
        <input type="button" name="submit_mode" value="Go" onclick="javascript: document.forms['show_mode'].submit();" />
        </fieldset>
      </form>
    </div>
    <div class="message">
      <?php if (!empty($message)) { ?>
        <?=$message?>
      <?php } ?>
    </div>
    <div class="section">
      <?php echo $data->getHtml(); ?>
    </div>
  </div>
