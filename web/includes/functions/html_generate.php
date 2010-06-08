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
require_once "api/PageRenderer/PageRenderer.php";//FIX ME
// this function forms <script> links to javascript within the current
// theme directory.
// {{{ js_includes($file)
/**
 * DEPRECATED: call js_includes() to get just jquery+base_javascript
 *
 * Call js_includes("anyfile.js") to get a JS
 * in the current theme path/javascript/anyfile.js
 *
 * Note: this method changed its behaviour as of 2007-04-19 (PA v1.2pre3).
 * Most significat change is also that this method *always* requires input parameter
 * and the input parameter should always be JS file name only.
 *
 * @param string $file contains JS files name. ie. js_includes("anyfile.js") gets JS in the current
 * /theme/path/javascript/anyfile.js
 * @param boolean $optimize you can force optimizer to not work for selected files by setting this
 * to FALSE. Default TRUE
 * @since 1.2pre3
 */
function js_includes($file, $optimize = true) {
    global $js_includes;
    global $js_includes_dont_optimize;
    if (!isset($js_includes)) {
        $js_includes = array();
    }
    $path = PA::$theme_url.DIRECTORY_SEPARATOR.'javascript'.DIRECTORY_SEPARATOR;
    $file = trim($file);
    $sanity_check = explode(DIRECTORY_SEPARATOR, $file);
    if (1 < count($sanity_check)) {
        $file = array_pop($sanity_check);
        $path .= implode(DIRECTORY_SEPARATOR, $sanity_check);
        $path .= DIRECTORY_SEPARATOR;
    }
    $js_includes[$path][$file] = $file;
    if (false === $optimize) {
        $js_includes_dont_optimize[$path][$file] = $file;
    }
    return '';
}
// }}}
function build_rsd_link_rel($blogid) {
  // global var $_base_url has been removed - please, use PA::$url static variable

  return '
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.PA::$url .'/rsd.php?blogid='.urlencode($blogid).'" />';
}

/**
This function prints html header with a link to style.css of a theme
**/

function html_header($title='', $optional_arguements='', $style_css='') {
  global $use_theme;

  echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:fb=\"http://www.facebook.com/2008/fbml\">\n";
  echo "  <head>\n";
  echo "    <title>\n";
  echo "      $title \n";
  echo "    </title>\n";
  if ($style_css) {
  echo $style_css;
  }
  else {
    if( empty($use_theme) ) {    // TODO: Remove this when new UI is completely independent
      echo "    <link rel=\"stylesheet\" href=\"" . PA::$theme_url . "/style.css\" type=\"text/css\" />\n";
    }
  }

  echo "<link rel=\"shortcut icon\" href=\"". PA::$url . "/favicon.ico\" type=\"image/x-icon\" />";
  
  echo $optional_arguements;
  echo "  </head>\n";
}

function html_body($optional_parameters='') {
  // global var $_base_url has been removed - please, use PA::$url static variable

  //$bgcolor="#ccccca";
  //$bgcolor="#333333";
  echo "<body $optional_parameters>\n";
  echo "\n";
}

/**
This function prints html footer
**/
function html_footer() {
  echo "</html>\n";
  ob_end_flush();
}

function exception_handler($exception) {

  // clean out any buffering so we can write straight to the client
  while (@ob_end_clean());

  try {

    while ($exception->getCode() == 100 && strpos($exception->getMessage(), "no such table") != -1) {
      // See if the database hasn't been populated.

      // (Note: we use 'while' here rather than 'if' so we can use break
      // to avoid this turning into a mess of nested blocks).

      // First, make sure we have a working database connection.
      try {
	$sth = Dal::query("SHOW TABLES");
      } catch (PAException $e) {
	// The database connection isn't working - so fall through to
	// the normal error handler.
	break;
      }

      // Now run through the results and see if we can find a familiar
      // table.
      $found = 0;
      while ($r = $sth->fetchRow()) {
	if ($r[0] == "page_settings") { $found = 1; break; }
      }
      if ($found) {
	// ok, the db *has* been populated - fall through
	break;
      }

      // If we get this far, it means that the DB isn't populated, so we
      // show a message to the user (who is presumably an admin,
      // installing the system).
      // global var $path_prefix has been removed - please, use PA::$path static variable
      ?>

    <h1>Database not populated</h1>

    <p>Before you can run PeopleAggregator, you need to populate the database by running the script <code><?php echo PA::$path; ?>/db/PeepAgg.mysql</code> on your database.  You can do it in the MySQL console like this:</p>

    <pre><i>user</i>@<i>server</i>:<?php echo PA::$path ?>$ <b>mysql -u <i>username</i> -p</b>
Enter password: <b><i>password</i></b>

Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 63048 to server version: 4.1.14-Debian_6-log

Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

mysql> <b>use paalpha</b>
Database changed
mysql> <b>source <?php echo PA::$path ?>/db/PeepAgg.mysql</b></pre>

      <?php

      exit;
    }
    // render an error message
    $code_esc = intval($exception->getCode());
    $msg_esc = htmlspecialchars($exception->getMessage());
    $traceback = $exception->getTraceAsString();
    $template_file = getShadowedPath(PA::$theme_path . '/exception.tpl');
    $template = & new Template($template_file);
    $template->set('code_esc', $code_esc);
    $template->set('msg_esc', $msg_esc);
    $template->set('traceback', $traceback);
    echo $template->fetch();
/*
    $page = new PageRenderer(NULL, NULL, "Error $code_esc: $msg_esc", "container_one_column.tpl");
    $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/error_middle.tpl");
    $msg_tpl->set('code', $code_esc);
    $msg_tpl->set('msg', $msg_esc);
    $page->add_module("middle", "top", $msg_tpl->fetch());

    $css_path = PA::$theme_url . '/layout.css';
    $page->add_header_css($css_path);
    $css_path = PA::$theme_url . '/network_skin.css';
    $page->add_header_css($css_path);
    $page->header->set('navigation_links', null);//setting the links to null
    echo $page->render();
*/
    // write a copy into the log
    Logger::log("An exception occurred: code ".$exception->getCode().", message ".$exception->getMessage()."\nLast error: ".var_export(error_get_last(), TRUE)."\n".$exception->getTraceAsString(), LOGGER_ERROR);

  } catch (Exception $e) {
    // If an error occurred in PageRenderer or something, present a much plainer screen with both errors:
    echo "<h1>Lots of errors occurred!</h1>
<p>An error occurred, then the error handler crashed while trying to handle the error.  Whoops!</p>
<p><b>Here are the details of the original error:</b></p>
<p>".$exception->getMessage()."</p>
<pre>".$exception->getTraceAsString()."</pre>
<p><b>Here are the details of the second error:</b></p>
<p>".$e->getMessage()."</p>
<pre>".$e->getTraceAsString()."</pre>";
  }
  exit;
}

function default_exception() {
  global $debug_for_user;
  if ($debug_for_user == TRUE) {
    set_exception_handler('exception_handler');
  }
}


// common file for edit profile- access perm pages
function uihelper_get_user_access_list($name, $selected, $other_params=NULL) {

  require_once "api/User/User.php";
  $output = "<select name=\"$name\" id=\"$name\"  class=\"select_access\" $other_params>";
  if ($selected == NONE) {
    $output .= '<option value="'.NONE.'" selected="selected">'.__("Myself").'</option>';
  } else {
    $output .= '<option value="'.NONE.'">'.__("Myself").'</option>';
  }

  if ($selected == ANYONE) {
    $output .= '<option value="'.ANYONE.'" selected="selected">'.__("Everyone").'</option>';
  } else {
    $output .= '<option value="'.ANYONE.'">'.__("Everyone").'</option>';
  }

  if ($selected == WITH_IN_DEGREE_1) {
    $output .= '<option value="'.WITH_IN_DEGREE_1.'" selected="selected">'.__("Friends Only").'</option>';
  } else {
    $output .= '<option value="'.WITH_IN_DEGREE_1.'">'.__("Friends Only").'</option>';
  }
	if (empty(PA::$config->no_family)) {
		if ($selected == IN_FAMILY) {
			$output .= '<option value="'.IN_FAMILY.'" selected="selected">'.__("In Family").'</option>';
		} else {
			$output .= '<option value="'.IN_FAMILY.'">'.__("In Family").'</option>';
		}
	}

  $output .= "</select>";
  return $output;
}

// retuns the html image element with adjusted width and height so that image doesn't get distort

function getimagehtml($image, $width, $height, $attributes="", $image_url="") {

 if (!$image_url) {
  if (!file_exists($image) || !is_file($image)) {
    return;
  }
 }
 $output = NULL;
 $title = NULL;
 if (preg_match('/^http/', $image)) {
 	return '<img src="'.$image.'" class="sb-image" alt="" />';
 }

 $img = getimagesize($image);  // returns actual image attributes
 if ($img[1]) {
   $w = $img[0]; // actual image width
   $h = $img[1]; // actual image height
   $aar = $w / $h; // actual image aspect ratio
   $dar = $width / $height; // desired image aspect ratio

   $output .= '<img src="' . ($image_url ? $image_url : PA::$url."/".$image) . '" alt="' . $title . '" ';
     if ($w <= $width && $h <= $height) {
     $output .= 'width="' . $w . '" height="' . $h . '" ';
   }
   elseif ($aar <= $dar) {
     $output .= 'height="' . ($h > $height ? $height : $h) . '" ';
   }
   elseif ($aar > $dar) {
     $output .= 'width="' . ($w > $width ? $width : $w) . '" ';
   }

   $output .= $attributes . '/>';
   return $output;
 }

}

function apply_style ($condition_left, $condition_right, $css) {
    if($condition_left == $condition_right) {
        echo " ".$css;
    } else {
        echo "";
    }
    return;
}


// common file for edit profile- access perm pages
function get_media_access_list($name, $selected=1) {
  // global var $path_prefix has been removed - please, use PA::$path static variable
  require_once "api/User/User.php";
  $output = "<select name=\"$name\" id=\"$name\"  class=\"select-txt text\" style=\"width:120px;\">";

  if ($selected == NONE) {
    $output .= '<option value="0" selected="selected">Myself</option>';
  }
  else {
    $output .= '<option value="0">Myself</option>';
  }
  if ($selected == ANYONE) {
    $output .= '<option value="1" selected="selected">Everybody</option>';
  }
  else {
    $output .= '<option value="1">Everybody</option>';
  }
  if ($selected == WITH_IN_DEGREE_1) {
    $output .= '<option value="2" selected="selected">Friends Only</option>';
  }
  else {
    $output .= '<option value="2">Friends Only</option>';
  }


  $output .= "</select>";
  return $output;
}
function get_fancy_url_content($cid,$ccid=NULL) {
  // global var $_base_url has been removed - please, use PA::$url static variable

  if ( 1 == FANCY_URL ) {
    $r = PA::$url .'/content/'.$cid.'/'.$ccid;
  } else {
    if ($ccid){
      $optional = '&ccid='.$ccid;
    } else {
      $optional = '';
    }
    $r= PA::$url . PA_ROUTE_CONTENT . '/cid='.$cid.$optional;
  }
  return $r;
}

function get_user_permalink($uid) {
  // global var $_base_url has been removed - please, use PA::$url static variable

  $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $uid;
  return $url;
}

function mothership_info() {
  $m['url'] = BASE_URL_PA . PA_ROUTE_HOME_PAGE;
  //currently the homepage logo is this.. need to change this function if image name changes
  $m['image'] = PA::$theme_url . '/images/pa-logo.gif';
  $m['rel_image'] = PA::$theme_path . "/images/pa-logo.gif";
  $m['name'] = 'PeopleAggregator';
  $m['members'] = User::get_member_count();
  $m['extra'] = array(
      'links' => array(
	  'networks_directory' => BASE_URL_PA .'/networks_home.php',
	  'create_network' => BASE_URL_PA .'/create_network.php',
	  ),
      );
  return $m;
}

function get_network_search_options() {
  $search_options[] = array('caption'=>__('Network name'), 'value'=>'name');
  $search_options[] = array('caption'=>__('Network address'), 'value'=>'address');
  $search_options[] = array('caption'=>__('Network tagline'), 'value'=>'tagline');
  return $search_options;
}

function get_groups_search_options() {
  $search_options[] = array('caption'=>__('Group name'), 'value'=>'title');
  $search_options[] = array('caption'=>__('Group description'), 'value'=>'description');
  $search_options[] = array('caption'=>__('Group tag'), 'value'=>'tags');
  return $search_options;
}

/**
* Function to get the drop down for age search. Dropdown will have the various ranges of ages to be searched.
*/

function get_age_options($field_name, $selected=null) {

  $age_range_options_array = array(''=>__('Select age'), '1-10'=>__('Under 10 years'), '11-20'=>__('11-20 years'), '21-30'=>__('21-30 years'), '31-40'=>__('31-40 years'), '41-50'=>__('41-50 years'), '50'=>__('Over 50 years'));// ranges can be made configurable by network administrator.

  $options = '<select name="'.$field_name.'">'.chr(10);
  if (!empty($age_range_options_array)) {
    foreach ($age_range_options_array as $value => $caption) {
      $selected_string = null;
      if (!is_null($selected) && $value == $selected) {
        $selected_string = ' selected="selected"';
      }
      $options .= '<option value="'.$value.'"'.$selected_string.'>'.$caption.'</option>'.chr(10);
    }
  }
  $options .= '</select>';
  // return $options;
}



// Adding option for tag based search
function get_tag_search_option() {
  $search_options[] = array('caption'=>'Group Tag', 'value'=>'group_tag');
//   $search_options[] = array('caption'=>'Network Tag', 'value'=>'network_tag');
  $search_options[] = array('caption'=>'Member Tag', 'value'=>'user_tag');
  $search_options[] = array('caption'=>'Content', 'value'=>'content_tag');
  return $search_options;
}
function uihelper_generate_select_list($options = NULL, $attr = NULL, $selected = NULL) {
  $str = "<select ";
  if ($attr !=NULL && is_array($attr)) {
    foreach($attr as $at=>$val) {
      $str .= "$at=\"$val\"";
    }
  }
  $str .=">";
  if ($options !=NULL && is_array($options)) {
    foreach($options as $val=>$text) {
      if (is_array($selected) && in_array($val, $selected)) {
      $str .= "<option value=\"$val\" selected=\"selected\">$text</option>";
      }
      else if ($val == $selected) {
      $str .= "<option value=\"$val\" selected=\"selected\">$text</option>";
      } else {
      $str .= "<option value=\"$val\" >$text</option>";
      }
    }
  }
  $str .= "</select>";
  return $str;
}

?>