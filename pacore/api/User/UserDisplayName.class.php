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
/*
** Purpose of this class:
** by shadowing JUST this class a project can define the rules for the display_name
*/
class UserDisplayName {
	public function __construct($user) {
		// see of we have been passed a valid user
		if (empty($user->login_name)) {
			throw new PAException(USER_INVALID_LOGIN_NAME, "Invalid user id/login name/email address '$user_id_or_login_name'");
		}
		$this->user = $user;
	}

	public function get() {
		// ok, do our magic
		$display_name = $this->user->first_name." ".$this->user->last_name;
		return $display_name;
	}
}
?>