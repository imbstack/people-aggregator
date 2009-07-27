<?php
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/ProfileIO/ProfileIO.php";

class DynamicProfile {
  public $user;
  public $blogrss_setting_status;

  public function __construct($user) {
    $this->user = $user;
    $merger = new ProfileMerger($user, NULL);
  }

  // process the incomming POST data
  // and make it available as 'profile' data
  // use ->save() to make it permanent
  public function processPOST($section) {
    $profile = Array();
    foreach ($_POST as $k=>$v) {
      if (is_array($v) && isset($v['value'])) {
        $profile[$k] = $v;
      } else if (is_array($v) && isset($v[1])) {
        // this is a collection
        foreach($v as $seq=>$d) {
          $profile[$k][$seq] = $d;
        }
      }
    }
    $this->user->{$section} = $profile;
  }

  public function save($section, $saveAs=null, $savePerm=null, $preserve = true) {
    $saveSection = ($saveAs) ? $saveAs : $section;
    if ($savePerm) {
    	foreach ($this->user->{$section} as $k=>$d) {
    		$d['perm'] = $savePerm;
    		$this->user->{$section}{$k} = $d;
    	}
    }
    // save the current state of the profile for given section
    $this->user->save_profile_section($this->user->{$section}, $saveSection, $preserve);
  }

  public function render_section($sec, $secIsPath=false) {
    $html = "";
    if (! $secIsPath) {
      $secPath = PA::$blockmodule_path ."/EditProfileModule/{$sec}_info.tpl";
    } else {
      $secPath = $sec;
    }
    ob_start();
    try {
      include($secPath);
    } catch (PAException $e) {
      throw $e;
    }
    $html .= ob_get_contents();
    ob_end_clean();
    return $html;
  }


/*
* FUnctions to create Form elements intelligently
*/
  public function accessonly($label, $fieldname, $section, $seq=NULL) {
    $f = @$this->user->{$section}[$fieldname];
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <span><?= $v ?></span>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]",
            $f["perm"]);
        ?>
        </div>
      </div>
  <?
  }

  public function url_accessonly($label, $fieldname, $section, $seq=NULL) {
    $f = @$this->user->{$section}[$fieldname];
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <span><a href="<?= $v ?>" target="_blank"><?= $v ?></a></span>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f['perm']);
        ?>
        </div>
      </div>
  <?
  }

  public function pic_accessonly($label, $fieldname, $section, $seq=NULL) {
    $f = @$this->user->{$section}[$fieldname];
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <span><img src="<?= $v ?>" /></span>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
      </div>
  <?
  }
  /*
  $show_access_list should be TRUE when we want to show the access_permission_list with the textfield
  $show_access_list should be FALSE when we don't want to show the access_permission_list with the textfield
  */
  public function textfield($label, $fieldname, $section, $seq=NULL, $show_access_list=true, $description='') {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <input type="text"
          name="<?= $fieldname.'[value]' ?>"
          id="<?= $fieldname.'[value]' ?>"
          value="<?= $v ?>" />
        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", @$f["perm"]);
        ?>
        </div>
        <?php
          }
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <br />
        <? } ?>
      </div>
        <br />
  <?
  }

  public function passwordfield($label, $fieldname, $section, $seq=NULL) {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field_big">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <input type="password"
          name="<?= $fieldname.'[value]' ?>"
          id="<?= $fieldname.'[value]' ?>"
          value="<?= $v ?>" />
        </div>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <br />
      </div>
  <?
  }

  public function textarea($label, $fieldname, $section, $seq=NULL, $show_access_list=true, $description='') {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <textarea rows="3" cols="20"
          name="<?= $fieldname.'[value]' ?>"
          id="<?= $fieldname.'[value]' ?>"><?= $v ?></textarea>
        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <?php
          }
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
      </div>
      <br />
  <?
  }
  public function radiobar($label, $fieldname, $fields, $section, $seq=NULL, $show_access_list=true, $description='') {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <?php
        for($i=0;$i<count($fields);$i++) {
          $field = $fields[$i];
          $checked = "";

              if (is_array($fields[$i])) {
              	$cv = $fields[$i]['value'];
              	$cl = $fields[$i]['label'];
              } else {
              	$cv = $cl = $fields[$i];
              }
              if($cv == $v) {
                $checked = "checked=\"checked\"";
              }

          ?>
          <?=$cl?><input type="radio" class="<?=$fieldname?>" name="<?= $fieldname.'[value]' ?>" value="<?=$cv?>" <?=$checked?> />
          <?php
        } ?>
        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <?php
          }
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
        <br />
      </div>
  <?
  }

  public function checkbox($label, $fieldname, $section, $seq=NULL, $show_access_list=true, $description='') {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
        <?php

          $checked = "";
          if ($v) {
            $checked = "checked=\"checked\"";
          }
          ?>
          <input type="checkbox" name="<?= $fieldname.'[value]' ?>" value="1" <?=$checked?> />

        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <?php
          }
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
        <br />
      </div>
  <?
  }

    public function select($label, $fieldname, $values, $section, $seq=NULL, $show_access_list=true) {
    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
  ?>
      <div class="field_medium">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
          <select name="<?=$fieldname.'[value]'?>" id="<?=$fieldname?>" class="select-txt">
            <option value=""> - Select -</option>
          <?php
          for ($i=0; $i<count($values); $i++) {
              // have we been passed value/label or just a list
              if (is_array($values[$i])) {
              	$cv = $values[$i]['value'];
              	$cl = $values[$i]['label'];
              } else {
              	$cv = $cl = $values[$i];
              }
              if($cv == $v) {
                $selected = " selected=\"selected\" ";
              } else {
                $selected = "";
              }
            ?>
            <option <?=$selected;?> value="<?=$cv?>"><?=$cl?></option>
          <?php } ?>
        </select>

        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <?php
          }
        ?>
        <br />
      </div>
  <?
  }

  public function dateselect($label, $fieldname, $section, $seq=NULL, $show_access_list=true) {
     
    $_months = array_values(PA::getMonthsList());
    array_unshift($_months, " ");
    $monthnames = $_months;
    $years = PA::getYearsList();

    $f = @$this->user->{$section}[$fieldname];
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
    $v = @$f['value'];
    $vyear = $vmonth = $vday = 0;
    if ($v) {
      list($vyear, $vmonth, $vday) = explode('-', $v);
    }
  ?>
      <div class="field_medium">
        <h4><label for="<?=$fieldname.'[value]'?>"><?=$label?></label></h4>
        <div class="center">
          <select name="<?=$fieldname.'_day[value]'?>" id="<?=$fieldname.'_day[value]'?>" class="select-txt">
            <option value=""></option>
          <?php
          for ($i=1; $i<=31; $i++) {
              if($i == (int)$vday) {
                $selected = " selected=\"selected\" ";
              } else {
                $selected = "";
              }
            ?>
            <option <?=$selected;?> value="<?=$i?>"><?=$i?></option>
          <?php } ?>
        </select>
        <select name="<?=$fieldname.'_month[value]'?>" id="<?=$fieldname.'_month[value]'?>" class="select-txt">
          <?php
          for ($i=1; $i<=12; $i++) {
              if($i == (int)$vmonth) {
                $selected = " selected=\"selected\" ";
              } else {
                $selected = "";
              }
            ?>
            <option <?=$selected;?> value="<?=$i?>"><?=$monthnames[$i]?></option>
          <?php } ?>
        </select>
        <select name="<?=$fieldname.'_year[value]'?>" id="<?=$fieldname.'_year[value]'?>" class="select-txt">
            <option value=""></option>
          <?php
          foreach ($years as $k=>$year) {
              if($year == (int)$vyear) {
                $selected = " selected=\"selected\" ";
              } else {
                $selected = "";
              }
            ?>
            <option <?=$selected?> value="<?=$year?>"><?=$year?></option>
          <?php } ?>
        </select>

        </div>
        <?php
          if ($show_access_list) {
        ?>
        <div>
        <?php
          print uihelper_get_user_access_list($fieldname."[perm]", $f["perm"]);
        ?>
        </div>
        <?php
          }
        ?>
        <br />
      </div>
  <?
  }

}
?>