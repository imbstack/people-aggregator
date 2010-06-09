<h1><?= __("Delete Account") ?></h1>
<?php 
global $network_info;
if($network_info->type != MOTHER_NETWORK_TYPE) {
echo "<center><h2 style=\"width:700px\">".__("You are not allowed to delete your account here. You can delete your account from meta organization only.")."</h2>";
} else {
?>

    <h4> <center><div  style="width:700px"><?= __("If you delete your account, all your content posted and your membership in other networks will be deleted. Your complete information will be deleted from the network.") ?><br/><br />
 <?= __("To delete your account you have to check this box.") ?></div>

<form enctype="multipart/form-data" action="<?php echo PA::$url?>/delete_account.php" method="post">
<input type="checkbox" name="Delete" value="Delete">
    <div class="button_position">
      <input type="submit" name="submit" value="<?= __("Delete My Account") ?>" />
    </div>
</form></center></h4>
<?php } ?>
