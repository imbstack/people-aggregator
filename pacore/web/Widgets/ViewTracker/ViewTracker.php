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
require_once dirname(__FILE__).'/../../../config.inc';
require_once "api/DB/Dal/Dal.php";
require_once "api/PAException/PAException.php";

/** Class for handling the number of views of a page.
**/
Class ViewTracker {

    /**
     * Id of the page . primary key. 
    **/
    public $id;

    /**
     * Type of the page(article, video)
    **/
    private $type;

    /**
     * title of page.
    **/
    public $title;

    /**
     *Url of the page .
    **/
    public $url;

    /**
     *view count of the page.
    **/
    public $view;

    /**
     * timestamp of the page view.
    **/
    public $time_stamp;

    /**
     * Defining setter method for type.
    **/
    public function set_type($type) {
        Logger::log("Enter: ViewTracker::set_type");
        $type = trim($type);
        if(empty($type)) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'ViewTracker::type can not have empty value');
        }
        $sql = 'SHOW COLUMNS FROM page_views';
        $res = Dal::query($sql);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            if(ereg(('enum'), $row->Type)) {
                eval(ereg_replace('enum', '$valid_types = array', $row->Type).';');
            }
        }
        if(!in_array($type, $valid_types)) {
            throw new PAException(REQUIRED_PARAMETERS_MISSING, 'ViewTracker::type='.$type.' is not a valid page_type');
        }
        $this->type = $type;
        Logger::log("Exit: ViewTracker::set_type");
    }

    /**
     * function to save the view count of a page depending upon page_type.
    **/
    public function save() {
        Logger::log("Enter: ViewTracker::save");
        try {
            $sql = 'SELECT id, view FROM page_views WHERE type = ? AND title = ? ';
            $data = array(
                $this->type,
                trim($this->title),
            );
            $res = Dal::query($sql, $data);
            if($res->numRows()) {
                //increment the view_count.
                $row        = $res->fetchRow(DB_FETCHMODE_OBJECT);
                $this->view = $row->view+1;
                $this->id   = $row->id;
                $sql        = 'UPDATE page_views SET view = ?, time_stamp = ? WHERE id = ?';
                $data = array(
                    $this->view,
                    $this->time_stamp,
                    $this->id,
                );
                Dal::query($sql, $data);
            }
            else {
                //insert the new view_count.
                $sql = 'INSERT INTO page_views (type, title, url, view, time_stamp) VALUES (?, ?, ?, ?, ?)';
                $data = array(
                    $this->type,
                    trim($this->title),
                    $this->url,
                    1,
                    $this->time_stamp,
                );
                Dal::query($sql, $data);
            }
        }
        catch(PAException$e) {
            throw $e;
        }
        Logger::log("Exit: ViewTracker::save");
    }

    /**
     * Function to get n pages based on views for last n days.
     * type is to be set via set_type function.
    **/
    public function get_most_viewed_pages($duration = 7, $show = 'ALL', $page = 0, $direction = 'DESC', $sort_by = 'view', $cnt = FALSE) {
        Logger::log("Enter: ViewTracker::get");
        $order_by = $sort_by.' '.$direction;
        if($show == 'ALL' || $cnt == TRUE) {
            $limit = '';
        }
        else {
            $start = ($page-1)*$show;
            $limit = 'LIMIT '.$start.','.$show;
        }
        $sql = "SELECT * FROM page_views WHERE type = ? AND time_stamp > DATE_SUB(CURDATE(),INTERVAL ? DAY) ORDER BY $order_by $limit";
        $data = array(
            $this->type,
            $duration,
        );
        $res = Dal::query($sql, $data);
        if($cnt) {
            return $res->numRows();
        }
        $result = array();
        if($res->numRows()) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $result[] = $row;
            }
        }
        Logger::log("Exit: ViewTracker::get");
        return $result;
    }
}
?>