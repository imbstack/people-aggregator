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
include_once("../config.inc");
include_once("web/Widgets/ViewTracker/ViewTracker.php");

//spliting the path_info to get type.
$param = preg_split("|/|", $path_info);
for($i = 2;$i<count($param);$i++) {
  list($k, $v) = explode('=', $param[$i]);
  $url_param[$k] = $v;
}
if (!empty($url_param['type'])) {
  $template_file = 'web/Widgets/'.$widget_name.'/widget.tpl';
  $template = & new Template($template_file);
  $template->set('url_param', $url_param);
  $template->set('login_uid', PA::$login_uid);
  $html .= $template->fetch();
} else {
 $html .= 'Type does not exist';
}
header("Content-Type: application/x-javascript");
echo "document.write(".js_quote($html).");";

?>