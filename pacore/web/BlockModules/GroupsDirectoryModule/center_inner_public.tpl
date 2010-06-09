<?php 
  $other_args = (!empty($_GET['uid'])) ? '&uid='.$_GET['uid'] : NULL;
  $other_args .= (!empty($_GET['keyword'])) ? '&keyword='.$_GET['keyword'].'&name_string='.$_GET['name_string'] : NULL;
  $active = ' class="active"';
?>

<ul id="filters">
  <li <?= (empty($_GET['sort_by']) || ($_GET['sort_by'] == 'alphabetic')) ? $active:'' ;?> ><a href="<?= PA::$url . PA_ROUTE_GROUPS .'/sort_by=alphabetic'.$other_args?>"><?= __("Alphabetical") ?></a></li>
  <li <?= (@$_GET['sort_by'] == 'members') ? $active:'' ;?>><a href="<?= PA::$url . PA_ROUTE_GROUPS . '/sort_by=members'.$other_args?>"><?= __("Largest") ?></a></li>
  <li <?= (@$_GET['sort_by'] == 'created') ? $active:'' ;?>><a href="<?= PA::$url . PA_ROUTE_GROUPS . '/sort_by=created'.$other_args?>"><?= __("Date Created") ?></a></li>
</ul>
    
<h1><?= __("Group Directory") ?></h1>
<div class="description"><?= sprintf(__("There are %d groups"), $total);?> </div>
<div id="buttonbar">
  <ul>
    <li><a href="<?= PA::$url;?>/addgroup.php"><?= __("Create a Group") ?></a></li>
  </ul>
</div>

<form action="<?= PA::$url . PA_ROUTE_GROUPS ?>" method="get">
  <fieldset >
    <legend><?= __("Search Groups") ?></legend>
    <div class="field">
    <?= __("Search for") ?>: <input type="text" value ="<?= stripslashes(@$_GET['keyword']);?>" name="keyword"/>
      <select class="select-txt" name="name_string">
        <?php 
          foreach ( $search_str as $search_option ) {
             if (@$_GET['name_string'] == $search_option['value']) {
                echo "<option value=\"".$search_option['value']."\" selected >".$search_option['caption'].'</option>'; 
             }
             else {
                echo "<option value=\"".$search_option['value']."\">".$search_option['caption'].'</option>';  
             }             
           }
        ?>
      </select>
     <?php if (!empty($_GET['uid'])) {?>
      <input type='hidden' name="uid" value="<?= $_GET['uid']?>">  
    <? } ?>
    <input type = "image" style="position:relative; top:6px" src="<?echo PA::$theme_url;?>/images/go-btn.gif" />

    </div>

  </fieldset>  
</form>


<div id="GroupsDirectoryModule">
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?= $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }// Page Links are Modified ?>
  
  <?php  $cnt = count($links);
  if (  $cnt > 0) { ?>
      <div class="group_list">
        <table cellspacing="0" cellpadding="0">
        <? for ($i=0; $i<$cnt; $i++) {
          $pic = $links[$i]['picture']; 
          // make sure we have the actual OWNER (not author_id, which is Creator)
          $links[$i]['owner_id'] = Group::get_owner_id((int)$links[$i]['group_id']);
          ?>         
            <tr>
              
              <td align="center" valign="top" width="80">
                <a href="<?= PA::$url . PA_ROUTE_GROUP . '/gid='.$links[$i]['group_id'];?>"><?= uihelper_resize_mk_img($pic, 60, 55, "images/default_group.gif", 'alt="group image"', RESIZE_CROP) ?></a>
              </td>
              
              <td valign="top" width="415">
                <h2><a href="<?=
                PA::$url . PA_ROUTE_GROUP . '/gid='.$links[$i]['group_id'];?>"><?= chop_string($links[$i]['title'],32);?></a></h2>

               <?= stripslashes($links[$i]['description']); ?>
                
                <div class="post_info">
                 <?= sprintf(__('%d Members'), $links[$i]['members']);
                 $o = new User();
                 try {
	                 $o->load((int)$links[$i]['owner_id']);
                 } catch (PAException $e) {
                 	Logger::log(__FILE__.": ".$e->getMessage(), 32);
                 } ?> 
                 | <?= __("Created") ?> <?= PA::date($links[$i]['created'], 'short'); ?>
                 | <?= __("Moderated By") ?> <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['owner_id'] ?>"><?= @$o->display_name ?></a>
                </div>
              </td>
                   
             <?php  $group_action = group_user_authentication($links[$i]['group_id']);?>
               <td align="center" valign="top">
                  <div id="buttonbar" <?= $group_action['style'];?>>
                    <ul>
                      <li>
                        <a href="<?= $group_action['hyper_link']?>"><?= __($group_action['caption'])?></a>
                      </li>
                    </ul>
                  </div>
               </td>
       </tr>
            
      <? } ?>
      </table>
    </div>
    <?  } else { ?>      
           <?= __("No groups found.") ?>
    <? } ?>
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?= $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php } ?>
</div>  
