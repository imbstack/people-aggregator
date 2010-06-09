<?php 
  global  $login_uid;
?>  
<ul id="thumbwrap">
<?php
  if( !empty( $page_links ) ) {
?>
<li>
  <ul id="nav_prev-next">          
    <?php echo $page_links; ?>
  </ul>
</li>
<?php
  }
?> 
</ul>
<div id="mod_view_member">
<?php
  if ($in_relations) {
    $description = sprintf(__("There are %d members in %s."), count($links), $sub_title);
  } else if ($relations) {
    $description = sprintf(__("There are %d friends."), count($links));
  } 
    if (!empty($links)) { ?>
    <div class='description'><?php echo $description;?></div>
  <?php
    for( $counter = 0; $counter < count( $links ); $counter++ ) {
      $user_link = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$counter]['user_id'];
  ?>
  <table width="592px" cellspacing="0" cellpadding="0">    
    <tr>
      <td width="10%" rowspan="3"><a href="<?php echo $user_link;?>"><?php echo uihelper_resize_mk_user_img($links[$counter]['picture'], 50, 50, 'alt="User picture."') ?></a></td>
      <td width="50%">
        <ul>
          <li><a href="<?php echo $user_link;?>"><?php echo $links[$counter]['display_name'];?></a></li>
          <li><?php echo chop_string($links[$counter]['first_name'].' '.$links[$counter]['last_name'], 50);?></li>
          <li>member since: <?php echo manage_content_date ($links[$counter]['created']); ?></li>
        </ul>     
      </td>
      <?php 
        if ($_GET['view_type'] == 'relations' || $_GET['view_type'] == 'in_relations') {
          switch( (int)$links[$counter]['relation_type_id'] ) {
            case 1:
              $relation_type = 'Acquaintance';
            break;
            case 2:
              $relation_type = 'Friend';
            break;
            case 3:
              $relation_type = 'Good Friend';
            break;
            case 4:
              $relation_type = 'Best Friend';
            break;
            case 5:
              $relation_type = "Haven't Met";
            break;
          }       
      ?>
        <td width="10%"><?php echo $relation_type; ?></td>
        <?php if ($reciprocated_relationship_set == TRUE && $_GET['uid'] == $login_uid) {
          if ($relations && $links[$counter]['status'] == 'pending') {?>
            <td width="10%"><?php echo $links[$counter]['status']; ?></td>
          <?php } else { ?>
            <td width="10%"></td>
          <?php } 
           if ($in_relations &&
                   $links[$counter]['status'] == PENDING) { ?>
     <form method = "POST" action="">
       <input type="hidden" name="related_id" 
       value="<?php echo $links[$counter]['user_id']; ?>" />
       <input type="hidden" name="related_email" 
       value="<?php echo $links[$counter]['email']; ?>" />
       <div id="button_bar">
         <td width="10%">
         <input type="submit" value="Approve"  name="btn_approve" />
         </td>
         <td width="10%">
         <input type="submit" value="Deny"  name="btn_deny" />
         </td> 
       </div>     
     </form>
          <?php } else { ?>
          <td width="20%"></td>
          <?php } ?>
        <?php } ?>
      <?php } ?>
    </tr>    
  </table>
  <?php
      } // end for $links
  ?>  

<?php
  }
  else {
    echo 'No relation exists';
  }
?>
</div>