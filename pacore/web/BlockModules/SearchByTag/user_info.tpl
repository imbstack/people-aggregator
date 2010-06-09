<?php // global var $_base_url has been removed - please, use PA::$url static variable

$links = $links['user_info'];
$cnt = count($links);
if ($cnt > 0) { ?>
<div id="GroupsDirectoryModule">
  <div class="group_list">
    <table cellspacing="0" cellpadding="0">
     <?php for ($i=0; $i<$cnt; $i++) { 
             $pic = $links[$i]['picture']; ?>         
       <tr>   
         <td align="center" valign="top" width="80">
           <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['user_id'];?>"><?= uihelper_resize_mk_user_img($links[$i]['picture'], 35, 35, 'alt="User image"'); ?></a>
         </td>
              
         <td valign="top" width="415">
           <h2><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['user_id'];?>"><?php echo $links[$i]['login_name'];?></a></h2>
               
         <?php echo ucfirst($links[$i]['first_name']).' '.$links[$i]['last_name'] ; ?>
                
         <div class="post_info"><?php print_r(Tag::tag_array_to_html($links[$i]['tags']));?></div>
        </td>
                  
       </tr>
      <? } ?>
    </table>
  </div>
</div>
<? } ?>