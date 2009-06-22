<?php
// make sure we can return to ourselves with all parameters intact
function self_url ($extra_query='') {
  // global var $_base_url has been removed - please, use PA::$url static variable

  $s = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; 
  // PA::$url ."/login.php".$path_info;
  if($extra_query != '') {
    $s .= (strpos('?',$s)) ? '&' : '?';
    $s .= $extra_query;  
  }
  return $s;
}

function get_proper_post() {
  // get the raw POST header
  $ph = fopen("php://input", "rb");
  while (!feof($ph)) {
    $p .= fread($ph, 4096);
  }
  fclose($ph);
  // process it like a query string
  $tmp = explode("&", $p);
  while (list($k1, $v1) = each($tmp)) {
    $nv = explode("=", $v1);
    $name = urldecode(array_shift($nv));
    $thevalue = urldecode(join("", $nv));
    // This portion created a multidimensional array when its needed
    if ($post[$name]) {
      if (is_array($post[$name])) {
        // there's been others of this name
        $post[$name][] = $thevalue;
      } else {
        // there was one previous of this name
        $value = $post[$name];
        unset($post[$name]);
        $post[$name][] = $value;
        $post[$name][] = $thevalue;
      }
  } else {
      $post[$name] = $thevalue;
    }
  }
  return $post; 
}

?>