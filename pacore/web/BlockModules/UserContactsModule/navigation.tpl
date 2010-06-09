<?php
  error_reporting(E_ALL);
   

  function isActiveNav($section, $type) {
    echo ($section == $type) ? ' class="active"' : NULL;
  }
?>

<ul id="filters">
  <li<?php isActiveNav('basic', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=basic" id="show-basic"><?= __("Basic Info") ?></a>
  </li>
  <li<?php isActiveNav('general', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=general" id="show-general"><?= __("General Info") ?></a>
  </li>
  <li<?php isActiveNav('personal', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=personal" id="show-personal"><?= __("Personal Info") ?></a>
  </li>
  <li<?php isActiveNav('professional', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=professional" id="show-professional"><?= __("Professional Info") ?></a>
  </li>

  <li <?php isActiveNav('notifications', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=notifications" id="show-notifications"><?= __("Notifications") ?></a>
  </li>

  <li <?php isActiveNav('contacts', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts" id="show-contacts"><?= __("Contacts") ?></a>
  </li>

  <?php
    if (empty(PA::$config->simple['omit_advacedprofile'])) {
  ?>

  <li <?php isActiveNav('blogs_rss', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=blogs_rss" id="show-blogs_rss"><?= __("Blogs/RSS") ?></a>
  </li>
  <li <?php isActiveNav('notifications', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=notifications" id="show-notifications"><?= __("Notifications") ?></a>
  </li>
  <? // import module, added by Zoran Hron ?>
  <li<?php isActiveNav('import', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=import" id="show-import"><?= __("Import") ?></a>
  </li>

  <?php
    }
  ?>
  <li<?php isActiveNav('delete_account', $type) ?>>
    <a href="<?=PA::$url.PA_ROUTE_EDIT_PROFILE?>?type=delete_account" id="show-delete_account"><?= __("Delete Account") ?></a>
  </li>
</ul>
