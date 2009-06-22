<?php

// dynamic_config.php:
//
// This file contains values for configuration
// variables that can't be initialized statically
// and variables that are defined as members
// of $_PA dynamic Class
//
// These values could be overriden in
//     /pa/paproject/web/config/dynamic_config.php
//
// ONLY THE CORE TEAM SHOULD CHANGE THIS FILE.
// Project teams: use project_config.php
// Server administrators: use local_config.php



/** ---------------------------------------------------------------------------------------
*
*   MISC DYNAMIC INITIALIZATION TASKS
*
**/

// default user pictures
define_once('DEFAULT_USER_PHOTO', PA::$theme_url."/images/default.png");
define_once('DEFAULT_USER_PHOTO_REL', PA::$theme_rel."/images/default.png");

// set $_PA->perf_log = "path to performance log" in local_config.inc to turn on
// detailed performance logging - for spam debugging
if (isset($_PA->perf_log)) register_shutdown_function("pa_log_script_execution_time");


// populate GLOBAL var $file_type_info with max. upload file size value
$upload_max_filesize = parse_file_size_string(ini_get("upload_max_filesize"));
foreach ($GLOBALS['file_type_info'] as &$fti) {
 if ($fti['max_file_size'] > $upload_max_filesize) {
   $fti['max_file_size'] = $upload_max_filesize;
 }
}

  
/** ---------------------------------------------------------------------------------------
*
*   PA CLASS DYNAMIC PROPERTIES INITIALIZATION
*
**/
  
// hosts from which we can trust X-Forwarded-For headers (load balancers)
$_PA->trusted_proxies = array('127.0.0.1');

// Set enable_network_spawning to FALSE to disable the creation of new
// networks, without disabling the network directory or any existing
// networks.
$_PA->enable_network_spawning = TRUE;

// Set enable_networks to FALSE to completely disable networks - don't
// allow them to be used at all, and disable spawning and the network
// directory.
$_PA->enable_networks = TRUE;

// Set to TRUE to force all networks to be private, regardless of
// their settings.  This will mean nobody has access to anything until
// they have created an account and logged in.

//TODO: To round this feature off, the following things would be useful:
// - an option to not auto-join users to the home network (to make the home net truly private).
// - remove the 'return to home network' button from request.php if the home network is private (get_home_network()->is_private() == TRUE).
// - an option to join users to the home network on e-mail confirmation (as in Cyama).
//   - in this case, users should be sent to the 're-send e-mail confirmation' page rather than request.php if they are not a member of the network yet.
$_PA->all_networks_are_private = FALSE;

// Enable new storage system
$_PA->use_storage = FALSE;

// Enable fancy URLs
$_PA->enable_fancy_url = TRUE;




/** ---------------------------------------------------------------------------------------
*
*   file uploading
*
**/

// File storage backend: local
$_PA->storage_backend = "local";

// LocalStorage config:
// - where the files are stored
$_PA->local_storage_path = PA::$project_dir . "/web/files";
// - URL to files, relative to /web
$_PA->local_storage_rel_url = "files";


// Default album names
$_PA->default_album_titles = array(
    IMAGE_ALBUM => "My pictures",
    AUDIO_ALBUM => "My audio",
    VIDEO_ALBUM => "My videos",
    );


/** ---------------------------------------------------------------------------------------
*
*   profanity filtering
*
**/

// this is a base list of bad words that will be replaced
// with random strings consisting of the letters "$%@#*"
// use project_config and/or  local_config to add/override this list

// Use file if already exists, otherwise use this default
  if(file_exists(PA::$project_dir . "/web/config/profanity_words.txt")) {
     $_PA->profanity = explode("\r\n", file_get_contents(PA::$project_dir . "/web/config/profanity_words.txt"));
  } else if(file_exists(PA::$core_dir . "/web/config/profanity_words.txt")) {
     $_PA->profanity = explode("\r\n", file_get_contents(PA::$core_dir . "/web/config/profanity_words.txt"));
  } else {
     $_PA->profanity = array( "ass", "asses", "assfuck", "asshole", "assholes", "bastard", "beaner", "bestial", "bestiality", "bitch", "bitcher", "bitchers", "bitches", "bitchin", "bitching", "blowjob", "blowjobs", "bukkake", "butfuck", "butfucker", "buttfarmer", "buttfuck", "buttfucker", "buttsex", "camel jockey", "camel-humper", "camel-jockey", "chink", "clit", "cock", "cocks", "cocksuck", "cocksucked", "cocksucker", "cocksucking", "cocksucks", "coochie", "coon", "coonass", "cornhole", "cum", "cummer", "cumming", "cums", "cumshot", "cunt", "cuntlick", "cuntlicker", "cuntlicking", "cunts", "cyberfuc", "cyberfuck", "cyberfucked", "cyberfucker", "cyberfuckers", "cyberfucking", "damn", "darky", "deepthroat", "dick", "dickface", "dickwad", "dildo", "dildos", "dumbass", "fag", "fagging", "faggot", "faggs", "fagot", "fagots", "fags", "fcuk", "fingerfuck", "fingerfucked", "fingerfucker", "fingerfuckers", "fingerfucking", "fingerfucks", "fistfuck", "fistfucked", "fistfucker", "fistfuckers", "fistfucking", "fistfuckings", "fistfucks", "fuck", "fucked", "fucker", "fuckerage", "fuckeration", "fuckerbitch", "fuckerd", "fuckerdoodle", "fuckerdoodles", "fuckerduck", "fuckered", "fuckeree", "fuckerel", "fuckerer", "fuckerfish", "fuckerfreakerin", "fuckeries", "fuckerific", "fuckering", "fuckerism", "fuckerize", "fuckermagar", "fuckerman", "fuckermit", "fuckermother", "fuckermuncher", "fuckernacle", "fuckernaut", "fuckernickel", "fuckernot", "fuckernutter", "fuckeroni", "fuckeroo", "fuckeroon", "fuckeroonie", "fuckerpants", "fuckers", "fuckers", "fuckerton", "fuckerware", "fuckery", "fucket", "fucketarian", "fucketh thine self", "fucketifuck", "fucketry", "fuckette", "fuckety", "fuckever", "fuckey", "fuckeye", "fuckeyed", "fuckeyes", "fuckface", "fuckfaced", "fuckfacedcunt", "fuckfacehead", "fuckfaceitis", "fuckfaggot", "fuckfarm", "fuckfarm", "fuckfarts", "fuckfase", "fuckfast", "fuckfest", "fuckfinger", "fuckfish", "fuckfist", "fuckflap", "fuckflesh", "fuckfluff", "fuckfolly", "fuckfoot", "fuckfor", "fuckfry", "fuckfuck", "fuckful", "fuckfurter", "fuckhandles", "fuckhappy", "fuckhat", "fuckhead", "fuckheaded", "fuckheadedness", "fuckheadry", "fuckhelmet", "fuckher", "fuckherface", "fuckhole", "fuckhole", "fuckholes", "fuckholio", "fuckhoop", "fuckhorse", "fuckhuge", "fuckidoo", "fuckidy", "fuckie", "fuckieda", "fuckies", "fuckiest", "fuckification", "fuckified", "fuckify", "fuckilarious", "fuckilicious", "fuckily", "fuckilydoodah", "fuckin", "fucking", "fuckings", "fuckme", "fucks", "fuckwad", "fuk", "fuks", "gangbang", "gangbanged", "gangbanging", "gangbangs", "gaysex", "goddamit", "goddamn", "gook", "handjob", "ho", "hoebag", "honkey", "horniest", "horny", "hotsex", "jack-off", "jackoff", "jerk-off", "jerkoff", "jism", "jiz", "jizm", "kike", "kock", "kum", "kummer", "kumming", "kums", "mothafuck", "mothafucka", "mothafuckas", "mothafuckaz", "mothafucked", "mothafucker", "mothafuckers", "mothafuckin", "mothafucking", "mothafuckings", "mothafucks", "motherfuck", "motherfucked", "motherfucker", "motherfuckers", "motherfuckin", "motherfucking", "motherfuckings", "motherfucks", "nigga", "niggah", "niggas", "nigger", "niggers", "niggor", "niggors", "nigguh", "nutsack", "phonesex", "phuk", "phuked", "phuking", "phukked", "phukking", "phuks", "phuq", "poontang", "prick", "pricks", "pussies", "pussy", "pussys", "quim", "raghead", "redneck", "shit", "shited", "shitfull", "shiting", "shitings", "shits", "shitted", "shitter", "shitters", "shitting", "shittings", "shitty", "skank", "slut", "sluts", "smut", "spic", "spick", "spik", "spunk", "tit", "tities", "tits", "twat", "wanker", "wetback", "whore");
  }


/** ---------------------------------------------------------------------------------------
*
*   Settings related to TypedGroups feature
*
**/

$_PA->useTypedGroups = FALSE; // set TRUE if you want to use this feature

// Set of types that are available for TypedGroup creation and listing
// this controls what types of TypedGroup will show up in the Directory
// Note 1: you need to have $_PA->useTypedGroups set to TRUE
// Note 2: if you create specific <type>TypedGroupEntity.php class files
//         you can control the profile fields etc of that type
//
$_PA->enum_typed_group_types = array(
			'business' => __("Business"),
			'church' => __("Church"),
			'school' => __("School"),
		);

$_PA->enum_typed_group_relations = array(
			'fan' => __("Fan"),
			'supporter' => __("Supporter"),
			'customer' => __("Customer"),
			'employee' => __("Employee")
		);

$_PA->typed_group_profilefields = array(
0 => array(
			'name' => 'address',
			'label' => __("Address"),
			'type' => 'textfield'
		),
1 => array(
			'name' => 'city',
			'label' => __("City"),
			'type' => 'textfield'
		),
2 => array(
			'name' => 'state',
			'label' => __("State/Province"),
			'type' => 'stateselect',
		),
3 => array(
			'name' => 'country',
			'label' => __("Country"),
			'type' => 'countryselect',
		),
4 => array(
			'name' => 'zip',
			'label' => __("Postal Code"),
			'type' => 'textfield'
		),
5 => array(
			'name' => 'phone',
			'label' => __("Phone Number"),
			'type' => 'textfield'
		),
6 => array(
			'name' => 'website',
			'label' => __("Website"),
			'type' => 'urltextfield'
		),
);

/** ---------------------------------------------------------------------------------------
*
*   states, month names etc
*
**/

// define some standard arrays here, like US states, month names etc
// these are used in the Profile section 
// overrife these in project_config.php or local_config.php
$_PA->states = array('Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York','North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island','South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming');

$_PA->months = array("", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec");
      
$_PA->days = array();
for ($i=1; $i<=31; $i++) {
  $_PA->days[$i] = $i;
}

$_PA->years = array();
$lastyear = date("Y", time());
$firstyear = $lastyear-76;
for ($i=1, $j=$firstyear; $i<=76 && $j<=$lastyear; $i++, $j++) {
  $_PA->years[$i] = $j;
}
      
$_PA->countries = array("", "India", "USA");

$_PA->industries = array(
"Agriculture/Mining",
	  	      "Association/Magazine",
	  	      "Business/Professional Services",
	  	      "Computers/Electronics",
	  	      "Conglomerates",
	     	  "Energy/Utilities",
	     	  "Financial Services",
	    	  "Food/Beverage",
	    	  "Healthcare/Pharmaceuticals",
	    	  "Manufacturing",
	    	  "Media/Entertainment",
	    	  "Real Estate/Construction",
	    	  "Retail/Wholesale",
	    	  "Software/Internet",
	    	  "Telecommunciations",
	    	  "Transportation",
	    	  "Utilities",
	    	  "Other",
);

$_PA->ethnicities = array('', 'african american (black)', 'asian', 'caucasian (white)', 'east indian', 'hispanic/latino', 'middle eastern', 'native american', 'pacific islander', 'multi-ethnic', 'other');

// standard set of religions
$_PA->religions = array('', 'Agnostic', 'Atheist', 'Bahai', 'Buddhist', 'Cao Dai', 'Christian/Anglican', 'Christian/Catholic', 'Christian/LDS', 'Christian/Other', 'Christian/Protestant', 'Hindu', 'Jain', 'Jewish', 'Muslim', 'Neo-Paganist', 'Rastafarian', 'Religious humanism', 'Scientologist', 'Shinto', 'Sikh', 'Spiritual but not religious', 'Taoist', 'Tenrikyo', 'Unitarian Universalist', 'Zoroastrian', 'Other' );

$_PA->political_views = array('', 'right-conservative', 'very right-conservative', 'centrist', 'left-liberal', 'veryleft-liberal', 'libertarian', 'very libertarian', 'authoritarian', 'very authoritarian', 'depends', 'not political');


$_PA->relationships = array('', 'Single', 'In a Relationship', 'In an Open Relationship', 'Engaged', 'Married', 'It\'s Complicated');

if(!isset($_PA->simple)) $_PA->simple = FALSE;

if ($_PA->use_storage) define("NEW_STORAGE", TRUE); // enable new-style storage system


// initial performance log entry
pa_log_script_execution_time(TRUE);
?>