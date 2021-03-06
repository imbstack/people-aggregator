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
<?php
  //  echo "<pre>" . print_r($items,1) . "</pre>";
?>

<?php include "filters.tpl.php" ?>
<?php echo "" //"<pre>" . print_r($items, 1) . "</pre>"; ?>
<h1><?= $sub_title ?></h1>
<div id="PointsDirectoryModule">
<div class="points_list">
  <?php if($edit_perm) : ?>
    <?php if(!empty($_REQUEST['uid'])) : ?>
      <a class="button_silver" href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?faction=newPoints&module=PointsDirectoryModule&uid={$_REQUEST['uid']}&fid=$fid" ?>"><?= __("New") ?></a>
      <br /><br />
    <?php else: ?>
       <label><?= __("Please, select a family member that you wish to assign points") ?>: </label>
       <select name="select_member" id="select_member" onchange="javascript: document.location = '<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY?>?faction=newPoints&module=PointsDirectoryModule&fid=<?=$fid?>&uid=' + this.options[this.selectedIndex].value
;">
          <option value=""><?=__("Select") ?></option>
       <?php foreach($fam_members as $fmember) : ?>
          <option value="<?=$fmember['user']->user_id?>"><?=$fmember['user']->display_name?></option>
       <?php endforeach; ?>
       </select>
    <?php endif; ?>
  <?php endif; ?>
</div>
  <?php if(!empty($page_links)) : ?>
   <div class="prev_next">
     <?php if ($page_first) {echo $page_first;} ?>
     <?php echo $page_links?>
     <?php if ($page_last) {echo $page_last;} ?>
   </div>
  <?php endif;  ?>
  <?php if(count($items) > 0) : ?>
    <ul class="points_list">
    <?php foreach($items as $item) : /*echo "<pre>" . print_r($item, 1) . "</pre>"; */?>
    <li class="points_item">
      <div><?= $item['media_icon'] ?></div>
      <div class="points_center">
        <h2><?= abbreviate_text($item['entity_name'], 21, 10) ?></h2>
        <div class="points_descr"><?= $item['description'] ?></div>
      </div>
      <div class="points_details">
        <table>
          <tr><td class="categ"><?= abbreviate_text($item['category'], 8, 3) ?></td></tr>
          <tr><td><b><?= __("Date: ") ?></b><?= PA::date($item['created'], 'short') ?></td></tr>
          <tr><td><?= abbreviate_text($item['place'], 20, 13) ?></td></tr>
        </table>
      </div>
      <div class="points_user">
        <?= uihelper_resize_mk_img($item['user']->picture, 64, 64, 'images/default.png', 'style=""'); ?>
        <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/{$item['user']->user_id}"?>" alt="<?= $item['user']->display_name ?>"><?= abbreviate_text($item['user']->display_name, 12, 7) ?></a>
      </div>
      <div class="points_rating">
         <?= uihelper_resize_mk_img($item['giveuser']->picture, 32, 32, 'images/default.png', 'style=""'); ?><br />
         <a style="font-weight: normal" href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/{$item['giveuser']->user_id}"?>" alt="<?= $item['giveuser']->display_name ?>"><?= abbreviate_text($item['giveuser']->display_name, 12, 7) ?></a><br />
        <p>
         <?= $item['rating'] ?>
        </p>
        <?= __("Points") ?>
      </div>
    </li>
    <li class="points_buttons">
     <?php if($edit_perm) : ?>
      <a href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?faction=editPoints&module=PointsDirectoryModule&eid={$item['entity_id']}&uid={$item['user_id']}&fid={$item['family_id']}" ?>"><?= __("Edit") ?></a> |
      <a href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?faction=deletePoints&module=PointsDirectoryModule&eid={$item['entity_id']}&uid={$item['user_id']}&fid={$item['family_id']}" ?>"><?= __("Delete") ?></a>
     <?php endif; ?>
    </li>
    <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="field">
      <?= __("No entries.") ?>
    </div>
  <?php endif; ?>
  <?php if(!empty($page_links)) : ?>
   <div class="prev_next">
     <?php if ($page_first) {echo $page_first;} ?>
     <?php echo $page_links?>
     <?php if ($page_last) {echo $page_last;} ?>
   </div>
  <?php endif;  ?>
</div>
