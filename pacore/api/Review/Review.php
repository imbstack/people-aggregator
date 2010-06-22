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

// Review API class
// Phillip Pearson
// Copyright (C) 2007 Broadband Mechanics, Inc
class Review {

    public static $columns = array(
        "review_id",
        "is_active",
        "subject_type",
        "subject_id",
        "author_id",
        "title",
        "body",
        "created",
        "updated",
    );
    // Review::get(42) returns a Review object for review #42
    public static function get($review_id) {
        $rev = new Review;
        $rev->load($review_id);
        return $rev;
    }
    // Review::get_recent_by_subject_type("movie", 5) returns the most recent 5 movie reviews
    public static function get_recent_by_subject_type($subject_type, $n) {
        $n = (int) $n;
        return Review::load_many_from_query("SELECT * FROM {reviews} WHERE is_active=1 AND subject_type=? ORDER BY created DESC LIMIT $n", array($subject_type));
    }
    // Review::get_recent_by_subject("movie", "123", 5) returns the most recent 5 movie reviews for movie #123
    public static function get_recent_by_subject($subject_type, $subject_id, $n) {
        $n = (int) $n;
        return Review::load_many_from_query("SELECT * FROM {reviews} WHERE is_active=1 AND subject_type=? AND subject_id=? ORDER BY created DESC LIMIT $n", array($subject_type, $subject_id));
    }
    // helper to turn a query + args into an array of Review objects
    public static function load_many_from_query($sql, $args) {
        $sth = Dal::query($sql, $args);
        $items = array();
        while($r = Dal::row_assoc($sth)) {
            $rev = new Review();
            $rev->load_from_row($r);
            $items[] = $rev;
        }
        return $items;
    }
    // backwards-compatible function to load a single review
    function load($review_id) {
        $row = Dal::query_one_assoc("SELECT * FROM {reviews} WHERE review_id=?", array($review_id));
        if(empty($row)) {
            throw new PAException(CONTENT_NOT_FOUND, "Could not find review id $review_id");
        }
        $this->load_from_row($row);
    }
    // helper to populate a review object from a row
    function load_from_row($row) {
        foreach($row as $k => $v) {
            $this->$k = $v;
        }
    }

    function save() {
        $is_new = (empty($this->review_id));
        if($is_new) {
            if(!isset($this->is_active)) {
                $this->is_active = 1;
            }
        }
        $sql           = $is_new ? "INSERT INTO {reviews} SET " : "UPDATE {reviews} SET ";
        $args          = array();
        $set_fragments = array();
        foreach(Review::$columns as $col) {
            switch($col) {
                case 'review_id':
                    // never set
                    continue;
                case 'updated':
                    // always set automatically
                    $set_fragments[] = "$col=NOW()";
                    break;
                case 'created':
                    // set automatically if new, otherwise copy
                    if($is_new) {
                        $set_fragments[] = "$col=NOW()";
                        break;
                    }
                    // else fallthrough
                default:
                    $set_fragments[] = "$col=?";
                    $args[] = $this->$col;
            }
        }
        $sql .= implode(", ", $set_fragments);
        if(!$is_new) {
            $sql .= " WHERE review_id=?";
            $args[] = $this->review_id;
        }
        Dal::query($sql, $args);
        if($is_new) {
            $this->review_id = Dal::insert_id();
        }
    }
}
?>