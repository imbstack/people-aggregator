<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?

$login_required = FALSE;
include dirname(__FILE__)."/../includes/page.php";

include "web/update/admin_login.php";

switch (@$_REQUEST['engine']) {
case 'gd':
  ImageResize::$skip_magick = TRUE;
  break;
case 'magick':
  ImageResize::$skip_gd = TRUE;
  break;
}

?><h1><?= __('testing ImageResize module with engine') ?> <?= ImageResize::get_engine() ?></h1>

<p><a href="test_imageresize.php">default</a> | <a href="test_imageresize.php?engine=gd">gd</a> | <a href="test_imageresize.php?engine=magick">magick</a> (<?= __('make sure to hit ctrl-F5 after clicking, to clear the cache') ?>)</p>

<p><?= __('flushing cache') ?> ...
<?php
flush();
system("rm -rf ../files/rsz");
?>... <?= __('done') ?></p>

<p>orig: <img src="../images/palogo_black_bg.jpg"></p>

<h2>stretching 135x75 to 300x300</h2>
<?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 300, 300, NULL, "", RESIZE_STRETCH) ?></p>

<h2><?= __('expanding and cropping 135x75 to fit inside 300x300') ?></h2>
<p>new: 
<?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 300, 300, NULL, "", RESIZE_CROP) ?></p>
<p><?= __('reduce only') ?>: <?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 300, 300, NULL, "", RESIZE_CROP_NO_EXPAND) ?></p>
<p>reduce only, smaller: <?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 100, 100, NULL, "", RESIZE_CROP_NO_EXPAND) ?></p>

<h2><?= __('fitting 135x75 inside 300x300') ?></h2>
<p>new: 
<?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 300, 300, NULL, "", RESIZE_FIT) ?></p>
<p><?= __('reduce only') ?>: <?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 300, 300, NULL, "", RESIZE_FIT_NO_EXPAND) ?></p>
<p><?= __('reduce only, smaller') ?>: <?= uihelper_resize_mk_img_static("images/palogo_black_bg.jpg", 100, 100, NULL, "", RESIZE_FIT_NO_EXPAND) ?></p>

