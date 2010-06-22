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
<?php if(!empty($ed['event_id'])) {?>
<h2><?=__("This event appears on")?></h2>

    <ul class="event-associations">
    <?php
    // find all EventAssociations for the Event
    $assoc_ids  = EventAssociation::find_for_event($ed['event_id']);
    $assocs     = EventAssociation::load_in_list($assoc_ids);
    $appears_in = array();
    foreach($assocs as $n => $assoc) {
        // remember this for add to calendars below
        $appears_in[] = $assoc->assoc_target_type.":".$assoc->assoc_target_id;
        echo "<li>";
        echo __("Calendar for")." ".$assoc->assoc_target_type." ".$assoc->assoc_target_name;
        // add remove link if we are allowed to
        $may_remove = false;
        // user is creator/owner
        if($assoc->user_id == PA::$login_user->user_id) {
            $may_remove = true;
        }
        // user is target
        if($assoc->assoc_target_type == 'user' && $assoc->assoc_target_id == PA::$login_user->user_id) {
            $may_remove = true;
        }
        // user is admin
        if($assoc->assoc_target_type == 'group') {
            $is_member = Group::get_user_type((int) PA::$login_user->user_id, (int) $assoc->assoc_target_id);
            if($is_member == 'owner') {
                $may_remove = true;
            }
        }
        if($may_remove) {
            if(($assoc->user_id == PA::$login_user->user_id) && ($assoc->assoc_target_type == 'user' && $assoc->assoc_target_id == PA::$login_user->user_id)) {
                // special case:
                // owner is also target
                echo "&nbsp;".__("(This event will always appear in the creator's calendar.)");
            }
            else {
                echo " <input type=\"submit\" name=\"remove_assoc_".$assoc->assoc_id."\" value=\"".__("Remove")."\" />";
            }
        }
        echo "</li>\n";
    }
    ?>
    </ul>

<?php if($may_edit_calendar && !$is_display) {?>
    <h2><?=__("Add to further calendars")?></h2>
    <div class="field_medium_event" style="height:85px;">
      <h4><label for="add_users">People</label></h4>
      <div class="center">
			<div class="field_text">
				<?=__("Add people's login names here, seperated by commas.")?>
				<? if(count($add_assoc_user_errors)) {?><div style="color:red"> <?
					foreach($add_assoc_user_errors as $n => $err) {
                echo "<br />$err";
            }
        }?>
			</div>
      <textarea id="assoc_users" name="assoc_users" rows="3" style="height:auto;width:180px;"><?
        echo htmlspecialchars(@$_POST['assoc_users']);
        ?></textarea>
      <br />
      <input type="submit" name="add_assoc_users" value="Add" />
      </div>
    </div>
    <?php 
    $mygroups = Group::get_user_groups((int) PA::$login_user->user_id);
        if(count($mygroups)) {
            ?>
    <div class="field_medium_event">
      <h4><label for="add_group"><?=__("Groups you are a member of")?></label></h4>
      <div class="center">
			<div class="field_text">
				<? if(count($add_assoc_group_errors)) {
                foreach($add_assoc_group_errors as $n => $err) {
                    echo "<br />$err";
                }
            }
            ?>
			</div>
      <?php
      foreach($mygroups as $n => $g) {
                echo "<input type=\"checkbox\" name=\"add_groups[]\" 
        	value=\"".$g['gid']."\" ";
                if(in_array("group:".$g['gid'], $appears_in)) {
                    echo ' checked="checked" disabled="true"';
                }
                echo ">";
                echo "&nbsp;".$g['name']."<br/>\n";
            }
            ?>
      <br />
      <input type="submit" name="add_assoc_groups" value="<?=__("Add")?>" />
      </div>
    </div>
    <?
        }
        // if count mygroups?>

    <div class="button_position">&nbsp;</div>


<?php
    }
    // may_edit
}
// end if(!empty($ed['event_id']))
?>
