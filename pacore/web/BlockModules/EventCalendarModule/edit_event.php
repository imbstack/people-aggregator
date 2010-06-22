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
<h2>
    <? if($is_edit) {?>
      <?=__("Edit Event")?> <? echo $ed['event_title'];?>
    <?
}
else {?>
      <?=__("Create Event")?>
    <?
}?>
    </h2>
    <div class="field_event">
      <h4>
        <label for="event_title"><?=__("Event title")?></label>
        <font style="color:red"> *</font>
      </h4>
        <div class="center"><input type="text" name="event_title" value="<?
        echo @$ed['event_title'];
?>" class="text" id="event_title"></div>
    </div>

    <div class="field_event">
      <h4>
        <label for="start_time"><?=__("Start date")?></label>
        <font style="color:red"> *</font>
      </h4>
      <div class="center">
        <select name="start_day">
        <?php 
        for($i = 1; $i <= 31; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['start_day']) {
        echo " selected=\"selected\"";
    }
    echo ">$i</option>\n";
}?>
        </select> <select name="start_month">
        <?php 
        for($i = 1; $i <= 12; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['start_month']) {
        echo " selected=\"selected\"";
    }
    echo ">$months[$i]</option>\n";
}?>
        </select> <select name="start_year">
        <?php 
        for($i = 2000; $i <= 2020; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['start_year']) {
        echo " selected=\"selected\"";
    }
    echo ">$i</option>\n";
}?>
        </select> 
      </div>
    </div>
        
    <div class="field_event">
      <h4>
        <label for="start_time"><?=__("Start time")?></label>
      </h4>
      <div class="center">
        <select name="start_hour">
        <?php 
        for($i = 0; $i <= 23; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['start_hour']) {
        echo " selected=\"selected\"";
    }
    echo ">".zp($i)."</option>\n";
}?>
        </select> <select name="start_min">
        <?php 
        for($i = 0; $i < 60; $i += 5) {
    echo "<option value=\"$i\"";
    if($i == $ed['start_min']) {
        echo " selected=\"selected\"";
    }
    echo ">".zp($i)."</option>\n";
}?>
        </select>
      </div>
    </div>
    <div class="field_event">
      <h4>
        <label for="end_time"><?=__("End date")?></label>
      </h4>
      <div class="center">
        <select name="end_day">
        <?php 
        for($i = 1; $i <= 31; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['end_day']) {
        echo " selected=\"selected\"";
    }
    echo ">$i</option>\n";
}?>
        </select> <select name="end_month">
        <?php 
        for($i = 1; $i <= 12; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['end_month']) {
        echo " selected=\"selected\"";
    }
    echo ">$months[$i]</option>\n";
}?>
        </select> <select name="end_year">
        <?php 
        for($i = 2000; $i <= 2020; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['end_year']) {
        echo " selected=\"selected\"";
    }
    echo ">$i</option>\n";
}?>
        </select> 
      </div>
    </div>
        
    <div class="field_event">
      <h4>
        <label for="end_time"><?=__("End time")?></label>
      </h4>
      <div class="center">
        <select name="end_hour">
        <?php 
        for($i = 0; $i <= 23; $i++) {
    echo "<option value=\"$i\"";
    if($i == $ed['end_hour']) {
        echo " selected=\"selected\"";
    }
    echo ">".zp($i)."</option>\n";
}?>
        </select> <select name="end_min">
        <?php 
        for($i = 0; $i < 60; $i += 5) {
    echo "<option value=\"$i\"";
    if($i == $ed['end_min']) {
        echo " selected=\"selected\"";
    }
    echo ">".zp($i)."</option>\n";
}?>
        </select>
      </div>
    </div>

    <div class="field_medium_event" style="height:90px;">
      <h4><label for="event_venue"><?=__("Event venue")?></label></h4>
      <div class="center">
      <textarea id="event_venue" name="event_venue" cols="20" rows="5" style="height:auto;"><?
        echo @$ed['event_venue'];
?></textarea>
      </div>
    </div>

    <div class="field_medium_event" style="height:90px;">
      <h4><label for="event_description"><?=__("Event description")?></label></h4>
      <div class="center">
      <textarea id="event_description" name="event_description" cols="20" rows="5" style="height:auto;"><?
        echo @$ed['event_description'];
?></textarea>
      </div>
    </div>


<?php if(!empty($ed['banner'])) {?>
      <div class="field_medium_event" style="height:90px;">
        <h4><?=__("Current Event Banner")?></h4>
          <?php echo uihelper_resize_mk_img($ed['banner'], 430, 80, NULL, 'alt="Current Event Banner"', RESIZE_FIT);?>
      </div>
<?
}?>
      <div class="field">
        <h4><label for="upload_event_bannere">
        <?=__("Upload Event Banner")?></label></h4>
        <input name="userfile" type="file" class="text" id="upload_user_event_banner"/>
      </div>
    
    <div class="button_position">
      <? if($is_edit) {?>
      <input type="submit" name="update" value="Update Event" />
      <input type="submit" name="delete" value="Delete Event" onclick="return confirm('<?=__("Are you sure you want to delete this Event?")?>');"/>
      <input type="button" name="back" value="Cancel" onclick="javascript: history.back();" />
      <?
}
else {?>
      <input type="submit" name="create" value="Create Event" />
      <input type="button" name="back" value="Cancel" onclick="javascript: history.back();" />
      <?
}?>
    </div>
    
                    
