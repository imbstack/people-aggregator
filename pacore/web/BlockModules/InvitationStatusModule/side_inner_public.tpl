<?php
?>
<form>
<fieldset>
  <legend><?= __("Accepted invitations") ?></legend>
    <ul>
    <?php if (!empty($accepted_invitation)) {?>
      <?php  for ($i=0; $i<count($accepted_invitation); $i++) { ?>        
         <li> 
            <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $accepted_invitation[$i]['user_id'] ?>">
             <?php echo $accepted_invitation[$i]['user_name'].'-'; ?>
             <?php print $accepted_invitation[$i]['first_name'].'-'; ?>
             <?php print $accepted_invitation[$i]['last_name']; ?>
            </li> 
            </a>
      <?php } } else { ?> <li> <?php echo __("No Accepted Invitations") ?> </li><?php } ?>  
   </ul>     
</fieldset>
      
<fieldset>
  <legend><?= __("Pending invitations") ?></legend>
  <ul>
  <?php if (!empty($pending_invitation)) { ?>
   <?php for ($i=0; $i<count($pending_invitation); $i++) { ?>
   <li>  
     <a href="mailto:<?=$pending_invitation[$i]['user_email'];?>">
        <?php print $pending_invitation[$i]['user_email']; ?></a>
   </li>
   <?php } } else { ?>
    <li> <?= __("No pending Invitations") ?> </li>
  <?php } ?>
  </ul>
</fieldset>
</form>