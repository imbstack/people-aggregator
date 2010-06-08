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
require_once dirname(__FILE__) . '/../../../config.inc';
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "web/includes/functions/functions.php";
require_once "web/includes/uihelper.php";

check_session(1);

include_once "api/Theme/Template.php";
require_once "api/Content/Content.php";
require_once "web/includes/functions/html_generate.php";

$parameter = js_includes("all") . '
<script src="peopleaggregator_api_desc.js" language="javascript" type="text/javascript"></script>
<script src="pa_api.js" language="javascript" type="text/javascript"></script>';
html_header("Javascript API access demo - PeopleAggregator", $parameter);
default_exception();

$page = & new Template(CURRENT_THEME_FSPATH."/homepage_pa.tpl");

$page->set('current_theme_path', PA::$theme_path);
$optional_parameters = "onload=\"$onload\"";
html_body($optional_parameters);

// header
$header = & new Template(CURRENT_THEME_FSPATH."/header.tpl");
$header->set('current_theme_path', PA::$theme_path);
$header->set('error', $_GET['error']);

// get user info and auth token for api
$login_user = new User();
$login_user->load((int)$_SESSION['user']['id']);
$auth_token = $login_user->get_auth_token(3600*24);

$array_middle_modules[] = <<<END

<script language="javascript">
var demo = {
    api: new PA_API(PA::$url . "/api/json"),

    auth_token: "$auth_token",

    login: "$login_user->login_name",

    results_per_page: 10,

    start: function() {
        this.show_user_list_page(1);
        this.show_friends_page(1);
    },

    show_user_list_page: function(page) {
        var result = \$("user_list_result");
        this.api.call({
            method: "peopleaggregator.getUserList",
            args: {
                resultsPerPage: this.results_per_page,
                page: page
            },
            onError: function(code, msg) {
                alert("error '"+code+"' occurred: "+msg);
            },
            onSuccess: function(resp) {
                var ret = [];

                // render page selector
                var pages = [];
                for (var page = 1; page < resp.totalPages + 1; ++page) {
                    if (page == resp.page)
                        pages.push(page);
                    else
                        pages.push('<a href="#" onclick="demo.show_user_list_page('+page+'); return false">'+page+'</a>');
                }
                ret.push('<p>Show page: '+pages.join(" ")+'</p>');

                // render user list
                var users = [];
                resp.users.each(function(u) {
                    users.push('<li><a target="_blank" href=PA::$url . "/user.php?uid='+u.id+'">'+u.login+'</a></li>');
                });
                ret.push('<ul>'+users.join("")+"</ul>");

                // and display it
                result.innerHTML = ret.join("");
            }
        });
    },

    show_friends_page: function(page) {
        var result = \$("friends_result");
        this.api.call({
            method: "peopleaggregator.getUserRelations",
            args: {
                login: this.login,
                resultsPerPage: this.results_per_page,
                page: page
            },
            onError: function(code, msg) {
                alert("error '"+code+"' occurred: "+msg);
            },
            onSuccess: function(resp) {
                var ret = [];

                // render page selector
                var pages = [];
                for (var page = 1; page < resp.totalPages + 1; ++page) {
                    if (page == resp.page)
                        pages.push(page);
                    else
                        pages.push('<a href="#" onclick="demo.show_friends_page('+page+'); return false">'+page+'</a>');
                }
                ret.push('<p>Show page: '+pages.join(" ")+'</p>');

                // render user list
                var relations = [];
                resp.relations.each(function(u) {
                    relations.push('<li><a target="_blank" href=PA::$url . "/user.php?uid='+u.id+'">'+u.login+'</a></li>');
                });
                ret.push('<ul>'+relations.join("")+"</ul>");

                // and display it
                result.innerHTML = ret.join("");
            }
        });
    }
};
</script>

<h1>Javascript ("Ajax") API demo</h1>

<p>This is a demo of using Javascript to access the PeopleAggregator API.</p>

<div id="user_list" class="middle-child-parent-bgcolor">

  <h2>User list</h2>

  <div id="user_list_result"></div>

  <p>The list above is generated dynamically by Javascript code (view source to see it) that uses the JSON version of the PeopleAggregator API's <a href="peopleaggregator_getUserList">getUserList</a> call.</p><!-- ' -->

  <p>The actual call is performed using Prototype's <a href="http://www.sergiopereira.com/articles/prototype.js.html#UsingAjaxRequest">Ajax.Request</a> object.  See <a href="pa_api.js">pa_api.js</a> for all the details, and instructions for how to use the PA JSON API.</p><!-- ' -->

</div>

<div id="friend_list" class="middle-child-parent-bgcolor">

  <h2>Friends</h2>

  <div id="friends_result"></div>

  <p>This box uses <a href="peopleaggregator_getUserRelations">getUserRelations</a> to fetch your relations<!--, and calls <a href="peopleaggregator_addUserRelation">addUserRelation</a> and <a href="peopleaggregator_deleteUserRelation">deleteUserRelation</a> (which require authentication) to add and delete relations-->.</p>

</div>

<script language="javascript">
demo.start();
</script>

END;

// footer
$footer = & new Template(CURRENT_THEME_FSPATH."/footer.tpl");
$footer->set('current_theme_path', PA::$theme_path);

//page settings
$page->set('header', $header);
$page->set('array_left_modules', $array_left_modules);
$page->set('array_middle_modules', $array_middle_modules);
$page->set('array_right_modules', $array_right_modules);
$page->set('footer', $footer);
$page->set('current_theme_path', PA::$theme_path);
echo $page->fetch();
?>
