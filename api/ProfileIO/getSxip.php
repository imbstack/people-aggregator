<?php
require_once dirname(__FILE__)."/../../config.inc";
ob_start();
$login_required = TRUE;
include_once("web/includes/page.php");
global $network_info;
require_once "api/User/User.php";

// PA includes
require_once "api/Logger/Logger.php";
require_once "web/functions.php";
require_once dirname(__FILE__)."/common.php";

killslashes();
$script_url = self_url();
$_POST = get_proper_post();

// request dispatch
if (isset($_REQUEST['do'])) {
    switch($_REQUEST['do']) {
        case 'discover';
            if (!isset($_POST['dix:/homesite']) or !strlen($_POST['dix:/homesite'])) {
                default_page('Please enter your homesite');
            }
            else {
                require_once 'Net/SXIP/Membersite.php';
                $ms = new Net_SXIP_Membersite();
                $response = $ms->discover($_POST['dix:/homesite']);
                if ($ms->isError($response)) {
                    default_page($response->getMessage());
                }
                else {
                    auto_post_page($response['endpoint']);
                }
            }
            break;
        case 'verify';
            require_once 'Net/SXIP/Membersite.php';
            $ms = new Net_SXIP_Membersite();
            $result = $ms->verify($_POST, $_POST['persona_url']);
            if ($ms->isError($result)) {
                default_page($result->getMessage());
            }
            elseif ($result === null) {
                default_page('The Homesite denied verification');
            }
            else {
                success_page();
            }
            break;
        case 'info':
            phpinfo();
            break;
        default:
            default_page();
            break;
    }
}
else {
    default_page();
}


function default_page ($error='') 
{
    global $script_url, $current_theme_path, $sxip_properties;
    if (isset($_POST['dix:/homesite'])) {
        $homesite = $_POST['dix:/homesite'];
    }
    elseif (isset($_COOKIE['dix:/homesite'])) {
        $homesite = $_COOKIE['dix:/homesite'];
    }
    else {
        $homesite = '';
    }
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>PeopleAggregator SXIP Profile Import</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="shortcut icon" href="<? echo $current_theme_path ?>/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<? echo $current_theme_path ?>/favicon.ico" type="image/x-icon" />
    <script src="<? echo $current_theme_path ?>/javascript/prototype.js" type="text/javascript"></script>
    <link rel="stylesheet" href="<? echo $current_theme_path ?>/style.css" type="text/css" />
</head>
<body style="background-color: white;">
    <image name="dix:/membersite-logo-url" src="<?php echo $current_theme_path; ?>/images/sxipms-logo.jpg" />
    <h1>PeopleAggregator SXIP Profile Import</h1>
<?php if ($error) { ?>
    <div style="margin: 10px;background: #ffe6e6; padding: 5px;">
    <?php echo $error; ?>
    </div>
<?php } ?>
<form class="DIX" method="post" action="?do=discover" accept-charset="utf-8">
    <input type="hidden" name="dix:/message-type" value="dix:/fetch-request" />
    <input type="hidden" name="dix:/membersite-url" value="<?php echo $script_url; ?>?do=verify" />
    <input type="hidden" name="dix:/membersite-path" value="<?php echo $script_url; ?>" />
    <input type="hidden" name="dix:/membersite-name" value="PeopleAggregator SXIP Profile Import" />
    <input type="hidden" name="dix:/membersite-explanation" value="Whci profile fields do you want to import into you PeopleAggregator account?" />
    <input type="hidden" name="dix:/membersite-cancel-url" value="<?php echo $script_url; ?>?do=cancel" />
    <input type="hidden" name="dix:/membersite-logo-url" value="<?php echo $current_theme_path; ?>/images/sxipms-logo.jpg" />
    <!-- No fields are REQUIRED here!
    <input type="hidden" name="dix:/required" value="email" />
    <input type="hidden" name="dix:/required" value="persona_url" />
    <input type="hidden" name="dix:/required" value="login_name" />
    <input type="hidden" name="dix:/required" value="first_name" />
    -->
    <?php
    $sxip = simplexml_load_file("./xml/sxipProperties.xml");
    
    foreach($sxip->property as $prop) {
      echo "<input type=\"hidden\" name=\"";
      echo urlencode($prop['label']);
      echo "\" value=\"";
      echo $prop['name'];
      echo "\" />\n";
    }
    ?>
    <label for="homesite">homesite</label><br />
    <input type="text" size="20" name="dix:/homesite" value="<?php echo $homesite; ?>" id="homesite" class="input_box" />
    <input type="image" alt="sxip in" value="sxip in" src="<?php echo $current_theme_path; ?>/images/sxipin_btn_sq.gif" class="btn_sxip_in" id="sxip in" height="20" width="62" /> 
    </form>
    <?php
    // echo htmlspecialchars(show_property_matrix());
    ?>
</body>
</html>
<?php
}
function auto_post_page($action)
{
    setcookie('dix:/homesite', $_POST['dix:/homesite'], time()+60*60*24*365);
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>One moment please...</title>
    </head>
<body onLoad="document.sxipForm.submit()">
    <form name="sxipForm" method="post" action="<?php echo $action; ?>">
        <?php
    foreach (array_keys($_POST) as $name) {
        if (in_array($name,array('do','dix:/homesite'))) {
            continue;
        }
        $values = is_array($_POST[$name]) ? $_POST[$name] : array($_POST[$name]); 
        foreach ($values as $value) {
        ?>
            <input type="hidden" name="<?php echo htmlentities($name) ?>" value="<?php echo htmlentities($value) ?>" />
        <?php
        }
    }
    ?>
    </form>
</body>
</html>
<?php
}

function success_page()
{
    global $script_url, $current_theme_path, $login_uid;
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>PeopleAggregator SXIP Profile Import</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="shortcut icon" href="<? echo $current_theme_path ?>/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<? echo $current_theme_path ?>/favicon.ico" type="image/x-icon" />
    <script src="<? echo $current_theme_path ?>/javascript/prototype.js" type="text/javascript"></script>
    <link rel="stylesheet" href="<? echo $current_theme_path ?>/style.css" type="text/css" />
    <style>
    .profilediff  {
      background-color: black;
    }
    .profilediff td {
      background-color: white;
      margin:1px;
    }
    .profilediff .action {
      background-color: black;
      color: white;
      font-weight: bold;
      font-size: 1.1em;
    }
    .profilediff .section {
      background-color: lightgrey;
      font-weight: bold;
    }
    </style>
</head>
<body style="background-color: white;">
    <image name="dix:/membersite-logo-url" src="<?php echo $current_theme_path; ?>/images/sxipms-logo.jpg" />
    <h1>PeopleAggregator SXIP Profile Import</h1>
    <h2>The following data was imported into your User Profile</h2>
    <?php
    // load empty sxip properties XML
    $sxip = DOMDocument::load("api/ProfileIO/xml/sxipProperties.xml");
    // stick the POSTed data into our DOM
    foreach($sxip->getElementsByTagName('property') as $prop) {
      $label = urlencode($prop->getAttribute('label'));
      if($_POST[$label] && $_POST[$label] != '' ) {
        $prop->setAttribute('value', $_POST[$label]);
      }
    }
    $homesite = $_REQUEST['dix:/homesite'];
    // echo "<hr><b>Input DOM:</b>
    // <br><pre>".htmlspecialchars($sxip->saveXML())."</pre>";
    
    require_once "api/ProfileIO/ProfileIO.php";
    $normalizer = new Normalizer("sxip");
    $normalizer->setParameter('','nameSection',$homesite);
    $paDOM = $normalizer->transformToDoc($sxip);
// echo "<hr><b>PA DOM:</b> <br><pre>".htmlspecialchars($paDOM->saveXML())."</pre>";

    require_once "api/User/User.php";
    $user = new User();
    try {
      $user->load((int)$login_uid);
    } catch (PAException $e) {
      throw new PAException(USER_INVALID, $e->message);
    }
    
    $merger = new ProfileMerger($user, $paDOM);
    $merger->diff();
    // here we might include another step with user interction!!

    // actualy SAVE the new profile
print "<hr>saving to ".print_r($homesite,true)."<hr>";    
    $merger->saveProfile($homesite);
    

    $diff = $merger->diffProfileSXML;
// echo "<hr><b>PA DOM:</b> <br><pre>".htmlspecialchars($diff->asXML())."</pre>";

    function tr($f) {
    ?>
    <tr>
    <td><b><? echo $f['name']; ?></b>
    </td>
    <td>
    <? echo $f['value']; ?>
    <? if($f['oldvalue']) echo "<br>was: ".$f['oldvalue']; ?>
    </td>
    </tr>
    <?php
    }
    ?>
    <table class=profilediff>
    <?php
    foreach(array('update','create') as $action) {
      ?>
      <tr><td colspan=2 class=action>
        <? echo $action ?>-ing these fields:</td>
      </tr>
      <?php
      $sections = array('core') + array_keys($merger->userSections);
      foreach($sections as $section)
      {
        if ($diff->xpath("//field[@action='$action'][@section='$section']"))
        {
        ?>
          <tr>
            <td colspan=2 class=section>profile section: <? echo $section ?>:</td>
          </tr>
          <?php
          foreach ($diff->xpath("//field[@action='$action'][@section='$section']") 
            as $f) {
            tr($f);
          }
        }
      }
    }
    ?>
    </table>
    <p>
        <a href="<?php echo $script_path; ?>">Start over</a>
    </p>
    <!--
    <pre>
    raw POST data
    <? print_r($_POST); ?>
    </pre>
    -->
</body>
</html>
<?php
}


function show_property_matrix() {
  $pm = property_matrix($user);
  $html = "<h3>Matrix of supported fields from dix://sxip.net/simple#1</h3>";
  foreach($pm as $k=>$v) {
    preg_match("@(\w+)/(\w+)@", $v, $m);
    $pan = $m[2];
    $pas = $m[1];
    $html .= "
  <xsl:when test=\"@name='$k'\">
    <xsl:apply-templates select=\".\">
      <xsl:with-param name=\"name\">$pan</xsl:with-param> 
      <xsl:with-param name=\"section\">$pas</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    ";
  }
  return $html;
}

function property_matrix($user) {
  $prop_match =
  // map dix property names to internal PA user fieldnames
  array(
    "dix://sxip.net/namePerson/first" => "basic/first_name",
    "dix://sxip.net/namePerson/last" => "basic/last_name",
    "dix://sxip.net/namePerson/friendly" => "basic/login_name",
    "dix://sxip.net/contact/internet/email" => "basic/email",
    "dix://sxip.net/media/image/small" => "basic/picture",
    "dix://sxip.net/internet/web/default" => "general/homepage",
    // "dix://sxip.net/contact/web/blog" => "general/homepage",
    "dix://sxip.net/contact/web/Flickr" => "general/flickr",
    "dix://sxip.net/contact/web/Delicious" => "general/delicious",
    "dix://sxip.net/company/name" => "professional/company",
    "dix://sxip.net/company/title" => "professional/title",
    "dix://sxip.net/media/spokenname" => "general/caption"
  );
  return $prop_match;
}
?>