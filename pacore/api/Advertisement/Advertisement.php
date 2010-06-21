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
require_once "api/ModuleSetting/ModuleSetting.php";

/**
* Class Advertisement for managing display of ads within application
*
* @package Advertisement
* @author Tekriti Software
*/
class Advertisement {

    /**
    * ad_id of the ad.
    * @access public
    * @var int
    */
    public $ad_id;

    /**
    * image associated with ad
    * @access public
    * @var string
    */
    public $ad_image;

    /**
    * url associated with ad
    * @access public
    * @var string
    */
    public $url;

    /**
    * script for ad (for future scope)
    * @access public
    * @var string
    */
    public $ad_script;

    /**
    * title of ad
    * @access public
    * @var string
    */
    public $title;

    /**
    * description of ad
    * @access public
    * @var string
    */
    public $description;

    /**
    * page id associated with ad, means where to display the ad
    * @access public
    * @var int
    */
    public $page_id;

    /**
    * orientation associated with ad
    * @access public
    * @var int
    */
    public $orientation;

    /**
    * date: ad created
    * @access public
    * @var int
    */
    public $created;

    /**
    * date: ad changed
    * @access public
    * @var int
    */
    public $changed;

    /**
    * status of ad, 1 for enabled, 0 for disabled
    * @access public
    * @var int
    */
    public $is_active;

    /**
    * type of the ad. This attribute is used for adding the textpads
    * Enum having values (ad, textpad)
    * @access public
    * @var string
    */
    public $type;

    /**
    * The default constructor for Advertisement class.
    */
    public function __construct() {
        //default type is ad
        $this->type = 'ad';
    }

    /**
    * This function saves an entry for advertisements table
    * input type: Values are set for object eg advertisement = new Advertisement; $advertisement->title= 'sys';
    * return type: ad_id
    */
    public function save() {
        Logger::log("Enter: function Advertisement::save()");
        if(empty($this->title)) {
            Logger::log("Exit: function Advertisement::save(). Title of the ad found blank.");
            throw new PAException(BAD_PARAMETER, __('Ad Title can not have empty.'));
        }
        if(empty($this->page_id)) {
            Logger::log("Exit: function Advertisement::save(). Page not specified.");
            throw new PAException(BAD_PARAMETER, __('Ad Target page is not specified.'));
        }
        $data = array(
            $this->user_id,
            $this->ad_image,
            $this->url,
            $this->ad_script,
            $this->title,
            $this->description,
            $this->page_id,
            $this->orientation,
            $this->created,
            $this->changed,
            $this->is_active,
        );
        if(!empty($this->ad_id)) {
            $sql = "UPDATE {advertisements} SET user_id = ?, ad_image = ?, url = ?, ad_script = ?, title = ?, description = ?, page_id = ?, orientation = ?, created = ?, changed = ?, is_active = ? WHERE ad_id = ?";
            array_push($data, $this->ad_id);
        }
        else {
            $sql = "INSERT INTO {advertisements} (user_id, ad_image, url, ad_script, title, description, page_id, orientation, created, changed, is_active, type, group_id) VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            array_push($data, $this->type);
            array_push($data, @$this->group_id);
        }
        $res = Dal::query($sql, $data);
        $ad_id = Dal::insert_id();
        Logger::log("Enter: function Advertisement::save");
        return $ad_id;
    }

    /**
    * This function will retrieve data from advertisements table
    * @param $params specify parameters for query e.g.param['cnt'] = TRUE
    * @param $conditions specify WHERE clause for query e.g.conditions['ad_id'] = 5,
    */
    static

    function get($params = NULL, $conditions = NULL) {
        Logger::log("Enter: function Advertisement::get");
        // if network_id is set already then get that n/w only
        $args = array();
        if(!empty($conditions['ad_id'])) {
            $sql = " SELECT * FROM {advertisements} WHERE ad_id = ? ";
            $args[] = $conditions['ad_id'];
        }
        else {
            $sql = " SELECT * FROM {advertisements} WHERE 1 ";
            if(is_array($conditions)) {
                foreach($conditions as $field_name => $field_value) {
                    $sql .= ' AND ';
                    if($field_value) {
                        $sql .= $field_name.' = ?';
                        array_push($args, $field_value);
                    }
                    else {
                        $sql .= $field_name.' IS NULL';
                    }
                }
            }
            //paging variables if set
            $sort_by   = (@$params['sort_by']) ? $params['sort_by'] : 'created';
            $direction = (@$params['direction']) ? $params['direction'] : 'DESC';
            $order_by  = ' ORDER BY '.$sort_by.' '.$direction;
            if(@$params['page'] && @$params['show'] && !@$params['cnt']) {
                $start = ($params['page']-1)*$params['show'];
                $limit = ' LIMIT '.$start.','.$params['show'];
            }
            else {
                $limit = "";
            }
            $sql = $sql.$order_by.$limit;
        }
        if($res = Dal::query($sql, $args)) {
        }
        else {
            return FALSE;
        }
        if(@$params['cnt'] == TRUE) {
            // here we just want to know total ads
            Logger::log("[ Exit: function Advertisement::get and returning count] \n");
            return $res->numRows();
        }
        $ads = array();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
            $ads[] = $row;
        }
        Logger::log("Exit: function Advertisement::get");
        return $ads;
    }

    /**
    * This function delete an entry from advertisements table
    * @param $ad_id specifies the ad to be deleted
    */
    static

    function delete($ad_id) {
        Logger::log("Enter: function Advertisement::delete");
        $sql = " DELETE FROM {advertisements} WHERE ad_id = ? ";
        $data = array(
            $ad_id,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: function Advertisement::delete");
        return;
    }

    /**
    *This function updates an entry in advertisements table
    *@param $update_fields is an array that contains fields_name and respective value
    *@param $condition is an array that forms the WHERE clause for update query
    */
    static

    function update($update_fields, $condition) {
        Logger::log("Enter: function Advertisement::update");
        $sql = 'UPDATE {advertisements} SET changed = ?';
        $data = array(
            time(),
        );
        if(!empty($update_fields)) {
            foreach($update_fields as $key => $value) {
                $sql .= ', '.$key.' = ?';
                array_push($data, $value);
            }
        }
        if(!empty($condition)) {
            $sql .= ' WHERE 1';
            foreach($condition as $k => $v) {
                $sql .= ' AND '.$k.' = ?';
                array_push($data, $v);
            }
        }
        $res = Dal::query($sql, $data);
        Logger::log("Exit: function Advertisement::update");
    }
    // This function defines an array of pages where ads are to be displayed
    public static function get_pages($for = 'network') {
        global $app;
        list($info, $pages) = $app->configObj->getConfigSection("pages");
        foreach($pages as $const_name => $page_info) {
            if(preg_match("/($for)/", $page_info['attributes']['page_type'])) {
                $ads_pages[] = array(
                    'caption' => __($page_info['attributes']['page_name']),
                    'value'   => $page_info['value'],
                    'api_id'  => strtolower(preg_replace("/^PAGE_/",
                    '',
                    $const_name)),
                );
            }
        }

        /*
        		$pages_settings = ModuleSetting::get_pages_default_setting($for, true);
        		$ads_pages = array();
        
        		foreach ($pages_settings as $i=>$page) {
        			$ads_pages[] = array(
        				'caption' => __($page->page_name),
        				'value'   => $page->page_id,
        				'api_id'  => $page->api_id);
        
        		}
        */
        return $ads_pages;
    }
    // This function defines an array of orientaion, that can be set for ads.
    public static function get_orientations() {
        $orientation[] = array(
            'caption' => __('left'),
            'value' => 1,
        );
        $orientation[] = array(
            'caption' => __('right'),
            'value' => 2,
        );
        $orientation[] = array(
            'caption' => __('middle'),
            'value' => 3,
        );
        return $orientation;
    }
}
?>