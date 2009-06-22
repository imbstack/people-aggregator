<?php
  //  echo "<pre>" . print_r($items,1) . "</pre>";
?>

<?php include "filters.tpl.php" ?>

<h1><?= $sub_title ?></h1>

<div id="PointsDirectoryModule">
  <?php if(!empty($page_links)) : ?>
   <div class="prev_next">
     <?php if ($page_first) {echo $page_first;} ?>
     <?php echo $page_links?>
     <?php if ($page_last) {echo $page_last;} ?>
   </div>
  <?php endif;  ?>
  <?php if($edit_perm) : ?>
      <a class="button_silver" href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?action=newPoints&module=PointsDirectoryModule&uid={$_REQUEST['uid']}" ?>"><?= __("New") ?></a>
      <br /><br />
  <?php endif; ?>
  <?php if(count($items) > 0) : ?>
    <ul class="points_list">
    <?php foreach($items as $item) : ?>
    <li>
      <div><?= $item['media_icon'] ?></div>
      <div class="points_center">
        <h2><?= abbreviate_text($item['entity_name'], 17, 10) ?></h2>
        <div class="points_descr"><?= $item['description'] ?></div>
      </div>
      <div class="points_details">
        <table>
          <tr><td class="categ"><?= abbreviate_text($item['category'], 7, 3) ?></td></tr>
          <tr><td><b><?= __("Date: ") ?></b><?= PA::date($item['updated'], 'short') ?></td></tr>
          <tr><td><?= abbreviate_text($item['place'], 20, 13) ?></td></tr>
        </table>
      </div>
      <div class="points_user">
        <?= uihelper_resize_mk_img($item['user']->picture, 64, 64, 'images/default.png', 'style=""'); ?>
        <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/{$item['user']->user_id}"?>" alt="<?= $item['user']->display_name ?>"><?= abbreviate_text($item['user']->display_name, 12, 7) ?></a>
      </div>
      <div class="points_rating">
        <p><?= $item['rating'] ?></p>
        <?= __("Points") ?>
      </div>
    </li>
    <div class="points_buttons">
     <?php if($edit_perm) : ?>
      <a href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?action=editPoints&module=PointsDirectoryModule&eid={$item['entity_id']}&uid=$user_id" ?>"><?= __("Edit") ?></a> |
      <a href="<?= PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?action=deletePoints&module=PointsDirectoryModule&eid={$item['entity_id']}&uid=$user_id" ?>"><?= __("Delete") ?></a>
     <?php endif; ?>
    </div>
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
