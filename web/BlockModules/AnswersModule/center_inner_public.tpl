
<div class="answers">
  <ul>
   <?php
       if(!empty($links)) {
         $cnt = count($links); 
         for($i=0; $i< $cnt; $i++) { ?>
    <li>
       <?php echo ucfirst($links[$i]['name']);?><?= __("'s Answers") ?><br />
       <?php echo $links[$i]['comment'];?>
    </li><hr />
   <? } 
   } else { ?>
    <li><?= __("No answer is posted till now") ?></li>
   <? } ?>
  </ul>
</div> 

<form name='answers_form' action="" method='post'>
<?php
  if(!empty($question_id)) {
?>
    <fieldset class="center_box">
      <legend><?= __("Submit your answer") ?></legend>
        <div class="field_bigger">
        <label ><span class="required"> * </span> <?= __("Answers") ?></label>
          <textarea name="answer" cols="55" rows="5"></textarea>
        </div>
      <input type='hidden' name='id' value='<?php echo $question_id;?>' />
      <input type='hidden' name='action' value='SaveAnswer'>
      <div class="button_position">
        <input type='submit' name='submit' value='<?= __("Submit Answers") ?>' />
      </div>
    </fieldset>
<?php
  }
?>
</form>