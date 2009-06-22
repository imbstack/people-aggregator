<?php 
  //current month's first date
  $current_month = mktime(0, 0, 0, date("m"), 1,  date("Y"));
  //previous month's first date
  $previous_month = mktime(0, 0, 0, date("m")-1, 1,  date("Y"));
  //previous to previous month's first date
  $pre_previous_month = mktime(0, 0, 0, date("m")-2, 1,  date("Y"));
  
  $array_month = array($current_month, $previous_month, $pre_previous_month);
?>  
  
<form  action="<?php echo PA::$url;?>/network_manage_content.php">  
  <div class="section">
  <h2><?= __("Manage Network Content") ?></h2>
  <h3><?= __("Search Posts") ?> </h3>
  <ul>
    <li>
    <input name="keyword" value="<?php echo htmlspecialchars($_GET['keyword']); ?>" type="text" size="18" />
  
    </li>
    </ul>
  <h3><?= __("Browse Months") ?> </h3>
  <ul>
    <li>
      <select name="select_month">
        <?  for ( $i = 0; $i < count($array_month); $i++ ) {              
             if ($_GET['select_month'] == $array_month[$i]) {
                echo "<option value=\"".$array_month[$i]."\" selected >".date( 'M Y',$array_month[$i] ).'</option>'; 
             }
             else {
                echo "<option value=\"".$array_month[$i]."\">".date( 'M Y',$array_month[$i] ).'</option>';  
             }             
           }
        ?>
      </select>
        <input name="search" type="submit" id="search" value="<?= __("Search") ?>" />
    </li>    
  </ul>
  </div>
</form>