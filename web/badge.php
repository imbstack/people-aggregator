<?php

/*

PeopleAggregator sidebar widget display.

Renders the actual sidebar widget - see widget_create.php for code to configure a widget.

*/

error_reporting(E_ALL);

include dirname(__FILE__)."/../config.inc";
require_once "web/includes/image_resize.php"; // for resizing funcs
require_once "api/Storage/Storage.php";
require_once "api/User/User.php";
require_once "ext/Group/Group.php";
require_once "api/Widget/Widget.php";

/* Select a subset of a user's friends (or other friend-like objects)
   to display.

   if $rel_show_what=='all':
     $rel_incl = friends to put at the front of the output
   else:
     $rel_incl = friends to include in the output

   $rel = array of all of the user's friend IDs.  e.g. array(42,
   89763, 123);

   $input_format = 'ids' if input is as described above, or 'objects'

   $n_select = how many items to select.

   Returns: array of friend IDs to show.

*/
function select_random_subset($rel_incl, $rel_show_what, $rel, $input_format='ids', $n_select=8) {
    if ($n_select < 2) throw new PAException(OPERATION_NOT_PERMITTED, "select_random_subset() called with \$n_select < 2");
    /* This isn't as efficient as it could be, but we need to make
       sure we're not including people who aren't friends any longer,
       etc., so we shuffle the array, sift out included and
       not-included friends, then merge the two arrays and chop the
       end off. */
    // Randomize array of friends
    shuffle($rel);
    // Sift out included / not-included friends
    $rel_show = array(); $rel_show_two = array();
    foreach ($rel as $rel_info) {
	$rel_id = ($input_format=='ids') ? (int)$rel_info : $rel_info['user_id'];
	if (@$rel_incl[$rel_id]) {
	    $rel_show[] = $rel_info;
	} else {
	    $rel_show_two[] = $rel_info;
	}
    }
    // If we're showing unselected users too, add them in
    if ($rel_show_what == 'all') {
	$rel_show = array_merge($rel_show, $rel_show_two);
    }
    // Now chop the end off
    return array_slice($rel_show, 0, $n_select);
}

function get_profile_info($uid, $section) {
    $prof = array();
    foreach (User::load_user_profile($uid, NULL, $section) as $bits) {
	$prof[$bits['name']] = $bits['value'];
    }
    return $prof;
}

function badge_parse_xml($xml) {
    if (preg_match("/^\s*$/", $xml)) return NULL;
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    return new DOMXPath($dom);
}

//TODO: move this into a template
function badge_render_section($params) {

  // organise input into headings and rows
  $current_section = "";
  $sections = array("" => array());
  foreach ($params as $item) {
    list($k, $v) = $item;
    if ($k == 'heading') {
      $current_section = $v;
      $sections[$v] = array();
    } else {
      $sections[$current_section][] = $v;
    }
  }

  $html = '<table border="0" cellspacing="0">';
  foreach ($sections as $section_name => $items) {
    if ($section_name) {
      $img_path = null;
      $img_leaf = strtolower($section_name)."-logo.png";
      if(file_exists(PA::$project_dir . "/web/images/".$img_leaf)) {
         $img_path = PA::$project_dir . "/web/images/".$img_leaf;
      } else if(file_exists(PA::$core_dir . "/web/images/".$img_leaf)) {
         $img_path = PA::$core_dir . "/web/images/".$img_leaf;
      }
      if($img_path) {
        $sz = getimagesize($img_path);
        $section_name = '<img alt="'.$section_name.'" '.$sz[3].' src="'.PA::$url .'/images/'.$img_leaf.'" />';
      }
      $html .= '<tr><td colspan="2">'.$section_name.'</td></tr>';
    }
    $n = count($items);
    for ($start = 0; $start < $n; $start += 2) {
      $html .= '<tr>';
      for ($pos = $start; $pos < $n && $pos < $start + 2; ++$pos) {
        $html .= '<td>'.$items[$pos].'</td>';
      }
      $html .= '</tr>';
    }
  }
  $html .= "</table>";
  return $html;
}


$path = @$_SERVER['PATH_INFO'];

$html = $error = ""; $format = "js";
$url_params = array();

if (preg_match("~^/(js|jscb|jsdiv|html)/(\d+)/([a-z]+)/([a-zA-Z0-9\-\_\.]+)/(.*)$~", $path, $m)) {
    list($all, $format, $uid, $show_what, $param, $rest_of_url) = $m;
    
    foreach (explode("/", $rest_of_url) as $item) {
	if (!trim($item)) continue;
	list($k, $v) = explode("=", $item, 2);
	$url_params[$k] = preg_replace("~[^a-zA-Z0-9\-\_]~", "_", $v);
    }
    
    $uid = (int)$uid;
    $perpage = (int)$param;
    $page = 1;
    
    $user = new User();
    $user->load($uid);

    $cols = 2;
    $col_width_pix = 90;
    $badge_width_pix = $cols * $col_width_pix;
    //FIXME: If you take out the <wbr> below, it breaks the badge display in IE.  I don't have a clue why,
    // but it seems that we need something between the <div> and the <style> - either text or a tag but
    // not just whitespace.  - Phil
    $html = <<<EOF
<div id="peepagg_badge" class="peepagg_badge">
<wbr /><style type="text/css"><!--
div#peepagg_badge {
 background-color: white;
 width: ${badge_width_pix}px;
 text-align: center;
 padding: 2px 0px 2px 0px;
 border: solid 2px #eeeeee;
}
div#peepagg_badge h4 {
 font-size: 1.5em;
 background-color: #e0e0e0;
}
div#peepagg_badge p, div#peepagg_badge h4 {
 margin: 2px 0px 2px 0px;
}
div#peepagg_badge table tr td {
 width: ${col_width_pix}px;
 text-align: center;
}
div#peepagg_badge table {
 margin: 0 2px 0 2px;
}
div#peepagg_badge table tr td {
 vertical-align: top;
 padding-bottom: 8px;
}
div#peepagg_badge div.badge_person {
 height: 100px;
 width: 78px;
 overflow: hidden;
 margin: 0 1px 0 1px;
 padding: 3px;
 text-align: center;
 background-color: #eeeeee;
}
--></style>
EOF;
	
    if ($show_what == 'show') {
	// new-style sidebar widget
	$badge_tag = $param;
	try {
	    $widget = $user->load_widget($badge_tag);
	    $badge =& $widget->config;
	} catch (PAException $e) {
	    switch ($e->code) {
	    case ROW_DOES_NOT_EXIST:
		$html = "<p>User has not yet created a sidebar widget.</p>";
		break;
	    default:
		throw $e;
	    }
	    $badge = array();
	}

	$profile =& $badge['profile'];
	if (@$profile['enabled']) {
	  $html .= '<h4>About me</h4>';
	  $prof = get_profile_info($user->user_id, BASIC);
	  $html .= '<p><a target="_blank" href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id.'">'.htmlspecialchars($prof['first_name'].' '.$prof['last_name']).'</a></p>';

	  $prof = get_profile_info($user->user_id, "flickr");
	  if (count($prof)) {
	    $html .= '<p><a target="_blank" href="'.htmlspecialchars($prof['photosurl']).'">Flickr: '.htmlspecialchars($prof['username']).'</a></p>';
	  }
	  
	  $prof = get_profile_info($user->user_id, "facebook");
	  if (count($prof)) {
	      $html .= "<p>Facebook</p>";
	      $xp = badge_parse_xml(@$prof['school_info']);
	      if ($xp) {
		  $html .= "<p>School: ".htmlspecialchars($xp->query("name/text()")->item(0)->data.", ".
							  $xp->query("year/text()")->item(0)->data).'</p>';
	      }
	      $xp = badge_parse_xml(@$prof['affiliations']);
	      if ($xp) {
		  $html .= "<p>Affiliations:
			".htmlspecialchars(@$xp->query("name/text()")->item(0)->data." ".
								@$xp->query("status/text()")->item(0)->data.", ".
								@$xp->query("year/text()")->item(0)->data).'</p>';
	      }
	  }
	}

	foreach (array(
		     array("friends", "friends", "internal"),
		     array("friends_ext", "imported friends", "external"),
		     ) as $args) {
	    list ($group_key, $display_name, $rel_scope) = $args;
	    $friends =& $badge[$group_key];
	    if (@$friends['enabled']) {
		$html .= "<h4>My $display_name</h4>";
		
//		$rel = Relation::get_relations($user->user_id);
//		echo "relations: "; var_dump($rel);
		$rel = Relation::get_all_relations($user->user_id, 0, FALSE, 'ALL', 0, 'created', 'DESC', $rel_scope);
//		echo "<br><br>all relations: "; var_dump($rel); echo "<br><br>";
		
		$rel_incl = @$friends['included'];
		$max_friends = 8; // show max 8 friends in the widget
		$rel_show = select_random_subset($rel_incl, $friends['show'], $rel, 'objects', $max_friends);

		// sort by network
		$sorted_friends = array();
		foreach ($rel_show as $rel_info) {
		    $net = &$sorted_friends[$rel_info['network']];
		    if (!isset($net)) $net = array();
		    $net[] = $rel_info;
		    unset($net); // drop reference
		}
		
		$items_html = array();
		foreach ($sorted_friends as $sorted_friend_group) {
		    $n = $sorted_friend_group[0]['network'];
		    if (trim($n)) $items_html[] = array("heading", $n);
//		    $html .= "<div style='float: left'>".$sorted_friend_group[0]['network'].'</div>';
		    foreach ($sorted_friend_group as $rel_info) {
			if ($rel_scope == 'internal') {
			    $person_url_enc = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $rel_info['user_id'];
			    $img = uihelper_resize_mk_user_img($rel_info['picture'], 75, 75);
			} else {
			    $person_url_enc = htmlspecialchars($rel_info['user_id']);
			    $img = '<img border="0" src="'.htmlspecialchars($rel_info['picture']).'" width="75" />';
			}
			$person_name_enc = htmlspecialchars($rel_info['display_name']);
			$item_html = <<<EOS
<div class="badge_person">
<a href="$person_url_enc" target="_blank"><div class="person_image">$img</div><div class="person_name">$person_name_enc</div></a>
</div>
EOS;
			$items_html[] = array("item", $item_html);
		    }
		}
		$html .= badge_render_section($items_html);

	    }
	}

	$groups =& $badge['groups'];
	if (@$groups['enabled']) {
	    $html .= "<h4>My groups</h4>";
	    $grp = Group::get_user_groups($user->user_id, FALSE, 1000, 1);
	    $grp_ids = array();
	    foreach ($grp as $g) $grp_ids[] = $g['gid'];
	    $grp_incl = @$groups['included'];
	    $max_groups = 8; // show 8 groups (this should be configurable)
	    $grp_show = select_random_subset($grp_incl, $groups['show'], $grp_ids, 'ids', $max_groups);
	    $items_html = array();
	    foreach ($grp_show as $grp_id) {
		$group_url_enc = PA::$url . PA_ROUTE_GROUP . "/gid=$grp_id";
		$grp = new Group();
		$grp->load((int)$grp_id);
		$img = uihelper_resize_mk_img($grp->picture, 75, 75, DEFAULT_USER_PHOTO_REL);
		$group_name_enc = htmlspecialchars($grp->title);
		$h = <<<EOS
<div class="badge_person">
<a href="$group_url_enc" target="_blank"><div class="person_image">$img</div><div class="person_name">$group_name_enc</div></a>
</div>
EOS;
		$items_html[] = array("item", $h);
	    }

	    $html .= badge_render_section($items_html);

	}

	$networks =& $badge['networks'];
	if (@$networks['enabled']) {
	    $html .= "<h4>My networks</h4>";
	    $nets = Network::get_user_networks($user->user_id, FALSE, 1000, 1);
	    $net_ids = array(); $net_lookup = array();
	    foreach ($nets as $n) {
		$net_ids[] = $n->network_id;
		$net_lookup[$n->network_id] = $n;
	    }
	    $net_incl = @$networks['included'];
	    $max_networks = 8; // need to make this configurable
	    $net_show = select_random_subset($net_incl, $networks['show'], $net_ids, 'ids', $max_networks);
	    $items_html = array();
	    foreach ($net_show as $net_id) {
		$net = $net_lookup[$net_id];
		$network_url_enc = 'http://'.$net->address.'.'.PA::$domain_suffix.BASE_URL_REL;
		$extra = unserialize($net->extra)  ;
		$img = uihelper_resize_mk_img($net->inner_logo_image, 75, 75, DEFAULT_USER_PHOTO_REL);
		$network_name_enc = htmlspecialchars($net->name);
		$h = <<<EOS
<div class="badge_person">
<a href="$network_url_enc" target="_blank"><div class="person_image">$img</div><div class="person_name">$network_name_enc</div></a>
</div>
EOS;
		$items_html[] = array("item", $h);
	    }

	    $html .= badge_render_section($items_html);

	}

	$posts =& $badge['posts'];
	if (@$posts['enabled']) {
	    $html .= <<<ENS
<h4>My posts</h4>
<ul style="text-align: left; list-style-type: square; padding-left: 2em;">
ENS;
	    
	    // get all recent posts + sb content
	    $entries = Content::load_content_id_array($user->user_id, NULL, FALSE, 8, 1);
	    foreach ($entries as $entry) {
		$url_enc = htmlspecialchars(PA::$url . PA_ROUTE_CONTENT . "/cid=".$entry['content_id']);
		$title_enc = htmlspecialchars($entry['title']);
		$html .= <<<ENS
<li><a target="_blank" href="$url_enc">$title_enc</a></li>
ENS;
	    }

	    $html .= <<<ENS
</ul>
ENS;
	}

	foreach (array(
		     array("images", IMAGE, "image_file", NULL, "image_file", "image", "photos"),
		     array("audio", AUDIO, NULL, "images/audio-icon.gif", "audio_file", "audio", "audio"),
		     array("video", VIDEO, NULL, "images/video-icon.gif", "video_file", "video", "video"),
		     ) as $info) {
	    list($badge_key, $content_type, $thumbnail_key, $icon_file, $filename_key, $mfv_type, $display_name) = $info;
	    $images =& $badge[$badge_key];
	    if (@$images['enabled']) {
		$html .= <<<ENS
<h4>My $display_name</h4>
ENS;

		$recent = Content::get_recent_content_for_user($user->user_id, $content_type, 8);
	    
		$items_html = array();
		foreach ($recent as $obj) {
		    if ($thumbnail_key) {
			     if (strstr($obj[$thumbnail_key], "http://")) {
              $img = '<img src="'.$obj[$thumbnail_key].'" width="75px" height="75px" border="0" />';
            } else {
              $img = uihelper_resize_mk_img($obj[$thumbnail_key], 75, 75);
            }
      
		    } else {
			$img = uihelper_resize_mk_img_static(PA::$theme_rel . "/$icon_file", 75, 75, NULL, "", RESIZE_FIT_NO_EXPAND);
		    }
		    $url_enc = htmlspecialchars(PA::$url . "/media_full_view.php?cid=".$obj['content_id']."&type=".$mfv_type);
		    $title_enc = htmlspecialchars($obj['title']);
		    
		    $h = <<<EOS
<div class="badge_person">
<a href="$url_enc" target="_blank"><div class="person_image">$img</div><div class="person_name">$title_enc</div></a>
</div>
EOS;
		    $items_html[] = array("item", $h);
		}

		$html .= badge_render_section($items_html);
	    }
	}

    } else {
	// old-style friends / groups badge
	$html .= "<p><b><a target=\"_blank\" href=\"". PA::$url . PA_ROUTE_USER_PUBLIC . "/$user->user_id\">$user->first_name $user->last_name</a></b>'s $show_what:</p>";
	$show_all_url = "";

	switch ($show_what) {
	case 'friends':
	    $perpage = (int)$param;
            $relations = Relation::get_all_relations($user->user_id, 0, FALSE, $param, $page);
	    $n = count($relations);
	    $rows = intval($n/3) + ($n % 3 ? 1 : 0);
	    $pos = 0;
	    $html .= "<table>";
	    for ($y = 0; $y < $rows; ++$y) {
		$html .= "<tr>";
		for ($x = 0; $x < $cols && $pos < $n; ++$x, ++$pos) {
		    $html .= '<td valign="bottom">';
		    $rel = $relations[$pos];
		    $html .= '<a target="_blank" href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $rel['user_id'].'">';
		    if ($rel['picture']) {
			$html .= uihelper_resize_mk_user_img($rel['picture'], 50, 50) . "<br/>";
		    }
		    $html .= htmlspecialchars($rel['login_name']).'</a>';
		    $html .= "</td>";
		}
		$html .= "</tr>";
	    }
	    $show_all_url = PA::$url . "/view_all_members.php?view_type=relations&uid=$user->user_id";
	    $html .= "<tr>";
	    for ($i = 0; $i < $cols - 1; ++$i) $html .= '<td style="background-color: white"></td>';
	    $html .= "<td><a target=\"_blank\" href=\"$show_all_url\">show all</a></td></tr>";
	    $html .= "</table>";
	    break;
	case 'groups':
	    $perpage = (int)$param;
	    $html .= "<ul>";
	    $groups = Group::get_user_groups($user->user_id, FALSE, $param, $page);
	    foreach ($groups as $g) {
		$html .= '<li><a target="_blank" href="'.PA::$url . PA_ROUTE_GROUP . '/gid='.$g['gid'].'">'.htmlspecialchars($g['name']).'</a></li>';
	    }
	    $html .= "</ul>";
	    break;
	}
    }

    $html .= '<p><a target="_blank" href="'.PA::$url .'/"><img border="0" src="'.PA::$url .'/images/pa-on-white.png" width="141" height="27" /></a></p>';

    $html .= "</div>";

} else {
    $html = "Error parsing URL ($path).";
}

function js_quote($html) {
  return "'".str_replace("\n", "\\n", str_replace("'", "\\'", $html))."'";
}

switch ($format) {
 case 'js':
     header("Content-Type: application/x-javascript");
     echo "document.write(".js_quote($html).");";
     break;
 case 'jscb':
     header("Content-Type: application/x-javascript");
     $cb = @$url_params['cb'];
     if (!$cb) {
	 echo 'alert("Missing cb parameter to PA JS call!  Check that your PeopleAggregator badge script is correct.");';
     } else {
	 echo "$cb(".js_quote($html).");";
     }
     break;
 case 'jsdiv':
     header("Content-Type: application/x-javascript");
     $div = @$url_params['div'];
     if (!$div) {
	 echo 'alert("Missing div parameter to PA JS call!  Check that your PeopleAggregator badge script is correct.");';
     } else {
	 echo "document.getElementById('$div').innerHTML = ".js_quote($html).";";
     }
     break;
 case 'html':
     echo $html;
     break;
}

?>