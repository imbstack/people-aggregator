<?php
/**
 *  NOTE:
 *
 *     SCRIPT page.php should be included only in PA static pages !!!
 *
 *  TODO: all include directives that includes this script should be removed when the
 *  refactoring is complete!
 *
 **/

// used on occasion to make sure page.php has been included - DO NOT REMOVE THIS LINE!
define('INCLUDED_PAGE_PHP', 1);

// Include this file to handle all the standard page setup stuff that
// every .php file in the web directory uses.
// You MUST set $login_required = TRUE (if a login is required),
// "password" (if the user must have logged in 'strongly', by entering
// their name/password) or FALSE (if no login is required) before
// including this.
// If your page should always be shown, even on a private network
// (e.g. for login.php and invitation acceptance), set
// $login_required = FALSE and $login_never_required = TRUE.
// Set $page_redirect_function before inclusion to have some alternate
// action happen when we detect that the user is not logged in
// (instead of redirecting to login.php).  See web/badge_create.php
// for example usage.

require_once dirname(__FILE__).'/../../config.inc';
require_once 'web/includes/functions/functions.php';
require_once 'api/PAException/PAException.php';
require_once 'api/Network/Network.php';
require_once 'api/User/User.php';
require_once 'web/includes/file_names.php';
require_once 'web/includes/constants.php';
require_once 'web/includes/urls.php';
require_once 'api/Invitation/Invitation.php';
require_once 'web/includes/functions/auto_email_notify.php';
require_once 'api/Roles/Roles.php';
require_once 'api/Storage/Storage.php';// --- INITIAL AUTHENTICATION ---

if (!isset($login_required))
{
    throw new PAException('', "The \$login_required variable must be set before include()ing page.php!");
}

// Load network
//PA::$network_info = $network_info = get_network_info();

// Force login if we're on a private network, unless we're on login.php, register.php or dologin.php.
//PA::$extra = unserialize(PA::$network_info->extra);

if (!$login_required && PA::$network_info->is_private() && !@$login_never_required)
{
    $login_required = TRUE;
}

// Check user session / login status, and redirect to login page (or
// request page, for private networks) if required.
if (!check_session($login_required, @$page_redirect_function))
{
    if ($login_required)
    {
        exit;
    }
}

// --- PAST INITIAL AUTH: SET UP THE USER ENVIRONMENT ---
require_once 'api/PageRenderer/PageRenderer.php';
require_once 'web/includes/uihelper.php';
require_once 'web/includes/application_top.php';
require_once 'web/includes/functions/html_generate.php';
require_once 'web/includes/functions/date_methods.php';
require_once 'web/includes/functions/validations.php';
require_once 'web/languages/english/MessagesHandler.php';
require_once 'web/includes/classes/Navigation.php';
require_once 'web/includes/classes/FormHandler.php';// Register exception handler
default_exception();

// Update user rankings.
// TO DO: write a cron script to update ranking after a predefined period of time.
// Code for updating ranking is commented out for now, because it causes load to the system
// require_once "ext/Ranking/Ranking.php";
// $ranking = new Ranking();
// $ranking->update_ranking();
// *** See the definition for the PA class in config.inc for detail on
// how to access the standard user data variables set below. ***
// If a user is logged in, load 'em up, validate 'em, and track 'em.

$login_uid = PA::$login_uid = @$_SESSION['user']['id'];
$login_name = @$_SESSION['user']['name'];
if (!$login_uid)
{
    $login_user = PA::$login_user = NULL;
}
else
{
    $login_user = PA::$login_user = new User();
    try
    {
        PA::$login_user->load((int)$login_uid, 'user_id', TRUE);
    }
    catch (PAException $e)
    {
        if (!in_array($e->getCode(), array(USER_NOT_FOUND, USER_ALREADY_DELETED))) throw $e;// The currently logged-in user has been deleted; invalidate the session.
        session_destroy();
        session_start();
        $login_uid = PA::$login_uid = $login_name = $login_user = PA::$login_user = NULL;
    }
    // update tracking stuff
    if(PA::$login_uid)
    {
        PA::$login_user->update_user_time_spent();
        User::track_status(PA::$login_uid);
    }
}

// If a user is specified on the query string as an ID (uid=123) or
// login name (login=phil), validate the id/name and load the user
// object.
if (!empty($_GET['uid']))
{
    $page_uid = PA::$page_uid = (int)$_GET['uid'];
    $page_user = PA::$page_user = new User();
    PA::$page_user->load(PA::$page_uid);
}
elseif (!empty($_GET['login']))
{
    $page_user = PA::$page_user = new User();
    if (is_numeric($_GET['login']))
    {
        PA::$page_user->load((int)$_GET['login']);
    }
    else
    {
        PA::$page_user->load($_GET['login']);
    }
    $page_uid = PA::$page_uid = PA::$page_user->user_id;
}
else
{
    $page_uid = PA::$page_uid = $page_user = PA::$page_user = NULL;
}

// Copy PA::$page_* into PA::$* if present, otherwise use PA::$login_*.
if (PA::$page_uid)
{
    $uid = PA::$uid = PA::$page_uid;
    $user = PA::$user = PA::$page_user;
}
else
{
    $uid = PA::$uid = PA::$login_uid;
    $user = PA::$user = PA::$login_user;
}

// return the User object for the logged-in user.
// DEPRECATED: just use PA::$login_user now!
function get_login_user()
{
    return PA::$login_user;
}

// return the User object of the uid specified by the user
// DEPRECATED: just use PA::$page_user now.
function get_page_user()
{
    return PA::$page_user;
}

// return a User object for the uid specified by the user, or if none, for the logged in user
// DEPRECATED: just use PA::$user now.
function get_user()
{
    return PA::$user;
}

// Various functions return this object to indicate that the user
// should be redirected somewhere.  (For an example, see
// web/register.php).
class PA_Redirect
{
    function __construct($url)
    {
        $this->url = $url;
    }
}

// For form handling
handle_post();
?>