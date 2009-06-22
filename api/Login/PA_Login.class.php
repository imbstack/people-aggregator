<?php

require_once "api/LoginCookie/LoginCookie.php";


class PA_Login {

  public static $cookie_name = 'pa_login';

  public $login_cookie;

  public function __construct() {
    $this->login_cookie = new LoginCookie();
  }

  // delete cookie
  public static function log_out() {
    User::set_status_offline(PA::$login_uid);
    if (!empty($_SESSION['login_series'])) {
      LoginCookie::invalidate_series(PA::$login_uid, $_SESSION['login_series']);
    }
    PA_Login::_unset_cookie();
  }

  // parse and validate a persistent login cookie (if 'remember me' checked)
  public static function process_cookie() {
    // check for cookie
    if (empty($_COOKIE[PA_Login::$cookie_name])) return; // no cookie

    $ck = new PA_Login;
    $ck->_process_cookie($_COOKIE[PA_Login::$cookie_name]);
  }

  private static $once_only = 1;

  private function _process_cookie($cookie) {
    if (PA_Login::$once_only) {
      PA_Login::$once_only = 0;
    } else {
      die("PA_Login::process_cookie() called more than once in a page - this is not allowed.");
    }

    // parse and validate cookie
    $user_id = $this->login_cookie->parse_cookie($cookie);
    if (empty($user_id)) {
      PA_Login::_unset_cookie();
      return; // invalid
    }

    // success - log in
    PA_Login::log_in($user_id, true, "cookie");
  }

  private static function _unset_cookie() {
    setcookie(PA_Login::$cookie_name, '', 0, PA::$local_url, ".".PA::$domain_suffix);
  }

  // Set up session, persistent login cookie, etc.  You *must* catch
  // PAException outside here as it can be thrown in various places.
  // (Disabled account, failure to load user).
  public function log_in($uid, $remember_me, $login_source) {
    $user_type = Network::get_user_type(PA::$network_info->network_id, $uid);
    if ($user_type == DISABLED_MEMBER) {
      throw new PAException(USER_ACCESS_DENIED, 'Your account has been temporarily disabled by the administrator.');
    }

    $logged_user = new User(); // load user
    $logged_user->load((int)$uid);
    $logged_user->set_last_login();

    register_session(
    $logged_user->login_name,
    $logged_user->user_id,
    $logged_user->role,
    $logged_user->first_name,
    $logged_user->last_name,
    $logged_user->email,
    $logged_user->picture);

    if ($remember_me) {
      // set login cookie
      if ($this->login_cookie->is_new()) {
        $this->login_cookie->new_session($uid);
      }
      $cookie_value = $this->login_cookie->get_cookie();
      $cookie_expiry = time() + LoginCookie::$cookie_lifetime;
      // update tracking info
      $this->login_cookie->update_tracking_info($_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
    } else {
      // clear login cookie
      $cookie_value = "";
      $cookie_expiry = 0;
    }
    // remember series ID, so we can destroy session on logout
    $_SESSION['login_series'] = $this->login_cookie->get_series();
    // remember login source, so we know if it's safe to let user change password, etc
    $_SESSION['login_source'] = $login_source;
    // set new cookie for next login!  (or delete cookie, if not remembering login)
    setcookie(PA_Login::$cookie_name, $cookie_value, $cookie_expiry, PA::$local_url, ".".PA::$domain_suffix);
  }
}
?>