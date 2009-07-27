<div> <?php  ?> <form method="post" action="<?php echo PA::$url?>/save_vote.php">
    <div id="poll_module">
      <h5><?php echo $topic[0]->title?></h5>
      <?php $cnt = count($options);
           for ($i=1;$i<=$cnt;$i++){?>
        <?if ($options['option'.$i] != '') {
            if ($flag == 0) {
              echo '<div>';
              $vote = $options['option'.$i]; 
              echo '<span>';
              echo $options['option'.$i];
              echo '</span>';?>
              
              <INPUT TYPE=RADIO NAME='vote' VALUE="<?php echo htmlentities(addslashes($vote));?>">
              <?php
              echo "<br/>";
              echo '</div>';
            } else {
              echo '<div>';
              echo '<span>';
              echo $options['option'.$i];
               echo '</span>';
              $j = $i-1;
              echo "<span class='poll_bar'>" .'<img src="'.PA::$url.'/makebar.php?rating='.$percentage[$j].'&amp;width=95&amp;height=10" border="0" />'."</span>";
              echo "<span class='percent'>". $percentage[$j].'%'."</span>";
              echo "<br/>";
             
              echo '</div>';  
            }
          }?>
      <?php }?>
      <input type="hidden" value="<?php echo $topic[0]->poll_id;?>" name="poll_id" />
      
      <?php if($flag == 0) {?>
        <div id="poll_button">
          <br/>
          <input type="submit" name="submit" value="<?= __("Vote") ?>" />
        </div>
      <?} ?>
    
    </div>
  </form>
</div>
