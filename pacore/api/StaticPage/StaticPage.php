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
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "api/User/User.php";

/**
* Class StaticPage for managing static pages of application
*
* @package StaticPage
* @author Tekriti Software
*/
class StaticPage {

    /**
    * id of the static page
    * @access public
    * @var int
    */
    public $id;

    /**
    * caption for page
    * @access public
    * @var string
    */
    public $caption;

    /**
    * url associated with page_text
    * @access public
    * @var string
    */
    public $url;

    /**
    * text of page
    * @access public
    * @var string
    */
    public $page_text;

    /**
    * The default constructor for StaticPage class.
    */
    public function __construct() {
    }

    /**
    * This function saves an entry in static_pages table
    * input type: Values are set for object eg links = new StaticPage; $links->caption= 'sys';
    * return type: id
    */
    public function save() {
        Logger::log("Enter: function StaticPage::save()");
        if(!empty($this->id)) {
            $sql = "UPDATE {static_pages} SET caption = ?, page_text = ? WHERE id = ".$this->id;
            $data = array(
                $this->caption,
                $this->page_text,
            );
        }
        else {
            $page_id = Dal::next_id('static_pages');
            $sql = "INSERT INTO {static_pages} (caption, url, page_text) VALUES(?, ?, ?)";
            $data = array(
                $this->caption,
                $this->url,
                $this->page_text,
            );
        }
        $res = Dal::query($sql, $data);
        return $this->url;
    }

    /**
    * This function will retrieve data from static_pages table
    * @param $params specify WHERE clause for query e.g.params['id'] = 5, 
    */
    static

    function get($params = NULL, $cnt = false, $page = 1, $show = 'ALL', $order_by = 'caption ASC') {
        Logger::log("Enter: function StaticPage::get");
        $args = array();
        if(!empty($params['id'])) {
            $sql = " SELECT * FROM {static_pages} WHERE id = ? ";
            $args[] = $params['id'];
        }
        else {
            $sql = " SELECT * FROM {static_pages} WHERE 1 ";
            if(is_array($params)) {
                foreach($params as $field_name => $field_value) {
                    $sql = $sql.' AND '.$field_name.' = ?';
                    array_push($args, $field_value);
                }
            }
            //paging variables if set
            if(!empty($order_by)) {
                $order_by = ' ORDER BY '.$order_by;
                $sql .= $order_by;
            }
            if($show == 'ALL' || $cnt == TRUE) {
                $limit = '';
            }
            else {
                $start = ($page-1)*$show;
                $limit = ' LIMIT '.$start.','.$show;
            }
            $sql .= $limit;
        }
        if($res = Dal::query($sql, $args)) {
        }
        else {
            return FALSE;
        }
        if($cnt == TRUE) {
            // here we just want to know total static_pages
            Logger::log("[ Exit: function StaticPage::get and returning count] \n");
            return $res->numRows();
        }
        $links = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $links[] = $row;
        }
        Logger::log("Exit: function StaticPage::get");
        return $links;
    }

    /**
    * This function delete an entry from static_pages table
    * @param $id specifies the id of static page to be deleted
    */
    static

    function delete($id) {
        Logger::log("Enter: function StaticPage::delete");
        $sql = " DELETE FROM {static_pages} WHERE id = ? ";
        $data = array(
            $id,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: function StaticPage::delete");
        return;
    }

    /**
      *This function updates an entry in static_pages table
      *@param $update_fields is an array that contains fields_name and respective value
      *@param $condition is an array that forms the WHERE clause for update query
      */
    static

    function update($update_fields, $condition) {
        Logger::log("Enter: function StaticPage::update");
        if(!empty($update_fields)) {
            $sql = 'UPDATE {static_pages} SET ';
            foreach($update_fields as $key => $value) {
                $sql .= $key.' = '.$value.', ';
            }
            $sql = substr($sql, 0,-2);
            if(!empty($condition)) {
                $sql .= ' WHERE 1';
                foreach($condition as $k => $v) {
                    $sql .= ' AND '.$k.' = '.$v;
                }
            }
        }
        $res = Dal::query($sql);
        Logger::log("Exit: function StaticPage::update");
    }
}
?>