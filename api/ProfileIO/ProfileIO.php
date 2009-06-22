<?php
require_once dirname(__FILE__)."/../../config.inc";
require_once "api/Logger/Logger.php";
/* Classes for Profile(Meta)Data Import and Export
*  Martin Spernau 2006-08-03
*/

class Normalizer extends XSLTProcessor 
{
  function __construct($inputFormat) {
    Logger::log("Enter: creating new Normalizer class");
    // no need to call parent::__construct
    
    $xslfile = null;
    if(file_exists(PA::$project_dir."/api/ProfileIO/xsl/".$inputFormat.".xsl")) {
      $xslfile = PA::$project_dir."/api/ProfileIO/xsl/".$inputFormat.".xsl";
    } else if(file_exists(PA::$core_dir."/api/ProfileIO/xsl/".$inputFormat.".xsl")) {
      $xslfile = PA::$core_dir."/api/ProfileIO/xsl/".$inputFormat.".xsl";
    }
    if($xslfile) {
      $stylesheet = DOMDocument::load($xslfile);
      $this->importStylesheet($stylesheet);
    } else {
      throw new PAException(FILE_NOT_FOUND, 
        "couldn't read tranformation stylesheet: $xslfile");
    }
    Logger::log("Exit: creating new Normalizer class");
  }
  
  public function setParams($params, $value, $namspace='') {
  // conveience function to batch set a series of XSLT parameters
  // all to the same value (true/false etc)
    if (is_string($params)) $params= array($params);
    foreach($params as $param) {
      $this->setParameter($namspace,$param,$value);
    }
  }

}

class ProfileMerger {
  // basic workflow:
  // load the existing profile
  // DIFF it with the additional/new data 
  // (which has been normalized to the PA format)
  // save the diff
  
  public $user = NULL;
  public $addProfileDOM = NULL;
  public $newProfileDOM = NULL;
  public $diffProfileSXML = NULL;
  public $namedSections = 
    array("general"=>1,"personal"=>2,"professional"=>3, "basic"=>4);
    // see constants in User.php
  public $userSections = NULL;
  public $coreFields = 
    array("last_name","first_name","email","picture");
    // editable fields in the core profile
  public $currentProfileDOM = NULL;

  function __construct($user, $addProfileDOM) {
    Logger::log("Enter: creating new Merger class");
    $this->user = $user;
    // set up the proper list of sections for this user
    $this->userSections = $this->namedSections;
    // get and prepare the list of sections
    $us = User::list_availeable_profile_sections($user->user_id);
    $s = array_values($this->namedSections);
    foreach ($us as $sec) {
      if(!in_array($sec, $s)) {
        $this->userSections[$sec] = $sec;
      }
    }
    $this->addProfileDOM = $addProfileDOM;
    // load the existing profile
    $this->currentProfileDOM = 
      $this->loadDOM();
    Logger::log("Exit: creating new Merger class");    
  }
  
  public function saveProfile($extraSection=NULL) {
    // here is where we actually write the data to DB
    Logger::log("Enter: Merger::saveProfile()");

    // we save the DIFF 
    $dp = $this->diffProfileSXML;
    // get the core profile fields:
    $core = $dp->xpath("//field[@section='core']");
    foreach ($core as $c) {
      if (in_array($c['name'], $this->coreFields)) {
        $this->user->{$c['name']} = (string)$c['value'];
      }
    }
    
    // save the core profile
    try {
      $this->user->save();
    } catch (PAException $e) {
      // we stop processing here
      return $e->message;
    }
    
    // add in the extra section (new)
    $sections = $this->userSections;
    if($extraSection) {
      $sections[$extraSection] = $extraSection; 
    }
    // save the extended profile sections
    foreach($sections as $sec=>$type) {
      $data = $dp->xpath("//field[@section='$sec']");
      if (count($data)) {
        $this->save_ext_profile($data, $type);
      }
    }
    
    // code to save/update relations
    // delete all external relations for this section (=network)
    Relation::delete_relations_of_network($this->user->user_id, $extraSection);
    // add those that are in the profileDiff
    foreach($dp->xpath("//relation[@network='$extraSection']") as $r) {
        // save this relation to the DB
        Relation::add_relation(
          $this->user->user_id, -1, 2, $extraSection, 
          $r['network_uid'], $r['display_name'],
          $r['thumbnail_url'], $r['profile_url']);
    }
    
    Logger::log("Exit: Merger::saveProfile()");
  }
  
  function save_ext_profile($data, $type) {
    $uid = $this->user->user_id;
    for ($i=0; $i<count($data); $i++) {
      $array_user_data[$i]['uid'] = $uid;
      $array_user_data[$i]['name'] = (string)$data[$i]['name'];
      if ($data[$i]['value']) {
        $array_user_data[$i]['value'] = (string)$data[$i]['value'];
      } else {
        // this is a compund value (XML) 
        $xml = "";
        foreach ($data[$i]->children() as $c) {
          $xml .= $c->asXML();
        }
        $array_user_data[$i]['value'] = $xml;
      }
      $array_user_data[$i]['perm'] = (int)$data[$i]['perm'];
      $array_user_data[$i]['type'] = $type;
    }
    try {
      $this->user->save_user_profile($array_user_data, $type);
    } catch (PAException $e) {
      throw new PAException( $e );
      return false;
    }
    return true;
  }
  
  
  public function diff() {
    Logger::log("Enter: Merger::diff()");
    // diff $currentProfileDOM and $addProfileDOM
    // returns $diffProfileDOM: which fields would be ADDED or UPDATED
    
    // use the current profile's DOM via SimpleXML for easy access
    $currProf = simplexml_import_dom($this->currentProfileDOM);
    $diffProf = simplexml_import_dom($this->addProfileDOM);
    foreach($diffProf->field as $field) {
      // "//field[@section='core'][@name='first_name']"
      $sec = $field['section'];
      $name = $field['name'];
      $xp = "//field[@section='$sec'][@name='$name']";
      $cpa = $currProf->xpath($xp);
      if ($cpa) {
        $field['action'] = 'update';
        // the cast to string is important here
        // PHP crashes otherwise
        $field['oldvalue'] = 
          htmlspecialchars((string)$cpa[0]['value']); 
        // we need to preserve the permissions!
        $field['perm'] = 
          (int)$cpa[0]['perm']; 
      } else {
        $field['perm'] = 1; // default permission
        $field['action'] = 'create';
      }
    }
    $this->diffProfileSXML = $diffProf;
    Logger::log("Exit: Merger::diff()");  
  }
  
  
  public function loadProfileData() {
    // load the profile data for the sections this user has
    foreach($this->userSections as $sectionName=>$section) {
      $this->user->{$sectionName} = 
        User::load_profile_section($this->user->user_id, $section);
    }
  }

  public function loadRelations() {
    // load all this users relations
    $this->user->{'local_relations'} = 
      Relation::get_all_relations($this->user->user_id);
    $this->user->{'external_relations'} = 
      Relation::get_external_relations($this->user->user_id);
  }
  
  public function loadDOM() {
    // load the COMPLETE profile of user
    $this->loadProfileData();
    $this->loadRelations();
    
    // make a DOM of this
    $pdom = new DOMDocument();
    $profile = $pdom->appendChild($pdom->createElement("profile"));
    // and now the real fun beginns :)
    foreach($this->user as $k=>$v) {
      if(in_array($k, $this->coreFields) ) 
      {
        $node = $pdom->createElement("field");
        $node->setAttribute('name', $k);
        $node->setAttribute('value', (string)$v);
        $node->setAttribute('section', 'core');
        $node->setAttribute('perm', 1); // always public
        $profile->appendChild($node);
      } else if (
        in_array($k, array_keys($this->userSections))
        && is_array($v) ) 
      {
        foreach($v as $f=>$fd) {
          // if we have a seq, we have an array with numerical indices, 
          // otherwise we have keys like 'value' etc
          // in that case we pack it into a one elemnt array
          // so the below code can be the same for all cases
          $fda = array();
          if (isset($fd['name'])) {
            $fda = array( $fd );
          } else {
            $fda = $fd;
          }
          foreach ($fda as $fi=>$fd) {
            $node = $pdom->createElement("field");
            $node->setAttribute('section', $k);
            $node->setAttribute('name', $f);
            // ok, we might have some XML here
            $fdval = $fd['value'];
            if(preg_match('/^</', $fdval)) {
              // import it as childNodes
              try {
                if(! preg_match('/^<root>/', $fdval)) {
                  $fdval = "<root>".$fdval."</root>";
                }
                $frag = @DOMDocument::loadXML($fdval);
              } catch (PAException $e) {
                Logger::log("Problematic XML: ".$fdval);
                // go ahead and use it as string anyway
                $node->setAttribute('value', $fdval);
              }
              
              if($frag) {
                foreach ($frag->childNodes as $cn) {
                  $fragnode = $pdom->importNode($cn, TRUE);
                  $node->appendChild($fragnode);
                }
              }
            } else {
              $node->setAttribute('value', $fdval);
            }
            $node->setAttribute('perm', $fd['perm']);
            $node->setAttribute('seq', $fd['seq']);
            $profile->appendChild($node);          
          }
        }
      } else if (
        preg_match('/_relations/', $k)
        && is_array($v) ) 
      {
        foreach($v as $r) { 
          $node = $pdom->createElement("relation");
          $node->setAttribute('network', 
            ($r['network']) ? $r['network'] : 'internal'
            );
          $node->setAttribute('network_uid', $r['network_uid']);
          $node->setAttribute('display_name', $r['display_name']);
          $node->setAttribute('thumbnail_url', $r['picture']);
          $node->setAttribute('profile_url', $r['user_id']);
          $profile->appendChild($node);

        }
      }
    }
    return $pdom;
  }
  
  function load_ext_profile($slot=4) {
    $uid = $this->user->user_id;
    $user_profile = User::load_user_profile($uid, $uid, $slot);
    return $user_profile;
  }

}
?>