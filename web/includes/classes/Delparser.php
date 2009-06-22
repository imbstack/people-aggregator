<?php
/**
 * This class is use to fetch the RSS feeds of an user from http://del.icio.us
 * and then return the array contains the title and link of that title.
 *
 * @package Delparser
 * @author Tekriti Software (http://www.tekritisoftware.com)
 */

class Delparser {

  public function __construct() {
  }

  /**
  * Returns the tilte and the link of the post published by user
  * in http://del.icio.us
  *
  * @param string login name of user in http://del.icio.us.
  * @return array Returns the array $title_link for a particular user.Contain the title and link.
  * like $title_link[$i]["title"] contain title
  * like $$title_link[$i]["link"] contain link
  */
  static function get_link_of_user ($user_name) {
    Logger::log("Enter: Delparser::get_link_of_user");
    $title_link = array();
    $i = 0;

    $rss = delicious_getlinks($user_name);

    foreach ($rss as $item) {
      $title_link[$i]["title"] = $item['title'];
      $title_link[$i]["link"] = $item['link'];
      $i++;
    }

    Logger::log("Exit: Delparser::get_link_of_user");
    return $title_link;
  }
}

/**
* returns links from del.icio.us site for a given user
*/
function delicious_getlinks($uname) {
  $xml = file_get_contents("http://del.icio.us/rss/" . urlencode($uname));
  //  echo $xml;
  $dom = new DomDocument;
  $dom->loadXML($xml);
  $xp = new DOMXPath($dom);
  $xp->registerNamespace("rss", "http://purl.org/rss/1.0/");
  $xp->registerNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");

  $links = array();
  $n = 0;
  foreach ($xp->query("//rss:item") as $item) {
    $url = $item->getAttribute("about");
    $title = $xp->query("rss:title/text()", $item)->item(0)->data;
    $desc = $xp->query("rss:description/text()", $item)->item(0)->data;
    $links[] = array(
      'link' => $url,
      'title' => $title,
      'description' => $desc,
      );
    if (++$n >= 10) break;
  }
  return $links;
}

?>
