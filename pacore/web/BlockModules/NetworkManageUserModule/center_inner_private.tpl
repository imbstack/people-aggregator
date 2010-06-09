<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

?>
<div class="description"><?= __("In this page you can manage users registered on your network") ?></div>
<form action="<?php echo PA::$url;?>/manage_user.php" class="inputrow">
  <fieldset class="center_box">
    <legend><?= __("Search Registered Users") ?></legend>
      <input name="keyword" value="<?php echo htmlspecialchars($_GET['keyword']); ?>" type="text" size="18" />
    <input name="search" type="submit" id="search" value="Search" />
  </fieldset>
</form>