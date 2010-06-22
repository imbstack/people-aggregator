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
error_reporting(E_ALL);
$login_required = FALSE;
include_once("web/includes/page.php");
include_once("web/Widgets/ViewTracker/ViewTracker.php");
$type = NULL;
if(!empty($_GET['type'])) {
    $type = htmlspecialchars($_GET['type']);
}
$error = NULL;
$view_content = array();
//fetching the data according to view count.
$obj = new ViewTracker();
try {
    $obj->set_type($type);
    $view_content = $obj->get_most_viewed_pages();
}
catch(PAException$e) {
    $error = '<li>'.$e->message.'</li>';
}
$html = '<ul>';
if(!empty($view_content)) {
    foreach($view_content as $value) {
        if(!empty($value->title)) {
            $html .= '<li><a href= "'.$value->url.'">'.$value->title.'</a></li>';
        }
    }
}
else {
    $html .= $error;
}
$html .= '</ul>';
//creating the rss
$dom     = new DomDocument();
$format  = 'D, j M Y H:m:s O';
$rss     = $dom->createElement('rss');
$attrib1 = $rss->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
$attrib  = $rss->setAttribute("version", "2.0");
$root    = $dom->createElement("channel");
//this is the only item entry
$node = $dom->createElement("item");
$description = $dom->createCDATASection($html);
$node->appendChild($description);
$root->appendChild($node);
$rss->appendChild($root);
$dom->appendChild($rss);
header('Content-Type: text/xml');
print($dom->saveXml());
?>