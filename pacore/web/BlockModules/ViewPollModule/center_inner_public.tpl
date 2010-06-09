<?php error_reporting(0);?>
<div id="view_poll">  
<form>
<fieldset class="center_box">
<legend><?= __("Polls") ?></legend>  

 <?php 
  $cnt = count($prev_poll);
   if ($cnt == 1) {
     echo __("No previous poll to display");
   } else {
     $cnt = ($cnt > 6 )? 6 :$cnt;
    $k = 0;
    for($i=0;$i<$cnt;$i++) {
      if ($prev_poll[$i]->poll_id != $current_poll[0]->poll_id) { ?>
            <span>
              <?php
              echo '<br/>'.$prev_poll[$i]->title;?>
            </span>
            <div style="width:100%; float:left;">
              <span style="float:left; width:200px; margin-top:15px; font-weight:normal;">
                <?php
                echo "Start Date:".date('M-d-Y,G:i',$prev_poll[$i]->created);
                echo '<br />'."End Date:".date('M-d-Y,G:i',$prev_poll[$i]->changed);
                ?>
              </span>
              <?php
              $poll_id =$prev_poll[$i]->poll_id; 
              $j = 1;
              while ($prev_options[$i]['option'.$j] != '') {
                $legend[$k][] = $prev_options[$i]['option'.$j];  
        
                $j++; 
              }
              $per = serialize($per_prev_poll[$poll_id]);
              ?>
              <span style="float:left; width:250px; margin-top:5px;">
                <?php if(!empty($per_prev_poll[$poll_id])) {?>
                  <img style="vertical-align:top;" src="<?php echo PA::$url;?>/pie_chart.php?id=<?php echo $poll_id;?>" alt="loser"  />
                <?php }else {?>
                  <?= __("No votes are posted for this poll") ?>.
                <?php }?>
              </span>
             
          </div>
          <?php
        $k++;
     } 
    }
   
  }
 ?>   
 

</fieldset>
</form>
</div>