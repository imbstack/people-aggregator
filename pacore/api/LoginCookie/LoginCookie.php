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

// Login cookie handler class.

// Copyright (C) 2007 Broadband Mechanics
// Author: Phillip Pearson

// The algorithm here comes from Charles Miller[1], with the suggested
// improvement by Barry Jaspan[2].

// Miller's concept: When a user successfully logs in by entering a
// login name and password, a login cookie is generated by combining
// the username and two randomly generated values ('series' and
// 'token').  The three parts are stored in the login_cookies database
// table.

// When the user returns, if their session has timed out, we parse
// their login cookie and look for the (user, series, token) triplet in
// the database.  If present, we log them in and assign a new random
// token, which will be used the next time their session times out.

// Jaspan's improvement: If an attacker manages to steal a login
// cookie and use it to log in as the user, when the original user
// returns, they will present a now-invalid triplet.  We can detect
// this case as their 'series' value will be present in the database
// but their 'token' value will not.  In this case we invalidate *all*
// login cookies for the user.  This will cancel any 'stolen session',
// preventing the attacker from remaining perpetually loggged in as
// the user.

// 1. http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice
// 2. http://jaspan.com/improved_persistent_login_cookie_best_practice

class LoginCookie {

  private $user_id = NULL,
    $series = NULL,
    $token = NULL;

  public static $cookie_lifetime = 5184000; // 60 * 24 * 3600 seconds, i.e. 60 days

  // Returns TRUE if we have NOT already been set up with parse_cookie or new_session
  public function is_new() {
    return empty($this->user_id);
  }

  // Called by PA_Login::log_out to destroy a login session
  public static function invalidate_series($user_id, $series) {
    Dal::query("DELETE FROM login_cookies WHERE user_id=? AND series=?", array($user_id, $series));
  }

  // Call this to validate a login cookie.  On success it will return
  // a user ID.  On failure it will return FALSE.
  public function parse_cookie($cookie) {

    // Parse the given cookie
    if (!preg_match("/^uid:(\d+):([a-z0-9]+):([a-z0-9]+)$/", $cookie, $m)) {
      Logger::log("Invalid login cookie received: $cookie", LOGGER_WARNING);
      // Invalid cookie - ignore it
      return FALSE;
    }
    list(, $this->user_id, $this->series, $this->token) = $m;
    $this->user_id = (int)$this->user_id;

    // Flush old cookies
    Dal::query("DELETE FROM login_cookies WHERE expires < NOW()");

    // Locate our cookie
    $r = Dal::query_one("SELECT token FROM login_cookies WHERE user_id=? AND series=?", array($this->user_id, $this->series));
    if (!$r) {
      // Totally invalid - we don't even know of the series.  Probably timed out.
      return FALSE;
    }

    list($token) = $r;
    if ($token != $this->token) {
      // Possible attack detected - invalidate all sessions for this user
      Dal::query("DELETE FROM login_cookies WHERE user_id=?", array($this->user_id));
      Logger::log("Invalidated all sessions for user $this->user_id as a valid series ID but invalid token was presented -- someone has possibly had their login cookie stolen!", LOGGER_WARNING);
      return FALSE;
    }

    // Success -- assign a new token
    $this->token = $this->make_token();
    Dal::query("UPDATE login_cookies SET token=?, expires=DATE_ADD(NOW(), INTERVAL ".LoginCookie::$cookie_lifetime." SECOND) WHERE user_id=? AND series=?", array($this->token, $this->user_id, $this->series));

    return $this->user_id;
  }

  // Call on successful password-based login to set up a new session.
  public function new_session($user_id) {
    $this->user_id = $user_id;
    $this->series = $this->make_token();
    $this->token = $this->make_token();

    Dal::query("INSERT INTO login_cookies SET user_id=?, series=?, token=?, expires=DATE_ADD(NOW(), INTERVAL ".LoginCookie::$cookie_lifetime." SECOND)", array($this->user_id, $this->series, $this->token));
  }

  // Update tracking info
  public function update_tracking_info($user_agent, $ip_addr) {
    $this->check_setup();
    Dal::query("UPDATE login_cookies SET user_agent=?, ip_addr=? WHERE user_id=? AND series=?", array($user_agent, $ip_addr, $this->user_id, $this->series));
  }

  // Generate the cookie value to send to the client
  public function get_cookie() {
    $this->check_setup();
    return "uid:$this->user_id:$this->series:$this->token";
  }

  // Accessor for $series
  public function get_series() {
    return $this->series;
  }

  // ---

  private function check_setup() {
    if (empty($this->user_id) || empty($this->series) || empty($this->token)) {
      throw new PAException(OPERATION_NOT_PERMITTED, "Cannot perform operation as object has not been set up (call new_session() or process_cookie() first)");
    }
  }

  private function make_token() {
    return md5(rand());
  }

}

?>