<?php // global var $_base_url has been removed - please, use PA::$url static variable

$links = $links['group_info'];
$cnt = count($links);
if ($cnt > 0) { ?>
<div id="GroupsDirectoryModule">
  <div class="group_list">
    <table cellspacing="0" cellpadding="0">
     <?php for ($i=0; $i<$cnt; $i++) { 
             $pic = $links[$i]['picture']; ?>         
       <tr>   
         <td align="center" valign="top" width="80">
           <a href="<?php echo PA::$url . PA_ROUTE_GROUP . '/gid='.$links[$i]['group_id'];?>"><?= uihelper_resize_mk_img($pic, 60, 55, "images/default_group.gif", 'alt="group image"', RESIZE_CROP) ?></a>
         </td>
              
         <td valign="top" width="415">
           <h2><a href="<?php echo PA::$url . PA_ROUTE_GROUP . '/gid='.$links[$i]['group_id'];?>"><?php echo $links[$i]['title'];?></a></h2>
               
         <?php echo stripslashes($links[$i]['description']); ?>
                
         <div class="post_info">
           <?php echo uihelper_plural($links[$i]['members'], ' Member');?> | Created <?=date("F d, Y ", $links[$i]['created']);?>  | Moderated By <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/'. $links[$i]['owner_id'] ?>"><?php echo $links[$i]['owner_login_name']?></a>
         </div>
        </td>
                  
       </tr>
      <? } ?>
    </table>
  </div>
</div>
<? } ?>