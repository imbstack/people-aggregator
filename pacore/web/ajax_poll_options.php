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
$login_required = FALSE;
$use_theme = 'Beta';
//TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
$cnt = $_GET['num'];
$inner_html = '';
for($i = 1; $i <= $cnt; $i++) {
    $inner_html .= '<div class="field_medium">
        <h4><label for="option"><span class="required"> * </span>option'.$i.':</label></h4>
        <input type="text" class="text longer" name="option'.$i.'"/>
        <div class="field_text"></div>
  </div>';
}
$inner_html .= '<div class="button_position">
       <input type="submit" name="create" value="Submit Poll" class="giant_input_btn"/>
</div>';
print $inner_html;
?>
