<div id="poll_parent" >
<wbr /><style type="text/css"><!--
div#poll_parent {
 background-color: white;
 width: 194px;
 padding: 2px 0px 2px 0px;
 border: solid 2px #eeeeee;
 float:left;
 margin-left:10px;
}
div#poll_parent h4 {
 font-size: 1.2em;
 background-color: #e0e0e0;
}
div#poll_parent h4 {
 margin: 2px 0px 2px 0px;
}
div#poll_module {
  float:left;
  width:194px;
  padding:5px;
  backgrocund:lime;
}
div#poll_module div {
  float:left; 
  width:100%;
  margin-top:5px;
  backgrxound:red;
}
div#poll_module span {
  float:left; 
  width:80px;
}

div#poll_module span.poll_bar {
  float:left; 
  padding-top:3px;
  width:117px;
}

div#poll_module span.percent {
  float:right; 
  width:70px;
}

div#poll_button {
  text-align:center;
  width:50px;
}

#view_poll span {
  font-size:12px;
  font-weight:bold;
  width:100%;
  text-transform:capitalize;
}
--></style>
<form method="post" action="javascript:get_poll(document.forms['poll_form']);" name="poll_form" method="post">
  <h4>Take Our Poll</h4>
  <div id="poll_module">
   <h5><?php echo $topic[0]->title?></h5>
     <?php $cnt = count($options);
           for ($i=1;$i<=$cnt;$i++){?>
        <?if ($options['option'.$i] != '') {
            if ($flag == 0) {
              echo '<div>';
              $vote = $options['option'.$i]; 
              echo '<span>';
              echo stripslashes($options['option'.$i]);
              echo '</span>';?>
              
              <input type='radio' name='vote' value="<?php echo htmlentities($vote);?>" />
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
      <input type="hidden" value="<?php echo PA::$login_uid;?>" name="uid" />
       <?php if($flag == 0 && !empty(PA::$login_uid)) {?>
        <div id="poll_button">
          <br/>
          <input type="submit" name="submit" value="Vote" />
        </div>
      <?}elseif (empty(PA::$login_uid)) {?>
        <b style="text-align:center;font-size:12px;width:100%;">Please login to vote</b>
      <?php }?>    
    </div>
  </form>
</div>
