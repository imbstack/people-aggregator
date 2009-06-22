<?php
  include "navigation.tpl";
?>

<h1 class="accent_1">Contacts</h1>

<div class="tabbed_page_<?= $active_tab ?>">
  <ul id="tabbed_menu">
    <li id="nav-1">
      <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts&stype=import">Import</a>
    </li>
    <li id="nav-2">
      <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts&stype=plaxo">Plaxo Contacts</a>
    </li>
    <li id="nav-3">
      <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts&stype=mslive">MS Live Contacts</a>
    </li>
    <li id="nav-4">
      <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts&stype=linkedin">LinkedIn Contacts</a>
    </li>
    <li id="nav-5">
      <a href="<?=PA::$url.PA_ROUTE_USER_CONTACTS?>?type=contacts&stype=outlook">Outlook Contacts</a>
    </li>
   </ul>
  <div class="tabbed_page_holder" style="overflow: visible; ">
    <div class="clear"></div>
    <div id="contacts_holder">
      <?php if(!empty($stype)) : ?>
        <div class="import_edit" style="position:relative">
          <?php include "{$stype}_inner.tpl" ?>
        </div>
      <?php else: ?>
        <?php echo __('Unkonwn type selected') ?>
      <?php endif; ?>
      <div class="clear"></div>
    </div>
  </div>
</div>
