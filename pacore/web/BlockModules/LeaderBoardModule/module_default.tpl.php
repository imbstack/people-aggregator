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
  <div class="module_icon_list" id="list_members">
    <ul class="members">
      <?php
        for($counter = 0; $counter < count($users_ranking); $counter++) {
    $class = (($counter%2) == 0) ? 'class="color"' : NULL;
    ?>  
      
      <li <?php echo $class?>>
        <?=link_to(uihelper_resize_mk_user_img($users_ranking[$counter]->picture, 35, 35, 'alt="PA"'), "user_blog", array("login" => $users_ranking[$counter]->login_name))?>
        <span>
          <b><?=link_to(abbreviate_text($users_ranking[$counter]->display_name, 18, 10), "user_blog", array("login" => $users_ranking[$counter]->login_name))?></b>
          &nbsp;(<?=_n(";%d points\n1;one point\n0;no points", $users_ranking[$counter]->ranking_points)?>)
        </span>
      </li>
      <?php
}
?>          
    </ul>         
  </div>
