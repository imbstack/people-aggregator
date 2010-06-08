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
  <?php if(!empty($pagination_links)) { ?>
  <div class="ranking_pagination">
    <?= __("Pages: ") . $pagination_links ?>
  </div>
  <?php } ?>
  <?php if(count($users_ranking) > 0)  : ?>
  <div class="activities_ranking_container">
    <table class="activities_ranking_list">
      <thead>
        <tr>
          <th class="ranking_position"><?= __("No.") ?></th>
          <th class="ranking_image"></th>
          <th class="ranking_name"><?= __("User Name") ?></th>
          <th class="ranking_date"><?= __("Last Activity Date") ?></th>
          <th class="ranking_points"><?= __("Ranking points") ?></th>
          <th class="ranking_stars"><?= __("Ranking stars") ?></th>
        </tr>
      </thead>
      <tbody>
      <?php
       $i = 0; // echo '<pre>'.print_r($users_ranking,1).'</pre>';
       foreach($users_ranking as $ranked_user) { $even_odd = (++$i % 2) ? "even" : "odd"; ?>
        <tr class="activities_ranking_row activities_ranking_<?=$even_odd?>">
          <td class="ranking_position">
            <?= $i + $increment ?>
          </td>
          <td class="ranking_image">
            <?= "<a href=\"". PA::$url . PA_ROUTE_USER_PUBLIC . '/' . "$ranked_user->user_id\">".uihelper_resize_mk_user_img($ranked_user->picture, 35, 35, "alt=\"$ranked_user->display_name\"")."</a>"; ?>
          </td>
          <td class="ranking_name">
            <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $ranked_user->user_id ?>"><?= $ranked_user->display_name ?></a>
          </td>
          <td class="ranking_date">
            <?= (!empty($ranked_user->last_activity)) ? PA::datetime($ranked_user->last_activity, 'long', 'short') /* date("Y-m-d H:i:s", $ranked_user->last_activity) */ : __("unknown") ?>
          </td>
          <td class="ranking_points">
            <?= $ranked_user->ranking_points ?>
          </td>
          <td class="ranking_stars">
            <img src="<?= PA::$theme_url . '/images/'.$ranked_user->ranking_stars.'_star.gif' ?>" alt="star" />
          </td>
        </tr>
      </tbody>
      <?php } ?>
    </table>
  </div>
  <?php else : ?>
    <div class="activities_ranking_bigtext">
      <?= __("There's no ranked users.") ?>
    </div>
  <?php endif; ?>
