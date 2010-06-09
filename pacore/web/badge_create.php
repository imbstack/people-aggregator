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

PeopleAggregator sidebar widget creator - HTML UI.

*/

error_reporting(E_ALL);

// --- controller (part 1)
// parse urls like:
//  badge_create.php <-- show default badge
//  badge_create.php/asdf <-- badge 'asdf', main page
//  badge_create.php/default/friends/op=include/id=1 <-- include friend 1 for badge asdf
$path_info = @$_SERVER['PATH_INFO'];
$bits = preg_split("|/|", $path_info);

$badge_tag = @$bits[1];
if (!$badge_tag) {
    $badge_tag = 'default';
}
$section = @$bits[2];

$params = $_GET;

// Check if this is an AJAX request before including page.php, which will redirect to the login page on failure.
if (@$params['op'] && @$section) {
    $page_redirect_function = "badge_create_ajax_not_logged_in_redirect";

    function badge_create_ajax_not_logged_in_redirect() {
	echo "NOT LOGGED IN";
	exit;
    }
}

$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Widget/Widget.php";
require_once "web/includes/functions/user_page_functions.php";

class Badge_Redirect {
    public function __construct($url) {
	$this->url = $url;
    }
}

class Badge {

    public $sections = array(
	"profile" => "Profile",
	"friends" => "Friends",
	"friends_ext" => "Imported friends",
	"groups" => "Groups",
	"networks" => "Networks",
	"posts" => "Blog posts and SB content",
	"images" => "Photos",
	"audio" => "Audio",
	"video" => "Videos",
	);

    public $section_singular_names = array(
	"friends" => "friend",
	"friends_ext" => "friend",
	"groups" => "group",
	"networks" => "network",
	);

    public $display_keys = array(
	"friends" => "friends",
	"friends_ext" => "friends",
	"groups" => "groups",
	"networks" => "networks",
	);

    public $friends_per_page = 20;

    public $showing_states = array(
	'only' => "Showing ONLY blue highlighted %KEY% from below",
	'all' => "Showing first blue, then grey highlighted %KEY% from below",
	);

    function __construct($user, $badge_tag) {
	$this->user = $user;
	$this->badge_tag = $badge_tag;
	try {
	    $this->widget = $user->load_widget($this->badge_tag);
	    $this->state =& $this->widget->config;
	} catch (PAException $e) {
	    switch ($e->code) {
	    case ROW_DOES_NOT_EXIST:
		// we need to have a default widget, so if we don't
		// have one, make it now.  otherwise rethrow the
		// exception, and the user will be redirected to the
		// default widget.
		if ($badge_tag != 'default') throw $e;
		$this->widget = new Widget($this->user->user_id);
		$this->widget->badge_tag = $this->widget->title = $badge_tag;
		$this->widget->save();
		$this->state =& $this->widget->config;
		break;
	    default:
		throw $e;
	    }
	}
	$this->url = BASE_URL_REL."/badge_create.php";
    }

    private function save_state() {
	try {
	    $this->widget->save();
	} catch (PAException $e) {
	    echo "<p>error occurred saving badge!</p><p>$e->code | ".$e->getMessage()."</p>";
	    exit;
	}
    }

    function op($op, $section, $params) {
	$section_singular = @$this->section_singular_names[$section];
	if (method_exists($this, "op_$section")) {
	    $ret = $this->{"op_$section"}($op, $params);
	    if ($ret !== NULL) return $ret;
	}
	switch ($op) {
	    // enable/disable a section
	case 'enable':
	    if (!isset($this->sections[$section])) return "invalid section for enable";
	    if (!isset($this->state[$section])) $this->state[$section] = array();
	    $this->state[$section]['enabled'] = TRUE;
	    $this->save_state();
	    return $this->render_section_inner($section);
	case 'disable':
	    if (!isset($this->sections[$section])) return "invalid section for disable";
	    if (isset($this->state[$section])) $this->state[$section]['enabled'] = FALSE;
	    $this->save_state();
	    return $this->render_section_inner($section);

	    // include/exclude an individual friend/group/etc
	case 'include':
	case 'exclude':
	    $subid = @$params['id'];
	    if (is_string($subid) && substr($subid, 0, 1) == '_') {
		$subid = pack("H*", substr($subid, 1));
	    }
//	    echo "op with section $section ...";
	    //FIXME: verify that the subid is actually a valid network
	    $this->handle_op_include_exclude($section, $op, $subid);
	    return $this->{"render_".$section_singular."_image"}($subid);

	    // change show_state
	case 'show':
	    $show_state = $params['show'];
	    $this->handle_op_show($section, $show_state);
	    return $this->render_section_inner($section);

	case 'display':
	    return $this->render_section_inner($section);

	case 'create':
	    $this->assert_post();
	    return $this->handle_op_create_new_badge($_POST);
	    break;

	case 'rename':
	    $this->assert_post();
	    return $this->handle_op_rename_badge($_POST);
	    break;

	case 'delete':
	    $this->assert_post();
	    return $this->handle_op_delete_badge($_POST);
	    break;

	default:
	    return "invalid $section op";
	}
    }

    private function assert_post() {
	if ($_SERVER['REQUEST_METHOD'] != "POST") throw new PAException(OPERATION_NOT_PERMITTED, "This operation requires an HTTP POST");
    }
    
    private function handle_op_create_new_badge($params) {
	$title = $params['new_widget_name'];

	// sanitize name
	$new_name = preg_replace("~[^A-Za-z0-9\-\_]~", "_", $title);

	// make sure we don't already have one with that name
	try {
	    $this->user->load_widget($new_name);
	    // that should throw an exception; if it doesn't, the name is a duplicate
	    return new Badge_Redirect("$this->url?focus=new_widget_name&error=".urlencode("You already have a widget called $new_name. Please select another name."));
	} catch (PAException $e) {
	    switch ($e->getCode()) {
	    case ROW_DOES_NOT_EXIST:
		// Good - this is what we want (no existing widget with this name).
		break;
	    default:
		// A real error occurred; pass it through.
		throw $e;
	    }
	}

	$widget = new Widget($this->user->user_id);
	$widget->badge_tag = $new_name;
	$widget->title = $title;
	$widget->save();

	return new Badge_Redirect("$this->url/$new_name");
    }

    private function handle_op_rename_badge($params) {
	$widget = $this->user->load_widget($this->badge_tag);
	$widget->rename($params['new_name']);
	return new Badge_Redirect("$this->url/$this->badge_tag");
    }

    private function handle_op_delete_badge($params) {
	$widget = $this->user->load_widget($this->badge_tag);
	$widget->delete();
	return new Badge_Redirect($this->url);
    }

    private function handle_op_include_exclude($section, $op, $subid) {
	$incl =& $this->state[$section]['included'];
//	echo "section: $section; id: $subid"; echo "<hr>before:"; var_dump($incl);
	if (!isset($incl)) {$incl = array(); $this->save_state();}
	if (is_numeric($subid)) $subid = (int)$subid;
	if ($op == 'include') {
	    $incl[$subid] = 1;
	} else {
	    if (isset($incl)) unset($incl[$subid]);
	}
//	echo "<hr>after:"; var_dump($incl);
	$this->save_state();
    }

    private function handle_op_show($section, $show_state) {
	if (!@$this->showing_states[$show_state]) return "invalid show_state";
	$this->state[$section]['show'] = $show_state;

	$this->save_state();
    }

    // render the entire editing interface
    function render() {
	$ret = "";
	foreach ($this->sections as $key => $title) {
	    $ret .= $this->render_section($key, $title, "this:render_$key");
	}
	return $ret;
    }

    // render an entire section (e.g. friends, groups, ...)
    function render_section($key, $title, $body) {
	$inner = $this->render_section_inner($key, $title, $body);
	return <<<ENS
<div class="badge_section" id="badge_$key"><!-- start badge section -->
$inner
</div><!-- end badge section -->
ENS;
    }
   
    // render just the internal part of a section div
    function render_section_inner($key) {
	$title = $this->sections[$key];
	$section_state = @$this->state[$key];
	$enabled = @$section_state['enabled'];
	$enable_op = $enabled ? "disable" : "enable";
	$selection_count = "";
	if ($enabled) {
	    if (method_exists($this, "count_$key")) {
		$selection_count = $this->{"count_$key"}();
	    } else if (in_array($key, array("friends", "friends_ext", "groups", "networks"))) {
		$selection_count = '(<span id="'.$key.'_count">'.count(@$section_state['included'])."</span> selected)";
	    }
	}
	$inner = $enabled ? $this->{"render_$key"}() : '<p class="badge_section_disabled">'.__("This section is disabled. Click the checkbox above to show it in your sidebar widget.").'</p>';
	$checkbox_checked = $enabled ? 'checked="checked"' : '';
	$enabled_text = $enabled ? 'included' : '&larr; '._("click to include");
	return <<<ENS
<h2>$title<input type="checkbox" $checkbox_checked onclick="badge.reload_section('badge_$key', '$this->url/$this->badge_tag/$key?op=$enable_op');"> <span class="badge_section_included">$enabled_text</span> $selection_count</h2>
$inner
ENS;
    }

    // section-specific renderers

    function render_friend_image($rel_or_id) {
	if (is_integer($rel_or_id) || is_string($rel_or_id)) {
	    // it's an id
	    $rel = Relation::get_relation_record($this->user->user_id, $rel_or_id);
	} else {
	    $rel = $rel_or_id;
	}
	if (!$rel) return "invalid relation";
//	echo "<hr>render image: ".var_export($rel, TRUE);

	$section = is_numeric($rel['user_id']) ? 'friends' : 'friends_ext';

	$friends = $this->state[$section];
	$included = isset($friends['included'][$rel['user_id']]);
//	echo "<hr>friends-included: "; var_dump($friends['included']);
//	echo "<hr>included: "; var_dump($included);

	if (preg_match("|^http://|", $rel['picture'])) {
	  $img = '<img src="'.htmlspecialchars($rel['picture']).'" width="75">';
	} else {
	  $img = uihelper_resize_mk_user_img($rel['picture'], 75, 75);
	}
	$name = $rel['display_name'];
	$cls = "friend_pic";
	if ($included) $cls .= " included_friend";
	$include_op = $included ? "exclude" : "include";

	$uid = $rel['user_id'];
	$div_id = is_numeric($uid) ? $uid : md5($uid);
	$munge_id = is_numeric($uid) ? $uid : "_".bin2hex($rel['user_id']);
	$onclick = "badge.include_obj('$section', 'friend_$div_id', '$munge_id', '$include_op');";
	return <<<ENS
<div class="$cls" onclick="$onclick" title="Click to $include_op this friend.">
	<p>$img</p>
	<p>$name</p>
</div>
ENS;
    }

    private function make_showing_link(&$state, $section_key, $qs) {
	$keys = array_keys($this->showing_states);
	if (!@$state['show']) { $state['show'] = $keys[0]; $this->save_state(); } // default
	$next_showing_state = $keys[(array_search($state['show'], $keys) + 1) % count($keys)]; // next one

	$all_checked = $state['show'] == 'all' ? 'checked="checked"' : '';
	$only_checked = $state['show'] == 'only' ? 'checked="checked"' : '';
	$section_key_friendly = strtolower($this->sections[$section_key]);
	
	return "<div><input type=\"radio\" name=\"showing_state_$section_key\" onclick=\"if (this.checked) badge.reload_section('badge_$section_key', '$this->url/$this->badge_tag/$section_key?op=show&show=all&$qs')\" $all_checked> Show a random selection from all of my ${section_key_friendly}</div>
<div><input type=\"radio\" name=\"showing_state_$section_key\" onclick=\"if (this.checked) badge.reload_section('badge_$section_key', '$this->url/$this->badge_tag/$section_key?op=show&show=only&$qs')\" $only_checked> Show only the ${section_key_friendly} highlighted below</div>
";
    }


    function render_profile() {
	return $this->_render_template("profile");
    }

    function render_friends() {
	return $this->_render_friends("internal");
    }

    function render_friends_ext() {
	return $this->_render_friends("external");
    }

    function _render_friends($scope) {
	$section = ($scope == 'internal') ? 'friends' : 'friends_ext';
	$section_singular = $this->section_singular_names[$section];
	$page = (int)@$_REQUEST['friend_page'];
	$friend_state =& $this->state[$section];

	// paging controls
	$total_friends = Relation::count_relations($this->user->user_id, $scope);
	$total_pages = (int)ceil((float)$total_friends / $this->friends_per_page);
	if ($page > $total_pages) $page = $total_pages;
	if ($page < 1) $page = 1;
	$paging = "Show page: ";
	for ($i = 1; $i < $total_pages+1; ++$i) {
	  if ($i == $page) {
	    $paging .= "$i ";
	  } else {
	    $paging .= "<a href=\"javascript:badge.reload_section('badge_$section', '$this->url/$this->badge_tag/$section?friend_page=$i');\">$i</a> ";
	  }
	}
	$first_friend = ($page-1)*$this->friends_per_page + 1;
	$last_friend = min($first_friend + $this->friends_per_page - 1, $total_friends);
	$paging .= "(showing $first_friend-$last_friend of $total_friends friends)";

	// 'showing XXX' link
	$showing = $this->make_showing_link($friend_state, $section, "friend_page=$page");

	// facewall display
	$facewall = "";
	foreach (Relation::get_all_relations($this->user->user_id, 0, FALSE, $this->friends_per_page, $page, 'created', 'desc', $scope) as $rel) {
	    $facewall .= '<div id="'.$section_singular.'_'.($scope == 'internal' ? $rel['user_id'] : md5($rel['user_id'])).'">'.$this->render_friend_image($rel, $scope).'</div>';
	}

	// outer template
	return <<<ENS
<p>$showing</p>

<!--<p>How many items to show at once: <select id="item_count" onchange="badge.update()">
<option value="5">5</option>
<option value="10">10</option>
<option value="15" selected="selected">15</option>
<option value="20">20</option>
</select></p>-->

<div>
$facewall
<div style="clear: both"></div>
</div>

<p>$paging</p>
ENS;
    }

    function render_group_image($grp_or_id) {
	$grp = new Group();
	if (is_numeric($grp_or_id)) {
	    // it's an id
	    $grp->load((int)$grp_or_id);
	} else {
	    $grp->load($grp_or_id['gid']);
	}

	$groups = $this->state['groups'];
	$included = isset($groups['included'][(int)$grp->collection_id]);

	$img = uihelper_resize_mk_user_img($grp->picture, 75, 75);
	$name = $grp->title;
	$cls = "friend_pic";
	if ($included) $cls .= " included_friend";
	$include_op = $included ? "exclude" : "include";

	$onclick = "badge.include_obj('groups', 'group_$grp->collection_id', $grp->collection_id, '$include_op');";
	return <<<ENS
<div class="$cls" onclick="$onclick" title="Click to $include_op this group.">
	<p>$img</p>
	<p>$name</p>
</div>
ENS;
    }

    function render_groups() {
	$page = 1;
	$group_state =& $this->state['groups'];

	$showing = $this->make_showing_link($group_state, "groups", "");

	$facewall = "";
	foreach (Group::get_user_groups($this->user->user_id, FALSE, $this->friends_per_page, $page) as $grp) {
	    $facewall .= '<div id="group_'.$grp['gid'].'">'.$this->render_group_image($grp).'</div>';
	}

	return <<<ENS
<p>$showing</p>

<div>
$facewall
<div style="clear: both"></div>
</div>

ENS;
    }

    function render_network_image($net_or_id) {
	// find the id
	if (is_numeric($net_or_id)) {
	    // there's no easy way to get info about a network; we have to go through this incantation...
	    $net_obj = new Network();
	    $net_obj->network_id = $net_or_id;
	    $net = $net_obj->get();
	    $net = $net[0];
	} else {
	    $net = $net_or_id;
	}

	// and display
	$networks = $this->state['networks'];
	$included = isset($networks['included'][(int)$net->network_id]);

	$img = uihelper_resize_mk_user_img($net->inner_logo_image, 75, 75);
	$name = $net->name;
	$cls = "friend_pic";
	if ($included) $cls .= " included_friend";
	$include_op = $included ? "exclude" : "include";

	$onclick = "badge.include_obj('networks', 'network_$net->network_id', $net->network_id, '$include_op');";
	return <<<ENS
<div class="$cls" onclick="$onclick" title="Click to $include_op this network.">
	<p>$img</p>
	<p>$name</p>
</div>
ENS;
    }

    function render_networks() {
	$page = 1;
	$network_state =& $this->state['networks'];

	$showing = $this->make_showing_link($network_state, "networks", "");

	$facewall = "";
	foreach (Network::get_user_networks($this->user->user_id, FALSE, $this->friends_per_page, $page) as $net) {
	    $facewall .= '<div id="network_'.$net->network_id.'">'.$this->render_network_image($net).'</div>';
	}

	return <<<ENS
<p>$showing</p>

<div>
$facewall
<div style="clear: both"></div>
</div>

ENS;
    }

    function render_posts() {
	return "<p>Showing recent blog posts in sidebar widget.</p>";
    }

    function render_images() {
	return "<p>Showing recent photos in sidebar widget.</p>";
    }

    function render_audio() {
	return "<p>Showing recent audio in sidebar widget.</p>";
    }

    function render_video() {
	return "<p>Showing recent video in sidebar widget.</p>";
    }

    private function _render_template($template) {
	$tpl =& new Template(CURRENT_THEME_FSPATH."/widget_$template.tpl");
	return $tpl->fetch();
    }

}
    
// --- controller (part 2)

// find user and badge
$user = new user();
$user->load((int)$login_uid);
try {
    $badge = new Badge($user, $badge_tag);
} catch (PAException $e) {
    switch ($e->code) {
    case CONTENT_HAS_BEEN_DELETED:
    case ROW_DOES_NOT_EXIST:
	header("Location: " . PA::$url . "/badge_create.php");
	exit;
    default:
	throw $e;
    }
}

function badge_disp($content) {
    if ($content instanceof Badge_Redirect) {
	header("Location: ".$content->url);
    } else {
	echo $content;
    }
    exit;
}

// execute op if required
if (@$params['op']) {
    badge_disp($badge->op($params['op'], $section, $params));
} else if (@$section) {
    badge_disp($badge->op('display', $section, $params));
}

// --- left sidebar (widget selection) html

function render_left_sidebar() {
    global $user;

    $page_url = PA::$url;
    $badge_list = "";
    
    foreach ($user->list_widgets() as $badge_info) {
	    list($badge_id, $title) = $badge_info;
	    $badge_list .= '<li><a href="'. $page_url .'/badge_create.php/'.htmlspecialchars($badge_id).'">'.htmlspecialchars($title ? $title : $badge_id)."</a></li>";
    }

    $badges_html = <<<ENS
<div class="pane module">

<h1>Your widgets</h1>

<ul>
$badge_list
</ul>

<form method="POST" action="$page_url/badge_create.php/default?op=create"><p>Create another:
<input type="text" id="new_widget_name" name="new_widget_name" value="new-widget">
<input type="submit" value="Create">
</p></form>

</div><!-- pane -->

<div class="pane module">

<h1>Paste this into your blog</h1>

<p>Paste this HTML into your blog to display this information:</p>
<textarea id="badge_html" rows="10" cols="15" onclick="this.select(); x = this.createTextRange(); x.execCommand('Copy');">
</textarea>

</div>

ENS;

    return $badges_html;
}

// --- main center column html

ob_start();

?><div class="pane badge_pane wide_content">

<?php

if (isset($_REQUEST['error'])) {
?>
<div class="badge_error"><?= htmlspecialchars($_REQUEST['error']) ?></div>
<?php
}

?>

<h1><?= __("Design your sidebar widget") ?>: <?= htmlspecialchars($badge->widget->title ? $badge->widget->title : $badge_tag) ?></h1>

<p><form method="POST" action="<?= PA::$url ?>/badge_create.php/<?= $badge_tag ?>?op=rename"><?= __("Rename this widget") ?>: <input type="text" name="new_name" value="<?= htmlspecialchars($badge->widget->title) ?>"><input type="submit" value="<?= __("Rename") ?>" /></form></p>
<p><form method="POST" onsubmit="return badge.confirm_delete();" action="<?= PA::$url ?>/badge_create.php/<?= $badge_tag ?>?op=delete"><?= __("Or") ?>: <input type="submit" value="<?= __("Delete this widget") ?>"></form></p>

<script><!--
var badge = {
  update: function() {
    var randomness = Math.round(Math.random() * 10000000.0);

    var url_start = '/badge.php/';
    var url_end = '/<?= $login_uid ?>/show/<?= $badge_tag ?>/';

    document.getElementById("badge_html").value = '<div id="pa_widget_'+randomness+'">[Loading <?=addslashes(PA::$site_name)?> widget ...]</div><'+'script language="javascript">var widget_'+randomness+'=window.onload;window.onload=function(){var s=document.createElement("script");s.type="text/javascript";s.src="<?= PA::$url ?>'+url_start+'jsdiv'+url_end+'div=pa_widget_'+randomness+'";document.getElementsByTagName("head")[0].appendChild(s);if(widget_'+randomness+')widget_'+randomness+'();};<'+'/script>';

    var badge_url = '<?= BASE_URL_REL ?>'+url_start+"html"+url_end;
    $("#badge_preview").load(badge_url, {});
  },

  reload_section: function(id, url) {
    var elem = $("#"+id);
    elem.load(url, {}, function(rtext, stat, resp) {
      if (rtext == 'NOT LOGGED IN') {
	window.location = "<?= PA::$url ?>/login.php?error=1&return=<?= PA::$url ?>/badge_create.php/<?= $badge_tag ?>";
      }
      badge.update();
    });
  },

  include_obj: function(section, nodeid, objid, include_op) {
    var url = '<?= BASE_URL_REL ?>/badge_create.php/<?= $badge_tag ?>/'+section+'?op='+include_op+'&id='+escape(objid);
    badge.reload_section(nodeid, url);
    var n = document.getElementById(section+"_count");
    n.innerHTML = parseInt(n.innerHTML) + (include_op == 'include' ? 1 : -1);
  },

  confirm_delete: function() {
    return confirm("<?= __("Are you sure you wish to delete this widget?") ?>");
  },

  __end__: 1
};

<?php

if (isset($_REQUEST['focus'])) {
?>
    var e = document.getElementById("<?= $_REQUEST['focus'] ?>");
    e.focus();
    e.select();
<?
}

?>

// --></script>

<?php
echo $badge->render();
?>

</div><!-- badge_pane -->

<?php
$page_html = ob_get_contents();
ob_end_clean();

// --- right sidebar (preview) html

function render_right_sidebar() {
    ob_start();
	?>

<div class="badge_pane">
<h2><?= __("Preview") ?></h2>

<div id="badge_preview" style="margin-bottom: 1em">
</div><!-- badge_preview -->
</div><!-- badge_pane -->

<?php
    $preview_html = ob_get_contents();
    ob_end_clean();
    return $preview_html;
}

// --- left sidebar (debugging)

function render_debug_sidebar() {
    global $user;
    ob_start();
    echo "<p>badges: "; var_dump($user->list_widgets()); echo "</p>";
    try {
	$default_badge = $user->load_widget("default");
	echo "<p>default badge: "; var_dump($default_badge); echo "</p>";
    } catch (PAException $e) {
	echo "<p>no default badge available.</p>";
    }
    $debug_sidebar = ob_get_contents();
    ob_end_clean();
    return $debug_sidebar;
}

// ---
$page = new PageRenderer(NULL, PAGE_BADGE_CREATE, sprintf(__("%s - My Widgets - %s"), $login_user->get_name(), PA::$network_info->name), "container_three_column.tpl", 'header_user.tpl');
$page->onload = "badge.update();";
$page->add_header_css(PA::$theme_url . "/badge_create.css");

$page->add_module("left", "top", render_left_sidebar());
//$page->add_module("left", "top", render_debug_sidebar()); // debugging
$page->add_module("middle", "top", $page_html);
$page->add_module("right", "top", render_right_sidebar());

uihelper_set_user_heading($page);
echo $page->render();

?>