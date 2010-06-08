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
/*
* Functions to create Form elements intelligently
* Martin
*/

class DynamicFormFields {
	// the css class to use for wrapping divs
	public $field_class = "field";

  public function __construct($data=NULL) {
  	$this->myFields = $data;
  }

  // process the incomming request data
  public function processRequest($request) {
    $fields = array();
    foreach ($request as $k=>$v) {
    	$fields[$k] = $v;
    }
    $this->myFields = $fields;
  }

  public function getVal($fieldname) {
  	if(!isset($this->myFields[$fieldname])) return NULL;
  	$data = $this->myFields[$fieldname];
  	if (is_array($data)) {
			if (isset($data['value'])) {
		  	// compound value?
				$v = $data['value'];
				return $v;
			}
  	}
		$v = $this->myFields[$fieldname];
		return $v;
  }

	public function hidden($fieldname) {
    $v = $this->getVal($fieldname);
  ?>
        <input type="hidden"
          name="<?= $fieldname ?>"
          id="<?= $fieldname ?>"
          value="<?= $v ?>" />
  <?php
	}


  public function image($label, $fieldname) {
    $v = $this->getVal($fieldname);
    if (!empty($v)) {
?>
  <div class="field_bigger">
    <h4><label><?= sprintf(__("Current %s"), $label)?></label></h4>
    <?
    $img_info = uihelper_resize_img($v, 200, 90, PA::$theme_rel."/skins/defaults/images/header_net.gif", NULL, RESIZE_FIT);
		?>
    <img src="<?= $img_info['url'];?>" alt="<?= __("Current Logo") ?>" <?= $img_info['size_attr']?> />
	</div>
  <br style="clear:both" />
<?php } ?>
  <div class="field">
    <h4><label><?= sprintf(__("Upload %s"), $label) ?></label></h4>
    <input name="<?= $fieldname?>" type="file" class="text short" id="<?= $fieldname ?>"/>
  </div>
  <br style="clear:both" />
  <?
  }

  public function textfield($label, $fieldname, $description='') {
    $v = $this->getVal($fieldname);
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
        <input type="text"
          name="<?= $fieldname ?>"
          id="<?= $fieldname ?>"
          value="<?= $v ?>" />
        </div>
        <?php
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
      </div>
		<br style="clear:both" />
  <?
  }


  public function textdisplay($label, $fieldname, $description='') {
    $v = $this->getVal($fieldname);
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
        <div class="textbixdisplay">
        <?= $v ?>
        </div>
        <input type="hidden"
          name="<?= $fieldname ?>"
          id="<?= $fieldname ?>"
          value="<?= $v ?>" />
        </div>
        <?php
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
      </div>
		<br style="clear:both" />
  <?
  }
  public function passwordfield($label, $fieldname, $seq=NULL) {
    $v = $this->getVal($fieldname);
    if ($seq) {
      $f = $f[$seq]; // we have a collection here
      $fieldname = $fieldname."[$seq]"; // to properly pass in HTML
    }
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
        <input type="password"
          name="<?= $fieldname ?>"
          id="<?= $fieldname ?>"
          value="<?= $v ?>" />
        </div>
      </div>
  <?
  }

  public function textarea($label, $fieldname, $description='') {
    $v = $this->getVal($fieldname);
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
        <textarea rows="3" cols="20"
          name="<?= $fieldname ?>"
          id="<?= $fieldname ?>"><?= $v ?></textarea>
        </div>
        <?php
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
      </div>
  <?
  }

  public function radiobutton($label, $fieldname, $fieldvalue) {
    $v = $this->getVal($fieldname);
  ?>
        <?php
          $checked = "";
          if ($v == $fieldvalue) {
            $checked = "checked=\"checked\"";
          }
          ?>
          <?=$label?><input type="radio" name="<?= $fieldname ?>" value="<?=$fieldvalue?>" <?=$checked?> />
          <?php
         ?>
  <?
  }
  public function radiobar($label, $fieldname, $fields, $seq=NULL, $description='') {
    $v = $this->getVal($fieldname);
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
        <?php
        for($i=0;$i<count($fields);$i++) {
          $field = $fields[$i];
          $checked = "";
          if ($v == $field['value']) {
            $checked = "checked=\"checked\"";
          }
          ?>
          <?=$field['label']?><input type="radio" name="<?= $fieldname ?>" value="<?=$field['value']?>" <?=$checked?> />
          <?php
        } ?>
        </div>
        <?php
        if(!empty($description)) {
        ?>
        <div class="field_text">
        <?=$description?>
        </div>
        <? } ?>
      </div>
<br style="clear:both" />
  <?
  }

    public function select($label, $fieldname, $values) {
    $v = $this->getVal($fieldname);
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
          <select name="<?=$fieldname?>" id="<?=$fieldname?>" class="select-txt">
            <option value=""> - Select -</option>
          <?php
          foreach ($values as $i=>$label) {
              // have we been passed value/label or just a list
              if (is_array($values[$i])) {
              	$cv = $values[$i]['value'];
              	$cl = $values[$i]['label'];
              } else if (!is_numeric($i)) {
              	// this is a dict
              	$cv = $i;
              	$cl = $label;
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
        <br />
      </div>
  <?
  }

  public function dateselect($label, $fieldname) {
     
    $_months = array_values(PA::getMonthsList());
    array_unshift($_months, " ");
    $monthnames = $_months;
    $years = PA::getYearsList();

    $v = $this->getVal($fieldname);
    $vyear = $vmonth = $vday = 0;
    if ($v) {
      list($vyear, $vmonth, $vday) = explode('-', $v);
    } else if ($this->getVal($fieldname.'_day')) {
    	$vday = $this->getVal($fieldname.'_day');
    	$vmonth = $this->getVal($fieldname.'_month');
    	$vyear = $this->getVal($fieldname.'_year');
    }
  ?>
      <div class="<?=$this->field_class?>">
        <h4><label for="<?=$fieldname?>"><?=$label?></label></h4>
        <div class="center">
          <select name="<?=$fieldname.'_day'?>" id="<?=$fieldname.'_day'?>" class="select-txt">
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
        <select name="<?=$fieldname.'_month'?>" id="<?=$fieldname.'_month'?>" class="select-txt">
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
        <select name="<?=$fieldname.'_year'?>" id="<?=$fieldname.'_year'?>" class="select-txt">
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
        <br />
      </div>
  <?
  }
}
?>