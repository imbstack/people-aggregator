<?php

/* comment management - mainly for spam deletion.

   phil, 2006-09-07

*/

error_reporting(E_ALL);
ini_set("memory_limit", 1024*1024*32); // get at least 16M

$login_required = TRUE;
include_once("web/includes/page.php");
require_once "web/Administration/Pager.php"; // this should probably be in 'includes'
require_once "ext/Akismet/Akismet.php";

// for mem debugging

function mem_debug($msg) {
  global $last_mem_used;
  $current_mem_used = memory_get_usage();
  $mem_delta = $current_mem_used - (isset($last_mem_used) ? $last_mem_used : 0);
  echo "<p>$msg (mem used ".memory_get_usage()."; delta $mem_delta)</p>";
  $last_mem_used = $current_mem_used;
  flush();
}

// --- main page area

function esc_wbr($s) {
  $len = strlen($s);
  $out = "";
  $blklen = 5;
  for ($i = 0; $i < $len; $i += $blklen) {
    $out .= htmlspecialchars(substr($s, $i, $blklen)) . "<wbr/>";
  }
  return $out;
}

// This could be moved into a BlockModule at some point - if we ever
// want to run it outside of comment_management.php.
function render_main_page_area($user) {

    global  $admin_password;

    $page_url = PA::$url . "/comment_management.php";
    $paging_url = "$page_url?"; // url to pass to the pager object

    $msg = "";

    $path_info = @$_SERVER['PATH_INFO'];

    // see if the user is logged in as an admin
    if ($path_info == "/login") {
      if (@$_REQUEST['admin_password'] == $admin_password) {
	$_SESSION['comment_management_is_admin'] = TRUE;
      } else $msg = "Incorrect password!  Try again...";
    } else if ($path_info == "/logout") {
      $_SESSION['comment_management_is_admin'] = FALSE;
      $msg = "You are now logged out (of admin mode).";
    }
    $is_admin = @$_SESSION['comment_management_is_admin'];

    $limit_set = NULL; // set this to an array with keys 'comment_id' to limit display to those keys
    $current_search_terms = NULL; // current search terms

    switch ($path_info) {
    case '/analyze_comment':
      $comment_id = (int)@$_REQUEST['comment'];
      if (!$is_admin) $msg = "Sorry, only administrators can analyze comments at the moment :(";
      elseif ($comment_id) {

	$cmt = new Comment();
	$cmt->load($comment_id);
	$cmt->index_spam_domains();

	$msg = "<p>Analysis of comment $comment_id:</p><hr/><p>".nl2br(htmlspecialchars($cmt->comment))."</p><hr/><ul>";
	$hosts = $cmt->get_link_hosts();

	foreach ($hosts as $domain => $links) {
	  $msg .= "<li><b>".htmlspecialchars($domain)."</b> (<a href=\"$page_url/analyze_domain?domain=".htmlspecialchars($domain)."\">analyze</a>): ";
	  $dom = new SpamDomain($domain);
	  if ($dom->blacklisted) $msg .= " BLACKLISTED";
	  $msg .= "<ul>";
	  foreach ($links as $link) {
	    list($url, $linktexts) = $link;
	    $msg .= "<li>".htmlspecialchars($url)." -> ".implode(" | ", array_map("htmlspecialchars", $linktexts))."</li>";
	  }
	  $msg .= "</ul></li>";
	}
	$msg .= "</ul><hr/>";

      }
      break;

    case '/search':
      $current_search_terms = @$_REQUEST['q'];
      if (!$is_admin) $msg = "Sorry, only administrators can search comments at the moment :(";
      elseif ($current_search_terms) {
	$paging_url = "$page_url/search?q=".urlencode($current_search_terms)."&";
	$limit_set = Comment::search($current_search_terms);
      }
      break;

    case '/stats':
      $msg = "<p>Stats:</p>";
      list($n) = Dal::query_one("SELECT COUNT(*) FROM {comments}");
      list($n_deleted) = Dal::query_one("SELECT COUNT(*) FROM {comments} WHERE is_active=0");
      $n_active = $n - $n_deleted;
      $msg .= "<li>$n comments ($n_active active / $n_deleted deleted)</li>";
      list($n_ham) = Dal::query_one("SELECT COUNT(*) FROM {comments} WHERE is_active=1 AND spam_state=0");
      $n_spam = $n_active - $n_ham;
      $msg .= "<li>$n_spam active+spam / $n_ham active+not spam</li>";
      list($n_no_class) = Dal::query_one("SELECT COUNT(*) FROM {comments} WHERE is_active=1 AND akismet_spam IS NULL");
      $msg .= "<li>$n_no_class active comments not (yet?) classified by Akismet</li>";
      list($n_akismet_del) = Dal::query_one("SELECT COUNT(*) FROM {comments} WHERE is_active=0 AND akismet_spam=1");
      $msg .= "<li>$n_akismet_del comments flagged as spam by akismet and deleted</li>";
      break;

    case '/add_spam_term':
      $spam_term = @$_REQUEST['term'];
      if (!$is_admin) $msg = "Sorry, only administrators can add spam terms at the moment.";
      elseif ($spam_term) {
	// find the comments
	$matches = Comment::search($spam_term);
	$n_deleted = count($matches);
	// add the term
	Comment::add_spam_term($spam_term);
	// and delete the comments
	$blk_size = 1000;
	$F_fetch_ids = create_function('$item', 'return $item["comment_id"];');
	for ($i = 0; $i < count($matches); $i += $blk_size) {
	  Comment::set_spam_state(array_map($F_fetch_ids, array_slice($matches, $i, $blk_size)), SPAM_STATE_SPAM_WORDS);
	}
	$msg = "Added <b>".htmlspecialchars($spam_term).'</b> to the spam term database, and deleted '.$n_deleted.' comments containing it.';
      }
      break;

    case '/analyze_domain':
      $domain = @$_REQUEST['domain'];
      if (!$is_admin) $msg = "Sorry, only administrators can analyze domains.";
      else {
	$msg .= "<p>analysis of domain ".htmlspecialchars($domain).":</p><ul>";
	$domain = new SpamDomain($domain);
	foreach ($domain->find_associated_domains() as $r) {
	  $msg .= "<li>".$r['domain']." (".$r['domain_id']."): ".$r['match_count']." matches</li>";
	}
	$msg .= "</ul>";
      }
      break;

    case '/blacklist_domain':
      $domain = @$_REQUEST['domain'];
      if (!$is_admin) $msg = "Sorry, only administrators can blacklist domains.";
      elseif (!trim($domain)) $msg = "Invalid domain";
      else {
	$dom = new SpamDomain($domain);
	$dom->set_blacklisted(DOMAIN_BLACKLISTED_MANUALLY);
	foreach ($dom->find_associated_domains() as $assoc_domain) {
	  SpamDomain::recalculate_link_counts_for_domain_id($assoc_domain['domain_id']);
	}
      }
      // FALL THROUGH TO /common_domains

    case '/common_domains':
      if (!$is_admin) $msg = "Sorry, only administrators can do this.";
      else {
	list($total_domains, $total_blacklisted_domains) = SpamDomain::count_domains();
	$msg .= "<p>Most common domains (out of total $total_domains, $total_blacklisted_domains blacklisted) in comments:</p><ul>";
	foreach (SpamDomain::get_most_common_domains() as $dom) {
	  $msg .= "<li>".$dom['active_count']." times: ".$dom['domain'].' '.($dom['blacklisted'] ? 'BLACKLISTED' : '').' (<a href="'.$page_url.'/blacklist_domain?domain='.$dom['domain'].'">blacklist domain</a> | <a href="'.$page_url.'/analyze_domain?domain='.$dom['domain'].'">analyze domain</a>)</li>';
	}
	$msg .= "</ul>";
      }
      break;

    case '/akismet_verify_key':
      global $akismet_key;
      if (!$is_admin) $msg = "Sorry, only administrators can access Akismet at the moment.";
      elseif (!$akismet_key) {
	$msg .= '<p>No Akismet key has been configured - Akismet is not active.</p>';
      } else {
	// global var $_base_url has been removed - please, use PA::$url static variable

	$msg .= "<p>verifying akismet key: $akismet_key</p>";
	$ak = new Akismet($akismet_key);
	$msg .= "<p>result: ".var_export($ak->verify_key(PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id), TRUE)."</p>";
      }
      break;

    case '/akismet_check_spam':
      if (!$is_admin) $msg = "Sorry, only administrators can access Akismet at the moment.";
      else {
	global $akismet_key;
	$msg .= "<p>checking comment for spam</p>";
	$cmt = new Comment();
	try {
	  $cmt->load((int)$_REQUEST['comment']);
	} catch (PAException $e) {
	  if ($e->getCode() != COMMENT_NOT_EXIST) throw $e;
	  $msg .= "<p>Comment already deleted.</p>";
	  break;
	}
	$cmt->akismet_check();
	$msg .= "<p>result: ".var_export($cmt->akismet_spam, TRUE)."</p>";
      }
      break;
      
    default:
      if (preg_match("~^/delete/(\d+)$~", $path_info, $m)) {
	list(, $cid) = $m;
	if (!$is_admin) $msg = "Sorry, only administrators can delete comments at the moment :(";
	else {
	  try {
	    $c = new Comment();
	    $c->load((int)$cid);
	    $c->delete();
	    $msg = "Comment deleted.";
	  } catch (PAException $e) {
	    if ($e->code == COMMENT_NOT_EXIST) {
	      $msg = "Comment already deleted.";
	    } else throw $e;
	  }
	}
      }
    }

    $per_page = 20; // how many comments to show on a page

    // paging
    if ($limit_set !== NULL) {
      $total_comments = count($limit_set);
    } else {
      $total_comments = Comment::count_all_comments($is_admin ? 0 : $user->user_id);
    }
    $pager = new pager($total_comments, $per_page, $paging_url);
    $paging = $pager->getButList(8) . " (total $total_comments comments)";

    // main comment list
    if ($limit_set !== NULL) {
      $show_start = max(0, min(($pager->page - 1) * $per_page, $total_comments));
      $show_count = min($per_page, $total_comments - $show_start);
      $limit_set_ids = array_map(create_function('$item', 'return $item["comment_id"];'),
				 array_slice($limit_set, $show_start, $show_count));
      $cmts = Comment::get_selected($limit_set_ids);
    } else {
      $cmts = Comment::get_all_comments($is_admin ? 0 : $user->user_id, $per_page, $pager->page);
    }
    $comments = "";
    foreach ($cmts as $cmt) {
      //      $comments .= "<li>".htmlspecialchars(var_export($cmt, TRUE))."</li>";
      $akismet_result = $cmt['akismet_spam'] ? "spam" : "?";
      $comments .= "<tr><td>".$cmt['comment_id']."</td><td>"
	.$cmt['content_id']."</td><td>"
	.esc_wbr($cmt['name'])."</td><td>"
	.esc_wbr($cmt['email'])."</td><td>"
	.esc_wbr($cmt['homepage'])."</td><td>"
	.esc_wbr($cmt['subject'])."</td><td>"
	.esc_wbr($cmt['comment'])." $akismet_result <a href=\"$page_url/analyze_comment?comment=".$cmt['comment_id']."\">analyze</a></td><td>"
	.esc_wbr($cmt['ip_addr'])."</td><td>"
	.'<form method="POST" action="'.PA::$url .'/comment_management.php/delete/'.$cmt['comment_id'].'?page='.$pager->page.'"><input type="submit" value="X"></form> <a href="'.$page_url.'/akismet_check_spam?comment='.$cmt['comment_id'].'">ak</a></td></tr>';
    }

    if ($is_admin) {

      if ($current_search_terms) {
	$current_search = '<form method="POST" action="'.$page_url.'/add_spam_term"><p>Currently displaying results for: <b>'.htmlspecialchars($current_search_terms).'</b>. <a href="'.$page_url.'">Show all comments</a>.  <input type="hidden" name="term" value="'.htmlspecialchars($current_search_terms).'"><input type="submit" value="Blacklist this term"></p></form>';
      } else $current_search = "";

      $your_permissions = <<<EOS
	<form method="POST" action="$page_url/logout"><p>You are an administrator, so all comments in the site will be displayed.  <input type="submit" value="Log out"></p></form>

	<p><a href="$page_url/akismet_verify_key">Verify Akismet key</a> | <a href="$page_url/common_domains">Show most common domains</a> | <a href="$page_url/stats">Spam statistics</a></p>

	<form method="GET" action="$page_url/search"><p>Search comment content: <input type="text" id="search_q" name="q" size="20"><input type="submit" value="Search"/></p></form>
	<script language="javascript"><!--
	    document.getElementById("search_q").focus();
        // --></script>
        $current_search
EOS;
    } else {
      $your_permissions = <<<EOS
<p>Showing comments on your blog and groups for which you are moderator.</p>

<form method="POST" action="$page_url/login"><p>Or enter the admin password here to adminster the whole site: <input type="password" name="admin_password" size="20"/><input type="submit" value="Log in"/></p></form>
EOS;
    }

    $page_title = "Manage comments";
    global $akismet_key;
    if ($akismet_key) $page_title .= " (Akismet active)"; else $page_title .= " (Akismet not configured)";
    
    $page_html = <<<EOS
<div class="pane comment_manage_pane">

<h1>$page_title</h1>

<div id="msg" class="fade">$msg</div>

$your_permissions

<p>$paging</p>

<table class="bulk_comment_summary"><tr>
<td>ID</td>
<td>Post</td>
<td>Name</td>
<td>Email</td>
<td>Website</td>
<td>Subject</td>
<td>Comment</td>
<td>IP</td>
<td>X</td>
</tr>
$comments
</table>

</div><!-- comment_manage_pane -->
EOS;
    return $page_html;
}

// ---

$user = new User();
$user->load($login_uid);

$page = new PageRenderer(NULL, PAGE_COMMENT_MANAGEMENT, "Manage comments", "container_one_column.tpl", "header.tpl");
$page->add_header_js("fat.js");
$page->add_header_css("$current_theme_path/comment_management.css");

$page->add_module("middle", "top", render_main_page_area($user));

uihelper_set_user_heading($page);
echo $page->render();


?>