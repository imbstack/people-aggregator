<?php

/* class Widget: 'blog badge' widget information
 */
class Widget {
    public $badge_id;
    public $user_id;
    public $badge_tag;
    public $title;
    public $config;
    
    public function __construct($user_id) {
	$this->user_id = $user_id;
	$this->config = array("friends"=>array());
    }

    public function load($badge_tag) {
	$this->badge_tag = $badge_tag;

	$sth = Dal::query("SELECT badge_id, title, badge_config FROM {blog_badges} WHERE is_active=1 AND user_id=? AND badge_tag=?", array($this->user_id, $badge_tag));
	if (!($r = Dal::row($sth))) {
	    throw new PAException(ROW_DOES_NOT_EXIST, "No widget with tag $badge_tag found for user $this->user_id");
	}
	list($this->badge_id, $this->title, $this->config) = $r;
	$this->config = unserialize($this->config);
    }

    public function save() {
	if ($this->badge_id) {
	    Dal::query("UPDATE {blog_badges} SET badge_config=? WHERE user_id=? AND badge_id=?", array(serialize($this->config), $this->user_id, $this->badge_id));
	} else {
	    if (!$this->user_id) throw new PAException(OPERATION_NOT_PERMITTED, "New widget requires user_id to save");
	    if (!$this->badge_tag) throw new PAException(OPERATION_NOT_PERMITTED, "New widget requires badge_tag to save");
	    if (!$this->title) $this->title = $this->badge_tag;
	    Dal::query("INSERT INTO {blog_badges} SET user_id=?, badge_tag=?, title=?, badge_config=?", array($this->user_id, $this->badge_tag, $this->title, serialize($this->config)));
	    $this->badge_id = Dal::insert_id();
	}
    }

    public function rename($new_name) {
	// whoops - this function is only to change the *title*, not the *badge_tag*.
/*	if (Dal::query("SELECT badge_id FROM {blog_badges} WHERE is_active=1 AND user_id=? AND badge_tag=?", array($this->user_id, $new_name))) {
	    throw new PAException(ITEM_ALREADY_EXISTS, "You already have a blog badge called $new_name");
	}	    */
	Dal::query("UPDATE {blog_badges} SET title=? WHERE user_id=? AND badge_id=?", array($new_name, $this->user_id, $this->badge_id));
    }

    public function delete() {
	Dal::query("UPDATE {blog_badges} SET is_active=0 WHERE user_id=? AND badge_id=?", array($this->user_id, $this->badge_id));
    }

    /* Returns true if the badge has not been saved to the database yet. */
    public function unsaved() {
	return !$this->badge_id;
    }
    
}

?>