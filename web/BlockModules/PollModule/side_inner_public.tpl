<div>
	<form method="post" action="<?= PA::$url?>/save_vote.php">
    <div id="poll_module">
      <h5><?= $topic[0]->title ?> (<?= $total_vote?> votes)</h5>
      <?php $cnt = count($options);
           for ($i=1; $i<=$cnt; $i++) {
           	if ($options['option'.$i] != '') {
           		if ($flag == 0) { 
	              $vote = $options['option'.$i]; 
  	         		?>
              	<div>
              	<span>
              <?= $options['option'.$i]; ?>
              </span>
              <input type="radio" name="vote" value="<?= htmlentities(addslashes($vote));?>">
              <br/>
              </div>
            <?php } else { 
              $j = $i-1;
            ?>
              <div>
              <span>
              <?= $options['option'.$i]; ?>
              </span>
              <span class="poll_bar">
              	<img src="<?=PA::$url ?>/makebar.php?rating=<?=$percentage[$j]?>&amp;width=95&amp;height=10" border="0" />
              	</span>
              <span class="percent">
              <?=$percentage[$j]?>
              %</span>
            <br/>
            </div>
          <?php
          }
       }
     } ?>
     
      <input type="hidden" value="<?= $topic[0]->poll_id;?>" name="poll_id" />
      
      <?php if($flag == 0) { ?>
        <div id="poll_button">
          <br/>
          <input type="submit" name="submit" value="<?= __("Answer") ?>" />
        </div>
      <? } ?>
    </div>
  </form>
</div>
