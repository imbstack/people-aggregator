<?php
  $other_args = (!empty($request_data['uid'])) ? '&uid='.$request_data['uid'] : NULL;
  $other_args .= (!empty($request_data['keyword']) && !empty($request_data['name_string'])) ? '&keyword='.$request_data['keyword'].'&name_string='.$request_data['name_string'] : NULL;
   $active = ' class="active"';
?>
<ul id="filters">
  <li <?php echo (empty($request_data['sort_by']) || ($request_data['sort_by'] == 'alphabetic')) ? $active:'' ;?> ><a href="<?php echo PA::$url .'/'.FILE_NETWORKS_HOME.'?sort_by=alphabetic'.$other_args?>"><?= __("Alphabetical") ?></a></li>
  <li <?php echo (!empty($request_data['sort_by']) && $request_data['sort_by'] == 'members') ? $active : '' ;?>><a href="<?php echo PA::$url .'/'.FILE_NETWORKS_HOME.'?sort_by=members'.$other_args?>"><?= __("Size") ?></a></li>
  <li <?php echo (!empty($request_data['sort_by']) && $request_data['sort_by'] == 'created') ? $active : '' ;?>><a href="<?php echo PA::$url .'/'.FILE_NETWORKS_HOME.'?sort_by=created'.$other_args?>"><?= __("Date Created") ?></a></li>
</ul>

<h1><?= __("Network Directory") ?></h1>
<div class="description"><?= _n(";There are %d networks
0;There are no networks
1;There is one network", $total) ?></div>

<form name="networkSearch" action="<?php echo PA::$url."/".FILE_DYNAMIC?>?page_id=<?=PAGE_NETWORKS_HOME?>&action=NetworkSearch" method="post" onsubmit="return validate_form();">

  <fieldset class="center_box">
    <legend><?= __("Search Networks") ?></legend>
    <div class="field" >
    <?= __("Search for") ?>:<input type="text" value ="<?php echo !empty($request_data['keyword']) ? stripslashes($request_data['keyword']) : '' ;?>" name="keyword"/><select class="select-txt" name="name_string">
        <?  foreach ($search_str as $search_option ) {              
             if (!empty($request_data['name_string']) && $request_data['name_string'] == $search_option['value']) {
                echo "<option value=\"".$search_option['value']."\" selected >".$search_option['caption'].'</option>'; 
             }
             else {
                echo "<option value=\"".$search_option['value']."\">".$search_option['caption'].'</option>';  
             }             
           }
        ?>
      </select>
    <input type = "image" src="<?echo PA::$theme_url;?>/images/go-btn.gif" />
    </div>
  </fieldset>  
</form>


<div id="GroupsDirectoryModule">
  <?php if(!empty($page_links)) { ?>
   <div class="prev_next">
     <?php if (!empty($page_first)) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if (!empty($page_last)) { echo $page_last;}?>
   </div>
  <?php }// Page Links are Modified ?>
  
  <?php if (!empty($links)) { ?>
      <div class="group_list">
        <table cellspacing="0" cellpadding="0">
        <?  foreach ($links as $network) {    
              $extra = unserialize($network->extra)  ;
              $network_image_name = $network->inner_logo_image; 
              ?>           
            <tr>
              
              <td align="center" valign="top" width="80">
                <a href="http://<? echo $network->address .'.' . PA::$domain_suffix.BASE_URL_REL . PA_ROUTE_HOME_PAGE?>" ><?= uihelper_resize_mk_img($network_image_name, 70, 60, PA::$theme_rel."/images/default-network-image.gif") ?> </a>
              </td>
              
              <?php $network_owner_name = chop_string($owner_info[$network->network_id]['name'], 25);?>
              
              <td valign="top" width="415">
                <h2><a href="http://<? echo $network->address .'.' . PA::$domain_suffix.BASE_URL_REL . PA_ROUTE_HOME_PAGE?>"><?php echo strip_tags(stripslashes($network->network_name));?></a></h2>

               <?php  echo wordwrap($network->description,75," ",1); ?>
                
                <div class="post_info">
                 <?php echo uihelper_plural($network->member_count, ' Member');?> | Created <?=date("F d, Y ", $network->created);?>  | Moderated By <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $network->owner_id ?>"><?php echo $network_owner_name;?></a>
                </div>
              </td>
             
             <?php if(isset(PA::$login_uid) && ($network->owner_id == PA::$login_uid)) {?>
               <td align="center" valign="top">
                  <div class="buttonbar">
                    <ul>
                      <li>
                        <a href="http://<? echo $network->address .'.' . PA::$domain_suffix . BASE_URL_REL . PA_ROUTE_CONFIGURE_NETWORK?>">
                        Edit
                        </a>
                      </li>
                    </ul>
                  </div>
               </td>
           <? } ?>
           <?php  $button_status = TRUE;
                if(!empty($users_network)) {
                  if(in_array($network->network_id,$users_network))
                  $button_status = FALSE;
                }
                if ($button_status == TRUE) { ?>
                 <td align="center" valign="top" >
                 <div class="buttonbar">
                   <ul>
                     <li> 
                       <a href="http://<? echo $network->address .'.' . PA::$domain_suffix.BASE_URL_REL.'/'.FILE_NETWORK_ACTION?>?action=join&amp;nid=<?php echo $network->network_id;?>">Join</a>
                     </li>
                   </ul>    
                 </div>
                 </td>
               <? } else if (!(isset(PA::$login_uid) && ($network->owner_id == PA::$login_uid))){
                //ToDo: Need to implement the module handler for action=leave in the module
                ?>
                 <td align="center" valign="top" >
                 <div class="buttonbar">
                   <ul>
                     <li> 
                       <a href="http://<? echo $network->address .'.' . PA::$domain_suffix.BASE_URL_REL.'/'.FILE_NETWORK_ACTION?>?action=leave&amp;nid=<?php echo $network->network_id;?>">Unjoin</a>
                     </li>
                   </ul>    
                 </div>
                 </td>
               <?php } ?>
       </tr>
            
      <? } ?>
      </table>
    </div>
    <?  } else { ?>      
          <?= __("No networks found") ?>
    <? } ?>
  <?php if(!empty($page_links)) {?>
   <div class="prev_next">
     <?php if (!empty($page_first)) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if (!empty($page_last)) { echo $page_last;}?>
   </div>
  <?php } ?>
</div>