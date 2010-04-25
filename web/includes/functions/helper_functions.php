<?php


if (!function_exists("define_once")) {
  function define_once($k, $v) {
    if (!defined($k)) return define($k, $v);
  }
}

if (!function_exists('property_exists')) {
  function property_exists($class, $property) {
   if (is_object($class))
     $class = get_class($class);
   return array_key_exists($property, get_class_vars($class));
  }
}

function get_svn_version() {
  global $debug_show_svn_version;
	// show svn version at the bottom of each page
	$entries_fn = PA::$path . "/.svn/entries";

	$svn_text = 'svn?';
	if (file_exists($entries_fn)) {
		$f = fopen($entries_fn, "rt");
		$line = trim(fgets($f));
		if (preg_match("/^<"."\?xml/", $line)) {
// pre v1.4 svn entries file
$dom = new DOMDocument;
@$dom->load($entries_fn);
$xp = new DOMXPath($dom);
$xp->registerNamespace("svn", "svn:");
$entries = $xp->query("//svn:entry[1]");
if ($entries->length) {
	$entry = $entries->item(0);
	$url = $xp->query("@url", $entry)->item(0)->nodeValue;
	$rev = $xp->query("@revision", $entry)->item(0)->nodeValue;
	$svn_text = "<a target='_blank' href='$url'>svn r$rev</a>";
} else {
	$svn_text = 'svn xml?';
}
		} elseif (is_numeric($line)) {
// post v1.4 entries file?
fgets($f); fgets($f);
$rev = trim(fgets($f));
$url = trim(fgets($f));
if (is_numeric($rev) && preg_match("/^http/", $url)) {
	$svn_text = "<a target='_blank' href='$url'>svn r$rev</a>";
} else $svn_text = 'svn new?';
		} else {
$svn_text = "svn fmt?";
		}
	}
	return $svn_text;
}
  
function pa_end_of_page_ob_filter($html) {
  // if headers not sent yet, and we don't have a content type specified, send text/html; charset=UTF-8
  if (!headers_sent()) {
    $ct_sent = FALSE;
    foreach (headers_list() as $hdr) {
      if (preg_match("/^Content-Type:/", $hdr)) {
  $ct_sent = TRUE;
      }
    }
    if (!$ct_sent) {
      header("Content-Type: text/html; charset=UTF-8");
    }
  }

  // work out timing
  global $pa_page_render_start;
  $duration = microtime(TRUE) - $pa_page_render_start;
  $eop_text = sprintf("[%.2f s]", $duration);

  global $debug_show_svn_version;
  if ($debug_show_svn_version) {
    $svn_text = get_svn_version();
    $eop_text .= " [$svn_text]";
  }

  $eop_text .= " ".PA::$remote_ip;
  // now drop timing and anything else we want to show at the bottom of the page

  return str_replace("<!--**timing**-->", $eop_text, $html);
}


// set PA::$config->perf_log = "path to performance log" in local_config.inc to turn on
// detailed performance logging - for spam debugging
function pa_log_script_execution_time($at_start=FALSE) {
  global $pa_page_render_start;
  if (!isset(PA::$config->perf_log)) return;

  $post = array();
  foreach ($_POST as $k => $v) $post[] = urlencode($k)."=".urlencode($v);

  $status_map = array(
          0 => "NORMAL",
          1 => "ABORTED",
          2 => "TIMEOUT",
          3 => "ABORTED+TIMEOUT",
          );

  if ($at_start) {
    $msg = sprintf("%d\tstart\t%s\t%s\t%s\t%s\t%s\thttp://%s\t%s\t%s\t%s\t%s\n",
       posix_getpid(),
       PA::datetime(time(), 'long', 'short'), //date("Y-m-d H:i:s"),
       $_SERVER['REMOTE_ADDR'],
       @$_SERVER['HTTP_X_FORWARDED_FOR'],
       $status_map[connection_status()],
       $_SERVER['REQUEST_METHOD'],
       $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
       $_SERVER['HTTP_USER_AGENT'],
       implode("&", $post),
       @$_SESSION['user']['id'],
       @$_SESSION['user']['name']
       );
  } else {
    $msg = sprintf("%d\t%.2f\n",
       posix_getpid(),
       microtime(TRUE) - $pa_page_render_start
       );
  }
  error_log($msg, 3, PA::$config->perf_log);
}

function show_profiler_statistic() {
  if(PA::$profiler) {
    PA::$profiler->done();
    echo PA::$profiler->html;
  }
}


// translate string $s into the current language (specified by PA::$language, which can be changed in local_config.inc).
function __($s) {
	// If we have a translated string, return it.
	global $TRANSLATED_STRINGS;
	if (isset($TRANSLATED_STRINGS[$s]) && !preg_match("/^\s*$/", $TRANSLATED_STRINGS[$s])) {
		return $TRANSLATED_STRINGS[$s];
	}

	// No string found.
	if (defined("PA_LANG_DEBUG")) {
		return "[$s]";
	}

	// Not debugging - just return English string.
	return $s;
}

// translate string and count into the current language.
/* e.g. _n(";You have %d relations.
1;You have one relation.
0;You have no relations yet!", 42) == "You have 42 relations."
*/
function _n($fmt, $n) {
	$n = (int)$n;

	// See if we have a translated string
	global $TRANSLATED_STRINGS;
	if (isset($TRANSLATED_STRINGS[$fmt]) && !preg_match("/^\s*$/", $TRANSLATED_STRINGS[$fmt])) {
		$fmt = $TRANSLATED_STRINGS[$fmt];
		$show_brackets = FALSE;
	} else {
		// Put square brackets around the response if we are debugging - looking for places to translate or strings that need marking
		$show_brackets = defined("PA_LANG_DEBUG");
	}

	$translation = NULL;
	foreach (preg_split("/\n/", $fmt) as $line) {
		$line = trim($line);
		if (!$line) continue;
		if (!preg_match("/^(\d*)(?:\-(\d+))?;(.*)$/", $line, $m)) throw new PAException(GENERAL_SOME_ERROR, "Invalid line format for _n(): '$line'");
		list(, $first, $last, $txt) = $m;
		if ($first === "") {
			// default, e.g. ;You have %d relations
			$translation = $txt;
		} elseif ($last === "") {
			// exact value, e.g. 1;You have one relation
			if ((int)$first == $n) {
				$translation = $txt;
				break;
			}
		} else {
			// range, e.g. 1-3;You have %d relations - go get some more!
			if ((int)$first <= $n && (int)$last >= $n) {
				$translation = $txt;
				break;
			}
		}
	}
	if (!$translation) throw new PAException(GENERAL_SOME_ERROR, "Couldn't find translation for format '$fmt' and number=$n");
	$ret = sprintf($translation, $n);
	if ($show_brackets) {
		return "[$ret]";
	}
	return $ret;
}

function parse_file_size_string($sz) {
	if (preg_match("/^(\d+)([gmk])$/i", $sz, $m)) {
		$num = (int)$m[1];
		$mult = $m[2];

		switch (strtolower($mult)) {
			case 'g': $num *= 1024; // fallthrough
			case 'm': $num *= 1024; // fallthrough
			case 'k': $num *= 1024;
			break;
		}
		return $num;
	}

	// failed to parse as something like '2M' - just force it to return as an integer
	return (int)$sz;
}


function format_file_size($sz) {
	$sz = floatval($sz);
	if ($sz > 500*1024*1024) {
		return sprintf("%.dGB", $sz/1073741824.0);
	}
	if ($sz > 500*1024) {
		return sprintf("%.dMB", $sz/1048576.0);
	}
	if ($sz > 1023) {
		return sprintf("%.dKB", $sz/1024.0);
	}
	return sprintf("%d bytes", $sz);
}


/**
  * @author   Zoran Hron
  * @name     getConstantsByPrefix
  * @brief    This function returns an array of defined constants with given prefix
  * @return   array of defined constants with given prefix or an empty array
  *
  * @example  getConstantsByPrefix("PAGE_");
  */
function getConstantsByPrefix($prefix) {
    $result    = array();
    $constants = get_defined_constants();

    foreach($constants as $key=>$value) {
      if(substr($key,0,strlen($prefix))==$prefix) {
        $result[$key] = $value;
      }
    }
    return $result;
}

function wrap_text($text, $chunks_len, $max_len, $brek_str = " ") {
  if(strlen($text) > $chunks_len) {
    $newtext = wordwrap($text, $chunks_len, "|", true);
    $nb_breaks = substr_count($newtext, "|");
    $newtext_len = strlen($newtext) - $nb_breaks;
    if($newtext_len <= $max_len) {
      $newtext = str_replace("|", $brek_str, $newtext);
      return $newtext;
    } else {
      $str_arr = str_split($newtext);
      for($cnt = $max_len; $cnt > 0; $cnt--) {
        if($str_arr[$cnt] == "|") break;
      }
      $newtext = substr($newtext, 0, $cnt);
      $newtext = str_replace("|", $brek_str, $newtext);
      return $newtext;
    }
  }
  return $text;
}


function abbreviate_text($text, $max_len, $abbr_pos = null, $abbr_str = '..') {
  if(strlen($text) <= $max_len) {
    return $text;
  }
  $max_len++;
  $cut_len = (!is_null($abbr_pos)) ? $abbr_pos : ($max_len - strlen($abbr_str));
  $first_part  = substr($text, 0, $cut_len) . $abbr_str;
  $fp_len = strlen($first_part);
  $second_part = substr($text, $fp_len - $max_len, $max_len - $fp_len);
  return $first_part . $second_part;

}

/**
  * @author   Zoran Hron
  * @name     type_cast
  * @brief    This function convert Object or Array to given object type
  * @return   Object of requested type
  *
  * @example  type_cast($net_info, 'Network');
  */
function type_cast($object_or_array, $new_classname) {
  if(is_array($object_or_array)) {
    $object_or_array = (object)$object_or_array;
  }
  if(class_exists($new_classname)) {
   $old_object = serialize($object_or_array);
   $new_object = 'O:' . strlen($new_classname) . ':"' . $new_classname . '":' . substr($old_object, $old_object[2] + 7);
   return unserialize($new_object);
  }
  else {
    throw new Exception("[helper_functions.php]::type_cast(): Can't typecast, class with name '$new_classname' is undefined.");
  }
}

/**
  * @author   Zoran Hron
  * @name     url_decode_all
  * @brief    This function decode single or array of URL encoded strings
  * @return   URL decoded string or array of strings
  *
  */
function url_decode_all($str_or_array) {
  if(!is_array($str_or_array)) {
     $str_or_array = array($str_or_array);
  }
  foreach($str_or_array as $k => &$v) {
    $v = urldecode($v);
  }
  return $str_or_array;
}

?>
