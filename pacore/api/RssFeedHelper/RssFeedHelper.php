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
require_once dirname(__FILE__)."/../config.inc";
require_once "api/DB/Dal/Dal.php";
require_once "api/Logger/Logger.php";
require_once "api/PAException/PAException.php";
require_once "api/User/User.php";

/**
 * A custom DomDocument object that keeps track of sites belonging to all RSS
 * @package RssFeedHelper
 * @author Tekriti Software
 */
  class RssFeedHelper extends DomDocument {

    /**
    *  Constructor.
    *  All it does is to call the parent constructor
    */
    public function __construct() {
      parent::__construct();
    }

    /**
     * Generate the RssFeed for the selected items using DomDocument functions
     * @param $row This $row is having the contents of all required rows
     * @return $this->saveXml() This is saving all xml string and return this string for the further process
     */
    public function generate_rss ($row, $user_id) {
      Logger::log("Enter: RssFeedHelper::generate_rss");
      // global var $_base_url has been removed - please, use PA::$url static variable

      $format = 'D, j M Y H:m:s O';

      $rss = $this->createElement( 'rss' );
      $attrib1 = $rss->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
      $attrib = $rss->setAttribute( "version", "2.0");
      $root = $this->createElement( "channel" );
      $user = new User();
      if ($user_id == '0') {
        $name1 = $this->createElement( "title" );
        $name1->appendChild( $this->createTextNode( sprintf(__("Latest posts from %s"), PA::$network_info->name) ) );
        $root->appendChild( $name1 );

        $body1 = $this->createElement( "description" );
        $body1->appendChild( $this->createTextNode(sprintf(__("Newest posts from everyone on %s"), PA::$network_info->name)) );
        $root->appendChild( $body1 );

        $url1 = $this->createElement( "link" );
        $url1->appendChild($this->createTextNode(PA::$url) );
        $root->appendChild( $url1 );
      }
      else {
        if (!empty($row[0])) { //TODO: figure out why we are getting a single empty row
          $user->load((int)$row[0]->author_id);
        }
        else {
          $user->load((int)$user_id);
        }


          $name1 = $this->createElement( "title" );
          $name1->appendChild( $this->createTextNode( sprintf(__("%s: %s blog"), $user->first_name, PA::$network_info->name) ) );
          $root->appendChild( $name1 );

          $body1 = $this->createElement( "description" );
          if ($user->last_name) {
            $body1->appendChild( $this->createTextNode("$user->first_name $user->last_name's Weblog" ) );
          }
          else {
            $body1->appendChild( $this->createTextNode("$user->first_name's Weblog" ) );
          }
          $root->appendChild( $body1 );

          $url1 = $this->createElement( "link" );

          $url1->appendChild($this->createTextNode(PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id) );
          $root->appendChild( $url1 );
        }



      $changed = $this->createElement( "lastBuildDate" );
      $changed->appendChild( $this->createTextNode( date($format, time()) ) );
      $root->appendChild( $changed );

      for($i=0; $i<count($row)-1; $i++) {
        $node = $this->createElement( "item" );

        $name = $this->createElement( "title" );
        $name->appendChild( $this->createTextNode( $row[$i]->title ) );

        $body = $this->createElement( "description" );
        $body->appendChild( $this->createTextNode( Content::_replace_percent_strings($row[$i]->content_id, $row[$i]->body, PA::$url) ) );

        $item_link = PA::$url . PA_ROUTE_CONTENT . "/cid=".$row[$i]->content_id;

        $url = $this->createElement( "link" );
        $url->appendChild( $this->createTextNode($item_link) );

        $guid = $this->createElement( "guid" );
        $guid->appendChild( $this->createTextNode($item_link) );

        $created = $this->createElement( "pubDate" );

        $created->appendChild( $this->createTextNode( date($format, $row[$i]->changed) ) );

        // getting the name of the author
        $sql = 'SELECT first_name FROM users WHERE user_id = ?';
        $data = array($row[$i]->author_id);
        $res = Dal::query($sql, $data);

        if ($res->numRows() > 0) {
          $result = $res->fetchRow(DB_FETCHMODE_OBJECT);
        }


        switch ($row[$i]->type) {

          case 7:
//             $video_content = $this->createElement( "media:content" );
//             $content_attrib = $video_content->setAttribute( "url", "$row[$i]->url" );
//             $content_attrib = $video_content->setAttribute( "filesize", "" );
//             $content_attrib = $video_content->setAttribute( "type", "Video" );
//             $content_attrib = $video_content->setAttribute( "medium", "Video" );
//             $node->appendChild( $video_content );

            break;
        }

        $node->appendChild( $name );
        $node->appendChild( $body );
        $node->appendChild( $url );
        $node->appendChild( $guid );
        $node->appendChild( $created );
        $root->appendChild( $node );
      }

      $rss->appendChild( $root );
      $this->appendChild( $rss );

      Logger::log("Exit: RssFeedHelper::generate_rss");
      return $this->saveXml();

    }


   /**
    *  This method is called by all rss feed generator method (common to all methods) using generate_rss method
    *
    * @param $res string The user id of the user for getting RssFeed of the content. FIXME: from the code, it looks like this has to be a DB result object (returned from Dal::query).
    * @return $result string This string contains the content of RssFeed file
    */

    public function get_rss_feed ($res, $user_id = 0) {

      // Collecting all $no_of_items rows in an array $row
      while($row[] = $res->fetchRow(DB_FETCHMODE_OBJECT)) {}
      $result = RssFeedHelper::generate_rss($row, (int)$user_id);
      return $result;
    }

  }


?>
