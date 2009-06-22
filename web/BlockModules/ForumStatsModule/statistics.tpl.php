<?php // global var $_base_url has been removed - please, use PA::$url static variable
?>
<div class="module_moderator_info">
 <div style="padding:8px">
  <table class="forum_statistics">
    <tr>
      <td><?=__("Type") . ":" ?></td><td><?= $statistics['type'] ?></td>
    </tr>
    <tr>
      <td><?=__("Members") . ":" ?></td><td><?= $statistics['nb_users'] ?></td>
    </tr>
    <tr>
      <td><?=__("Categories") . ":" ?></td><td><?= $statistics['nb_categories'] ?></td>
    </tr>
    <tr>
      <td><?=__("Forums") . ":" ?></td><td><?= $statistics['nb_forums'] ?></td>
    </tr>
    <tr>
      <td><?=__("Topics") . ":" ?></td><td><?= $statistics['nb_threads'] ?></td>
    </tr>
    <tr>
      <td><?=__("Posts") . ":" ?></td><td><?= $statistics['nb_posts'] ?></td>
    </tr>
    <tr>
      <td><?=__("Founded") . ":" ?></td><td><?= PA::date(strtotime($statistics['created_at']), 'short') ?></td>
    </tr>
  </table>
  <div class="box center">
  Owner:<br />
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $statistics['owner']->user_id ?>">
    <?php echo uihelper_resize_mk_img($statistics['owner']->picture, 90, 68, "images/default.png", 'alt="Picture of the forum owner."') ?></a><br />
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $statistics['owner']->user_id ?>"><?= $statistics['owner']->login_name ?></a>
  </div>
 </div>
</div>
