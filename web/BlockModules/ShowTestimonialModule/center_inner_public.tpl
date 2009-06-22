<?php 
  global  $login_uid;
  
?>
<div id="GroupsDirectoryModule">
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }?>
  
  <?php  $cnt = count($links);
  if (  $cnt > 0) { ?>
  
      <div class="group_list">
        <table cellspacing="0" cellpadding="0">
        <? for ($i=0; $i < $cnt; $i++) {
          $pic = $links[$i]['user_pic']; 
          $txt = ($mode == PRI) ? ucfirst($links[$i]['username']).' has written Testimonial for you': ucfirst($links[$i]['username']).' :';
          ?>         
            <tr>
              
              <td align="center" valign="top" width="80">
                <a href="<?php echo $links[$i]['user_url'];?>"><?= uihelper_resize_mk_img($pic, 60, 55, "images/default_group.gif", 'alt="sender"', RESIZE_CROP) ?></a>
              </td>
              
              <td valign="top" width="415">
              <b><?php echo $txt;?><br />
              

               <?php echo stripslashes($links[$i]['body']); ?>
                
                <div class="post_info">
                   <?php 
                     if(!empty($links[$i]['button'])) { $counter = count($links[$i]['button']);
                     $button = $links[$i]['button'];
                     ?>
                     <div id="buttonbar">
                       <ul>
                         <?php for($j = 0; $j < $counter; $j++) { ?>
                           <li>
                             <a href="<?php echo $button[$j]['link'];?>"><?php echo $button[$j]['caption'];?></a>
                           </li>
                        <? } ?>
                       </ul>
                     </div>  
                    <? }
                   ?>
 
                 </div>
              </td>
                   
               <td align="center" valign="top">
               </td>
       </tr>
            
      <? } ?>
      </table>
    </div>
    <?  } else { ?>      
           <?= __("No Testimonial Found") ?>
    <? } ?>
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php } ?>
</div> 