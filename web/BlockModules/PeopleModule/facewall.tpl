<?php

global $current_theme_path,$aim_api_key, $aim_presence_key;
$query_string = null;
  if (!empty($_REQUEST)) {
  // code for appending the GET string in filters
    $query_string_array = $_REQUEST;
    if (isset($query_string_array['sort_by'])) {
      unset($query_string_array['sort_by']);
    }

    $query_string = null;
    foreach ($query_string_array as $key => $value) {
      if($key <> 'PHPSESSID') {
        $query_string .= '&'.$key.'='.$value;
      }
    }
    if(!empty($show_people_with_photo)) {
      $query_string .= "&show_people_with_photo=$show_people_with_photo";
    }
  }

  $style = 'style="display:none;"';
  $toggle_text = __('Advanced Search');
  if ($show_advance_search_options) {
    $style = null;
    $toggle_text = __('Simple Search');
  }


?>

<ul id="filters">
    <li<?php echo (empty($_REQUEST['sort_by']) || (!empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'recent_users') ) ? ' class="active"' : '';?>><a href="<?php echo PA::$url . PA_ROUTE_PEOPLES_PAGE;?>/sort_by=recent_users<?php echo htmlspecialchars($query_string) ?>"><?= __("Recent Users") ?></a></li>
    <li<?php echo (!empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'alphabetic') ? ' class="active"' : '';?>><a href="<?php echo PA::$url . PA_ROUTE_PEOPLES_PAGE;?>/sort_by=alphabetic<?php echo htmlspecialchars($query_string) ?>"><?= __("Alphabetical") ?></a></li>
</ul>

<h1><?= __(PA::$people_noun) ?></h1>
<?php
  if (!empty($search_data)) {
    $msg = _n(";%d members found
1;One member found
0;No members found", $people_count) . (($only_with_photo) ? __(' with picture in profile') :'');
  } else {
    $msg = _n(";This network has %d members
1;This network has only one member
0;This network has no members", $people_count) . (($only_with_photo) ? __(' with picture in profile') :'');
  }
?>

<div class="description"><?php echo $msg?></div>
<div id="PeopleModule">


  <?php if( $page_links ) { ?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }  ?>
  <div style="padding-left: 18px; clear: both; float: left; width:540px">
  <?php foreach($links as $link) {
   include "_buddy.tpl.php";
  } ?>
  </div>

  <?php if( $page_links ) {?>
   <div class="prev_next" id="page_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php }  ?>

  <div class="search_gallery">
  <?php if ($people_count) { ?>


   <select id="rows" name="rows" onchange="javascript: show_rows('<?php echo PA::$url . PA_ROUTE_PEOPLES_PAGE?>');">
    <option  value="100"><?= __("Select rows") ?></option>
    <?php
      for($i=1; $i <=$row_count; $i++) {  ?>
        <option value="<?php echo $i ?>" <?php if (@$_REQUEST['rows'] == $i) {echo 'selected="selected"'; }?>><?php echo $i ?></option>
    <?php
       }
    ?>
    </select>
  <?php } ?>
  </div>
  <?php
    if (isset($aim_api_key))
      { ?>
    <div id="AIMBuddyListContainer" wim_key="<?php echo $aim_api_key ?>" class="hand" >
	    <a onclick="{AIM.widgets.buddyList.launch();return false;}"
	href="nojavascript.html"><img alt="AIM" border="1" src="<?php echo PA::$url ."/images/aim-online.png"?>">AIM Buddies</a>
	</div>
     <?php
	  }
      ?>

</script>
<form name="myform_show_mode" action="" method="get">
  <div style="margin-right:10px; width: 120px; float:right; text-align:right">
    no photo OK <input type="checkbox" name="no_photo_ok_chbox" id="chbox_no_photo_ok" value="1"
    <?php if ($no_photo_ok) { echo 'checked="checked"'; }?> />
  </div>
  <input type="hidden" name="no_photo_ok" id="no_photo_ok_switch" value="<?=$no_photo_ok?>" />
</form>

</div>

<form name="myform_search" action="" method="get">
  <input type="hidden" name="no_photo_ok" id="no_photo_ok_search" value="<?=$no_photo_ok?>" />

<fieldset class="center_box">
    <legend><?= __("Search") ?></legend>
<script type="text/javascript">
var _names_cleared = false;
function name_focus(el) {
  if (_names_cleared) return;
  document.getElementById("allnames").value = document.getElementById("last_name").value = "";
  _names_cleared = true;
}
</script>

    <table cellpadding="0" cellspacing="0" class="search_user">
      <tr>
        <td width="150">
        <input id="allnames" type="text" name="allnames" class="text normal" <?php
        if (!empty($_REQUEST['allnames']) || !empty($_REQUEST['allnames']) || $show_advance_search_options) {
          ?>value="<?php echo htmlspecialchars(@$_REQUEST['allnames']) ?>"<?php
        } else {
          ?>value="<?= __("First name") ?>" onfocus='name_focus()'<?php
        }?> /></td>
        <td width="150">
        <input id="last_name" type="text" name="last_name" class="text normal" <?php
        if (!empty($_GET['first_name']) || !empty($_GET['last_name']) || $show_advance_search_options) {
          ?>value="<?php echo htmlspecialchars(@$_GET['last_name']) ?>"<?php
        } else {
          ?>value="<?= __("Last name") ?>" onfocus='name_focus()'<?
        } ?> /></td>
        <td width="250" id="buttonbar">
          <ul>
          <li><a href="javascript: document.forms['myform_search'].submit();"><?= __("Find Users") ?></a></li>
          <li id="button_text"><a href="#" id="toggle_text"><?php echo $toggle_text?></a></li>
        </ul>
      </td>
      </tr>
    </table>

    <div id="advance_search_options"<?php echo $style?>>
      <div class="field">
        <h4><label><?= __("Gender") ?>:</label></h4>
        <?php
          $checked_female = $checked_male = $checked_all = null;
          if (!empty($_REQUEST['sex'])) {
            if ($_REQUEST['sex'] == "Male") {
              $checked_male = "checked=\"checked\"";
            } else if ($_REQUEST['sex'] == "Female") {
              $checked_female = "checked=\"checked\"";
            }
          } else {
            $checked_all = "checked=\"checked\"";
          }

        ?>
        <input type="radio" id="male" name="sex" value="Male" <?=$checked_male?> /> <?= __("Male") ?>
        <input type="radio" id="female" name="sex" value="Female" <?=$checked_female?> /> <?= __("Female") ?>
        <input type="radio" id="all" name="sex" value="" <?=$checked_all?> /> <?= __("Any") ?>
        <?php echo get_age_options('age', field_value(@$_GET['age'], null));?>
      </div>
      <div class="field">
        <h4><label for="city"><?= __("City") ?>:</label></h4>
        <input type="text" name="city" id="city" value="<?=htmlspecialchars(field_value(@$_REQUEST['city'], '')) ?>" class="text longer" />
      </div>

      <div class="field">
        <h4><label for="state"><?= __("State") ?>:</label></h4>
        <input type="text" name="state" id="state" value="<?=htmlspecialchars(field_value(@$_REQUEST['state'], '')) ?>" class="text longer" />
      </div>

      <div class="field">
        <h4><label for="company"><?= __("Company Name") ?>:</label></h4>
        <input type="text" name="company" id="company" value="<?=htmlspecialchars(field_value(@$_REQUEST['company'], '')) ?>" class="text longer" />
      </div>

      <div class="field">
        <h4><label for="user_tags"><?= __("Interests") ?>:</label></h4>
        <input type="text" name="user_tags" id="user_tags" value="<?=htmlspecialchars(field_value(@$_REQUEST['user_tags'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="industry"><?= __("Industry") ?>:</label></h4>
        <input type="text" name="industry" id="industry" value="<?=htmlspecialchars(field_value(@$_REQUEST['industry'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="music"><?= __("Music") ?>:</label></h4>
        <input type="text" name="music" id="music" value="<?=htmlspecialchars(field_value(@$_REQUEST['music'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="movies"><?= __("Movies") ?>:</label></h4>
        <input type="text" name="movies" id="movies" value="<?=htmlspecialchars(field_value(@$_REQUEST['movies'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="college"><?= __("College") ?>:</label></h4>
        <input type="text" name="college" id="college" value="<?=htmlspecialchars(field_value(@$_REQUEST['college'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="passion"><?= __("Passion") ?>:</label></h4>
        <input type="text" name="passion" id="passion" value="<?=htmlspecialchars(field_value(@$_REQUEST['passion'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="activities"><?= __("Activities") ?>:</label></h4>
        <input type="text" name="activities" id="activities" value="<?=htmlspecialchars(field_value(@$_REQUEST['activities'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="books"><?= __("Books") ?>:</label></h4>
        <input type="text" name="books" id="books" value="<?=htmlspecialchars(field_value(@$_REQUEST['books'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="tv_shows"><?= __("TV shows") ?>:</label></h4>
        <input type="text" name="tv_shows" id="tv_shows" value="<?=htmlspecialchars(field_value(@$_REQUEST['tv_shows'], '')) ?>"  class="text longer" />
      </div>

      <div class="field">
        <h4><label for="cusines"><?= __("Cusines") ?>:</label></h4>
        <input type="text" name="cusines" id="cusines" value="<?=htmlspecialchars(field_value(@$_REQUEST['cusines'], '')) ?>"  class="text longer" />
      </div>

    </div>
    <input type="hidden" name="submit_search" value="search" />
  </fieldset>
</form>
