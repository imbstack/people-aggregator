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

include_once dirname(__FILE__)."/../../config.inc";
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Content/Content.php";
require_once "api/Logger/Logger.php";

/**
 * Implements Question
 * @extends Content
 * @author Tekriti Software (www.TekritiSoftware.com)
 */
class Question extends Content {

    /**
     * class Content::__construct
     */
    public function __construct() {
        parent::__construct();
        //TODO: move to constants
        $this->type               = QUESTION;
        $this->is_html            = 0;
        $this->title              = 'Question';
        $this->allow_comments     = 1;
        $this->is_default_content = TRUE;
    }

    /**
      * load Question data in the object
      * @access public
      * @param content id of the Question.
      */
    public function load($content_id) {
        Logger::log("Enter: Question::load | Arg: $content_id = $content_id");
        Logger::log("Calling: Content::load | Param: $content_id = $content_id");
        parent::load($content_id);
        Logger::log("Exit: Question::load");
        return;
    }

    /**
     * load Question data
     * @access public
     */
    public function load_many($params = NULL) {
        Logger::log("Enter: Question::load_many");
        $sql = "SELECT * FROM {contents} WHERE type = ? ";
        if(isset($params['is_active'])) {
            $sql .= ' AND is_active = 1';
        }
        $data_array = array(
            QUESTION,
        );
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
        $res = Dal::query($sql, $data_array);
        if($params['cnt'] == TRUE) {
            Logger::log("Exit: Question::load_many");
            return $res->numRows();
        }
        $result = array();
        if($res->numRows() > 0) {
            while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                $result[] = $row;
            }
        }
        Logger::log("Exit: Question::load_many");
        return $result;
    }

    /**
     * Saves Question in database
     * @access public
     */
    public function save() {
        Logger::log("Enter: Question::save");
        Logger::log("Calling: Content::save");
        $this->created    = time();
        $this->changed    = time();
        $this->display_on = 0;
        $this->trackbacks = NULL;
        $id               = parent::save();
        $this->update_question($id);
        Logger::log("Exit: Question::save");
        return $this->content_id;
    }

    /**
      * calls Content::delete() to soft delete a content
      * soft delete
      */
    public function delete($question_id) {
        Logger::log("Enter: Question::delete");
        $sql = 'DELETE FROM {contents} WHERE content_id = ? AND type = ?';
        $data = array(
            $question_id,
            QUESTION,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: Question::delete");
        return;
    }

    /**
      * calls Content::update() to soft delete a content
      * soft delete
      */
    public function update_question($question_id, $status = 0) {
        Logger::log("Enter: Question::update");
        $sql    = 'UPDATE {contents} SET is_active = 0 WHERE is_active = 1 AND type =
?';
        $res_id = Dal::query($sql, array(QUESTION));
        $sql    = 'UPDATE {contents} SET is_active = ? WHERE content_id = ? AND type =
?';
        $data = array(
            $status,
            $question_id,
            QUESTION,
        );
        $res = Dal::query($sql, $data);
        Logger::log("Exit: Question::update");
        return;
    }
}
?>
