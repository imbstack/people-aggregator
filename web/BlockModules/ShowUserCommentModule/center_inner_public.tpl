<?php global  $login_uid;?>

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
          $pic = $links[$i]['picture'];?>         
            <tr>
              
              <td align="center" valign="top" width="80">
                <a href="<?php echo $links[$i]['hyper_link'];?>"><?= uihelper_resize_mk_img($pic, 60, 55, DEFAULT_USER_PHOTO_REL, 'alt="sender"', RESIZE_CROP) ?></a>
              </td>
              
              <td valign="top" width="415">
              <b><a href="<?php echo $links[$i]['hyper_link'];?>"><?php echo $links[$i]['user_name'];?> said:</a><br />
              

               <?php echo stripslashes($links[$i]['comment']); ?>
                
                <div class="post_info">
                   <?php 
                     if(!empty($links[$i]['delete_link'])) { 
                     ?>
                     <div id="buttonbar">
                       <ul>
                           <li>
                             <a href="<?php echo $links[$i]['delete_link'];?>"><?= __("Delete") ?></a>
                           </li>
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
    <?  } ?>      
    <?php if(PA::$login_uid && (PA::$login_uid <> PA::$page_uid)) : ?>
    <div class="inplace_edit_testimonial" id="new_testimonial" style="padding: 12px">
      <?=__('Click here to submit a Testimonial') ?>
      <input type="hidden" name="submit_url" id="submit_url" value="/user/<?= PA::$page_uid ?>" />
    </div>
    <?php endif; ?>
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php } ?>
</div> 