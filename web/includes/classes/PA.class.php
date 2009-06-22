<?php

// The PA class stores certain globals - critical things which affect every page.
// Less 'global' configuration items should be member variables of $_PA (see below).
class PA {

  public static $facebook_request_string = "Hi Facebook friend - come join this PeopleAggregator network!";
// Example: in ROTC default_config.php :
// PA::$facebook_request_string = __("Come check out ROTCLink - where cadets and alumni stay connected!");

  // TODO: add Currencies, number formats and other i18N data, Z.Hron 2008-12-01
  public static $culture_data = array(
                      'english' => array( // en_US
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'h:i:s A',
                                              'date'  => 'F d, Y'
                                  ),
                                  'short' => array (
                                              'time' => 'h:i a',
                                              'date' => 'm/d/y'
                                  )
                            ),
                            'states' => array(
                               'AL'=>"Alabama",
                               'AK'=>"Alaska",
                               'AZ'=>"Arizona",
                               'AR'=>"Arkansas",
                               'CA'=>"California",
                               'CO'=>"Colorado",
                               'CT'=>"Connecticut",
                               'DE'=>"Delaware",
                               'DC'=>"District Of Columbia",
                               'FL'=>"Florida",
                               'GA'=>"Georgia",
                               'HI'=>"Hawaii",
                               'ID'=>"Idaho",
                               'IL'=>"Illinois",
                               'IN'=>"Indiana",
                               'IA'=>"Iowa",
                               'KS'=>"Kansas",
                               'KY'=>"Kentucky",
                               'LA'=>"Louisiana",
                               'ME'=>"Maine",
                               'MD'=>"Maryland",
                               'MA'=>"Massachusetts",
                               'MI'=>"Michigan",
                               'MN'=>"Minnesota",
                               'MS'=>"Mississippi",
                               'MO'=>"Missouri",
                               'MT'=>"Montana",
                               'NE'=>"Nebraska",
                               'NV'=>"Nevada",
                               'NH'=>"New Hampshire",
                               'NJ'=>"New Jersey",
                               'NM'=>"New Mexico",
                               'NY'=>"New York",
                               'NC'=>"North Carolina",
                               'ND'=>"North Dakota",
                               'OH'=>"Ohio",
                               'OK'=>"Oklahoma",
                               'OR'=>"Oregon",
                               'PA'=>"Pennsylvania",
                               'RI'=>"Rhode Island",
                               'SC'=>"South Carolina",
                               'SD'=>"South Dakota",
                               'TN'=>"Tennessee",
                               'TX'=>"Texas",
                               'UT'=>"Utah",
                               'VT'=>"Vermont",
                               'VA'=>"Virginia",
                               'WA'=>"Washington",
                               'WV'=>"West Virginia",
                               'WI'=>"Wisconsin",
                               'WY'=>"Wyoming"
                            ),
                            'countries' => array(
                               'AD'=>"Andorra",
                               'AE'=>"United Arab Emirates",
                               'AF'=>"Afghanistan",
                               'AG'=>"Antigua and Barbuda",
                               'AI'=>"Anguilla",
                               'AL'=>"Albania",
                               'AM'=>"Armenia",
                               'AN'=>"Netherlands Antilles",
                               'AO'=>"Angola",
                               'AQ'=>"Antarctica",
                               'AR'=>"Argentina",
                               'AS'=>"American Samoa",
                               'AT'=>"Austria",
                               'AU'=>"Australia",
                               'AW'=>"Aruba",
                               'AX'=>"Aland Islands",
                               'AZ'=>"Azerbaijan",
                               'BA'=>"Bosnia and Herzegovina",
                               'BB'=>"Barbados",
                               'BD'=>"Bangladesh",
                               'BE'=>"Belgium",
                               'BF'=>"Burkina Faso",
                               'BG'=>"Bulgaria",
                               'BH'=>"Bahrain",
                               'BI'=>"Burundi",
                               'BJ'=>"Benin",
                               'BL'=>"Saint Barthélemy",
                               'BM'=>"Bermuda",
                               'BN'=>"Brunei Darussalam",
                               'BO'=>"Bolivia",
                               'BR'=>"Brazil",
                               'BS'=>"Bahamas",
                               'BT'=>"Bhutan",
                               'BV'=>"Bouvet Island",
                               'BW'=>"Botswana",
                               'BY'=>"Belarus",
                               'BZ'=>"Belize",
                               'CA'=>"Canada",
                               'CC'=>"Cocos (Keeling) Islands",
                               'CD'=>"Congo, the Democratic Republic of the",
                               'CF'=>"Central African Republic",
                               'CG'=>"Congo",
                               'CH'=>"Switzerland",
                               'CI'=>"Cote d'Ivoire",
                               'CK'=>"Cook Islands",
                               'CL'=>"Chile",
                               'CM'=>"Cameroon",
                               'CN'=>"China",
                               'CO'=>"Colombia",
                               'CR'=>"Costa Rica",
                               'CU'=>"Cuba",
                               'CV'=>"Cape Verde",
                               'CX'=>"Christmas Island",
                               'CY'=>"Cyprus",
                               'CZ'=>"Czech Republic",
                               'DE'=>"Germany",
                               'DJ'=>"Djibouti",
                               'DK'=>"Denmark",
                               'DM'=>"Dominica",
                               'DO'=>"Dominican Republic",
                               'DZ'=>"Algeria",
                               'EC'=>"Ecuador",
                               'EE'=>"Estonia",
                               'EG'=>"Egypt",
                               'EH'=>"Western Sahara",
                               'ER'=>"Eritrea",
                               'ES'=>"Spain",
                               'ET'=>"Ethiopia",
                               'FI'=>"Finland",
                               'FJ'=>"Fiji",
                               'FK'=>"Falkland Islands",
                               'FM'=>"Micronesia, Federated States of",
                               'FO'=>"Faroe Islands",
                               'FR'=>"France",
                               'GA'=>"Gabon",
                               'GB'=>"Great Britain",
                               'UK'=>"United Kingdom",
                               'GD'=>"Grenada",
                               'GE'=>"Georgia",
                               'GF'=>"French Guiana",
                               'GG'=>"Guernsey",
                               'GH'=>"Ghana",
                               'GI'=>"Gibraltar",
                               'GL'=>"Greenland",
                               'GM'=>"Gambia",
                               'GN'=>"Guinea",
                               'GP'=>"Guadeloupe",
                               'GQ'=>"Equatorial Guinea",
                               'GR'=>"Greece",
                               'GS'=>"South Georgia and the South Sandwich Islands",
                               'GT'=>"Guatemala",
                               'GU'=>"Guam",
                               'GW'=>"Guinea-Bissau",
                               'GY'=>"Guyana",
                               'HK'=>"Hong Kong",
                               'HM'=>"Heard Island and McDonald Islands",
                               'HN'=>"Honduras",
                               'HR'=>"Croatia",
                               'HT'=>"Haiti",
                               'HU'=>"Hungary",
                               'ID'=>"Indonesia",
                               'IE'=>"Ireland",
                               'IL'=>"Israel",
                               'IM'=>"Isle of Man",
                               'IN'=>"India",
                               'IO'=>"British Indian Ocean Territory",
                               'IQ'=>"Iraq",
                               'IR'=>"Iran, Islamic Republic of",
                               'IS'=>"Iceland",
                               'IT'=>"Italy",
                               'JE'=>"Jersey",
                               'JM'=>"Jamaica",
                               'JO'=>"Jordan",
                               'JP'=>"Japan",
                               'KE'=>"Kenya",
                               'KG'=>"Kyrgyzstan",
                               'KH'=>"Cambodia",
                               'KI'=>"Kiribati",
                               'KM'=>"Comoros",
                               'KN'=>"Saint Kitts and Nevis",
                               'KP'=>"Korea, Democratic People's Republic of",
                               'KR'=>"Korea, Republic of",
                               'KW'=>"Kuwait",
                               'KY'=>"Cayman Islands",
                               'KZ'=>"Kazakhstan",
                               'LA'=>"Lao People's Democratic Republic",
                               'LB'=>"Lebanon",
                               'LC'=>"Saint Lucia",
                               'LI'=>"Liechtenstein",
                               'LK'=>"Sri Lanka",
                               'LR'=>"Liberia",
                               'LS'=>"Lesotho",
                               'LT'=>"Lithuania",
                               'LU'=>"Luxembourg",
                               'LV'=>"Latvia",
                               'LY'=>"Libyan Arab Jamahiriya",
                               'MA'=>"Morocco",
                               'MC'=>"Monaco",
                               'MD'=>"Moldova, Republic of",
                               'ME'=>"Montenegro",
                               'MF'=>"Saint Martin",
                               'MG'=>"Madagascar",
                               'MH'=>"Marshall Islands",
                               'MK'=>"Macedonia, the former Yugoslav Republic of",
                               'ML'=>"Mali",
                               'MM'=>"Myanmar",
                               'MN'=>"Mongolia",
                               'MO'=>"Macao",
                               'MP'=>"Northern Mariana Islands",
                               'MQ'=>"Martinique",
                               'MR'=>"Mauritania",
                               'MS'=>"Montserrat",
                               'MT'=>"Malta",
                               'MU'=>"Mauritius",
                               'MV'=>"Maldives",
                               'MW'=>"Malawi",
                               'MX'=>"Mexico",
                               'MY'=>"Malaysia",
                               'MZ'=>"Mozambique",
                               'NA'=>"Namibia",
                               'NC'=>"New Caledonia",
                               'NE'=>"Niger",
                               'NF'=>"Norfolk Island",
                               'NG'=>"Nigeria",
                               'NI'=>"Nicaragua",
                               'NL'=>"Netherlands",
                               'NO'=>"Norway",
                               'NP'=>"Nepal",
                               'NR'=>"Nauru",
                               'NU'=>"Niue",
                               'NZ'=>"New Zealand",
                               'OM'=>"Oman",
                               'PA'=>"Panama",
                               'PE'=>"Peru",
                               'PF'=>"French Polynesia",
                               'PG'=>"Papua New Guinea",
                               'PH'=>"Philippines",
                               'PK'=>"Pakistan",
                               'PL'=>"Poland",
                               'PM'=>"Saint Pierre and Miquelon",
                               'PN'=>"Pitcairn",
                               'PR'=>"Puerto Rico",
                               'PS'=>"Palestinian Territory, Occupied",
                               'PT'=>"Portugal",
                               'PW'=>"Palau",
                               'PY'=>"Paraguay",
                               'QA'=>"Qatar",
                               'RE'=>"Reunion",
                               'RO'=>"Romania",
                               'RS'=>"Serbia",
                               'RU'=>"Russian Federation",
                               'RW'=>"Rwanda",
                               'SA'=>"Saudi Arabia",
                               'SB'=>"Solomon Islands",
                               'SC'=>"Seychelles",
                               'SD'=>"Sudan",
                               'SE'=>"Sweden",
                               'SG'=>"Singapore",
                               'SH'=>"Saint Helena",
                               'SI'=>"Slovenia",
                               'SJ'=>"Svalbard and Jan Mayen",
                               'SK'=>"Slovakia",
                               'SL'=>"Sierra Leone",
                               'SM'=>"San Marino",
                               'SN'=>"Senegal",
                               'SO'=>"Somalia",
                               'SR'=>"Suriname",
                               'ST'=>"Sao Tome and Principe",
                               'SV'=>"El Salvador",
                               'SY'=>"Syrian Arab Republic",
                               'SZ'=>"Swaziland",
                               'TC'=>"Turks and Caicos Islands",
                               'TD'=>"Chad",
                               'TF'=>"French Southern Territories",
                               'TG'=>"Togo",
                               'TH'=>"Thailand",
                               'TJ'=>"Tajikistan",
                               'TK'=>"Tokelau",
                               'TL'=>"Timor-Leste",
                               'TM'=>"Turkmenistan",
                               'TN'=>"Tunisia",
                               'TO'=>"Tonga",
                               'TR'=>"Turkey",
                               'TT'=>"Trinidad and Tobago",
                               'TV'=>"Tuvalu",
                               'TW'=>"Taiwan, Province of China",
                               'TZ'=>"Tanzania, United Republic of",
                               'UA'=>"Ukraine",
                               'UG'=>"Uganda",
                               'UM'=>"United States Minor Outlying Islands",
                               'US'=>"United States",
                               'UY'=>"Uruguay",
                               'UZ'=>"Uzbekistan",
                               'VA'=>"Holy See (Vatican City State)",
                               'VC'=>"Saint Vincent and the Grenadines",
                               'VE'=>"Venezuela",
                               'VG'=>"Virgin Islands, British",
                               'VI'=>"Virgin Islands, U.S.",
                               'VN'=>"Viet Nam",
                               'VU'=>"Vanuatu",
                               'WF'=>"Wallis and Futuna",
                               'WS'=>"Samoa",
                               'YE'=>"Yemen",
                               'YT'=>"Mayotte",
                               'ZA'=>"South Africa",
                               'ZM'=>"Zambia",
                               'ZW'=>"Zimbabwe"
                            ),
                       ),
                      'german' => array( // de_DE
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'H:i:s',
                                              'date'  => 'd.m.Y'
                                  ),
                                  'short' => array (
                                              'time' => 'H:i',
                                              'date' => 'd.m.y'
                                  )
                            ),
                       ),
                      'french' => array( // fr_FR
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'H:i:s',
                                              'date'  => 'd M y'
                                  ),
                                  'short' => array (
                                              'time' => 'H:i',
                                              'date' => 'd/m/Y'
                                  )
                            ),
                       ),
                      'spanish' => array( // es_ES
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'H:i:s',
                                              'date'  => 'd-M-y'
                                  ),
                                  'short' => array (
                                              'time' => 'H:i',
                                              'date' => 'd/m/y'
                                  )
                            ),
                       ),
                      'italian' => array( // it_IT
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'H:i:s',
                                              'date'  => 'd F Y'
                                  ),
                                  'short' => array (
                                              'time' => 'H:i',
                                              'date' => 'd/m/y'
                                  )
                            ),
                       ),
                      'japanese' => array( // jp_JP
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'G:i:s',
                                              'date'  => 'Y/m/d'
                                  ),
                                  'short' => array (
                                              'time' => 'G:i',
                                              'date' => 'y/m/d'
                                  )
                            ),
                       ),
                      'default' => array( // en_US is default date/time format
                            'date_time' => array (
                                  'long' =>  array(
                                              'time'  => 'h:i:s A',
                                              'date'  => 'F d, Y'
                                  ),
                                  'short' => array (
                                              'time' => 'h:i a',
                                              'date' => 'm/d/y'
                                  )
                            ),
                            'states' => array(
                               'AL'=>"Alabama",
                               'AK'=>"Alaska",
                               'AZ'=>"Arizona",
                               'AR'=>"Arkansas",
                               'CA'=>"California",
                               'CO'=>"Colorado",
                               'CT'=>"Connecticut",
                               'DE'=>"Delaware",
                               'DC'=>"District Of Columbia",
                               'FL'=>"Florida",
                               'GA'=>"Georgia",
                               'HI'=>"Hawaii",
                               'ID'=>"Idaho",
                               'IL'=>"Illinois",
                               'IN'=>"Indiana",
                               'IA'=>"Iowa",
                               'KS'=>"Kansas",
                               'KY'=>"Kentucky",
                               'LA'=>"Louisiana",
                               'ME'=>"Maine",
                               'MD'=>"Maryland",
                               'MA'=>"Massachusetts",
                               'MI'=>"Michigan",
                               'MN'=>"Minnesota",
                               'MS'=>"Mississippi",
                               'MO'=>"Missouri",
                               'MT'=>"Montana",
                               'NE'=>"Nebraska",
                               'NV'=>"Nevada",
                               'NH'=>"New Hampshire",
                               'NJ'=>"New Jersey",
                               'NM'=>"New Mexico",
                               'NY'=>"New York",
                               'NC'=>"North Carolina",
                               'ND'=>"North Dakota",
                               'OH'=>"Ohio",
                               'OK'=>"Oklahoma",
                               'OR'=>"Oregon",
                               'PA'=>"Pennsylvania",
                               'RI'=>"Rhode Island",
                               'SC'=>"South Carolina",
                               'SD'=>"South Dakota",
                               'TN'=>"Tennessee",
                               'TX'=>"Texas",
                               'UT'=>"Utah",
                               'VT'=>"Vermont",
                               'VA'=>"Virginia",
                               'WA'=>"Washington",
                               'WV'=>"West Virginia",
                               'WI'=>"Wisconsin",
                               'WY'=>"Wyoming"
                            ),
                            'countries' => array(
                               'AD'=>"Andorra",
                               'AE'=>"United Arab Emirates",
                               'AF'=>"Afghanistan",
                               'AG'=>"Antigua and Barbuda",
                               'AI'=>"Anguilla",
                               'AL'=>"Albania",
                               'AM'=>"Armenia",
                               'AN'=>"Netherlands Antilles",
                               'AO'=>"Angola",
                               'AQ'=>"Antarctica",
                               'AR'=>"Argentina",
                               'AS'=>"American Samoa",
                               'AT'=>"Austria",
                               'AU'=>"Australia",
                               'AW'=>"Aruba",
                               'AX'=>"Aland Islands",
                               'AZ'=>"Azerbaijan",
                               'BA'=>"Bosnia and Herzegovina",
                               'BB'=>"Barbados",
                               'BD'=>"Bangladesh",
                               'BE'=>"Belgium",
                               'BF'=>"Burkina Faso",
                               'BG'=>"Bulgaria",
                               'BH'=>"Bahrain",
                               'BI'=>"Burundi",
                               'BJ'=>"Benin",
                               'BL'=>"Saint Barthélemy",
                               'BM'=>"Bermuda",
                               'BN'=>"Brunei Darussalam",
                               'BO'=>"Bolivia",
                               'BR'=>"Brazil",
                               'BS'=>"Bahamas",
                               'BT'=>"Bhutan",
                               'BV'=>"Bouvet Island",
                               'BW'=>"Botswana",
                               'BY'=>"Belarus",
                               'BZ'=>"Belize",
                               'CA'=>"Canada",
                               'CC'=>"Cocos (Keeling) Islands",
                               'CD'=>"Congo, the Democratic Republic of the",
                               'CF'=>"Central African Republic",
                               'CG'=>"Congo",
                               'CH'=>"Switzerland",
                               'CI'=>"Cote d'Ivoire",
                               'CK'=>"Cook Islands",
                               'CL'=>"Chile",
                               'CM'=>"Cameroon",
                               'CN'=>"China",
                               'CO'=>"Colombia",
                               'CR'=>"Costa Rica",
                               'CU'=>"Cuba",
                               'CV'=>"Cape Verde",
                               'CX'=>"Christmas Island",
                               'CY'=>"Cyprus",
                               'CZ'=>"Czech Republic",
                               'DE'=>"Germany",
                               'DJ'=>"Djibouti",
                               'DK'=>"Denmark",
                               'DM'=>"Dominica",
                               'DO'=>"Dominican Republic",
                               'DZ'=>"Algeria",
                               'EC'=>"Ecuador",
                               'EE'=>"Estonia",
                               'EG'=>"Egypt",
                               'EH'=>"Western Sahara",
                               'ER'=>"Eritrea",
                               'ES'=>"Spain",
                               'ET'=>"Ethiopia",
                               'FI'=>"Finland",
                               'FJ'=>"Fiji",
                               'FK'=>"Falkland Islands",
                               'FM'=>"Micronesia, Federated States of",
                               'FO'=>"Faroe Islands",
                               'FR'=>"France",
                               'GA'=>"Gabon",
                               'GB'=>"Great Britain",
                               'UK'=>"United Kingdom",
                               'GD'=>"Grenada",
                               'GE'=>"Georgia",
                               'GF'=>"French Guiana",
                               'GG'=>"Guernsey",
                               'GH'=>"Ghana",
                               'GI'=>"Gibraltar",
                               'GL'=>"Greenland",
                               'GM'=>"Gambia",
                               'GN'=>"Guinea",
                               'GP'=>"Guadeloupe",
                               'GQ'=>"Equatorial Guinea",
                               'GR'=>"Greece",
                               'GS'=>"South Georgia and the South Sandwich Islands",
                               'GT'=>"Guatemala",
                               'GU'=>"Guam",
                               'GW'=>"Guinea-Bissau",
                               'GY'=>"Guyana",
                               'HK'=>"Hong Kong",
                               'HM'=>"Heard Island and McDonald Islands",
                               'HN'=>"Honduras",
                               'HR'=>"Croatia",
                               'HT'=>"Haiti",
                               'HU'=>"Hungary",
                               'ID'=>"Indonesia",
                               'IE'=>"Ireland",
                               'IL'=>"Israel",
                               'IM'=>"Isle of Man",
                               'IN'=>"India",
                               'IO'=>"British Indian Ocean Territory",
                               'IQ'=>"Iraq",
                               'IR'=>"Iran, Islamic Republic of",
                               'IS'=>"Iceland",
                               'IT'=>"Italy",
                               'JE'=>"Jersey",
                               'JM'=>"Jamaica",
                               'JO'=>"Jordan",
                               'JP'=>"Japan",
                               'KE'=>"Kenya",
                               'KG'=>"Kyrgyzstan",
                               'KH'=>"Cambodia",
                               'KI'=>"Kiribati",
                               'KM'=>"Comoros",
                               'KN'=>"Saint Kitts and Nevis",
                               'KP'=>"Korea, Democratic People's Republic of",
                               'KR'=>"Korea, Republic of",
                               'KW'=>"Kuwait",
                               'KY'=>"Cayman Islands",
                               'KZ'=>"Kazakhstan",
                               'LA'=>"Lao People's Democratic Republic",
                               'LB'=>"Lebanon",
                               'LC'=>"Saint Lucia",
                               'LI'=>"Liechtenstein",
                               'LK'=>"Sri Lanka",
                               'LR'=>"Liberia",
                               'LS'=>"Lesotho",
                               'LT'=>"Lithuania",
                               'LU'=>"Luxembourg",
                               'LV'=>"Latvia",
                               'LY'=>"Libyan Arab Jamahiriya",
                               'MA'=>"Morocco",
                               'MC'=>"Monaco",
                               'MD'=>"Moldova, Republic of",
                               'ME'=>"Montenegro",
                               'MF'=>"Saint Martin",
                               'MG'=>"Madagascar",
                               'MH'=>"Marshall Islands",
                               'MK'=>"Macedonia, the former Yugoslav Republic of",
                               'ML'=>"Mali",
                               'MM'=>"Myanmar",
                               'MN'=>"Mongolia",
                               'MO'=>"Macao",
                               'MP'=>"Northern Mariana Islands",
                               'MQ'=>"Martinique",
                               'MR'=>"Mauritania",
                               'MS'=>"Montserrat",
                               'MT'=>"Malta",
                               'MU'=>"Mauritius",
                               'MV'=>"Maldives",
                               'MW'=>"Malawi",
                               'MX'=>"Mexico",
                               'MY'=>"Malaysia",
                               'MZ'=>"Mozambique",
                               'NA'=>"Namibia",
                               'NC'=>"New Caledonia",
                               'NE'=>"Niger",
                               'NF'=>"Norfolk Island",
                               'NG'=>"Nigeria",
                               'NI'=>"Nicaragua",
                               'NL'=>"Netherlands",
                               'NO'=>"Norway",
                               'NP'=>"Nepal",
                               'NR'=>"Nauru",
                               'NU'=>"Niue",
                               'NZ'=>"New Zealand",
                               'OM'=>"Oman",
                               'PA'=>"Panama",
                               'PE'=>"Peru",
                               'PF'=>"French Polynesia",
                               'PG'=>"Papua New Guinea",
                               'PH'=>"Philippines",
                               'PK'=>"Pakistan",
                               'PL'=>"Poland",
                               'PM'=>"Saint Pierre and Miquelon",
                               'PN'=>"Pitcairn",
                               'PR'=>"Puerto Rico",
                               'PS'=>"Palestinian Territory, Occupied",
                               'PT'=>"Portugal",
                               'PW'=>"Palau",
                               'PY'=>"Paraguay",
                               'QA'=>"Qatar",
                               'RE'=>"Reunion",
                               'RO'=>"Romania",
                               'RS'=>"Serbia",
                               'RU'=>"Russian Federation",
                               'RW'=>"Rwanda",
                               'SA'=>"Saudi Arabia",
                               'SB'=>"Solomon Islands",
                               'SC'=>"Seychelles",
                               'SD'=>"Sudan",
                               'SE'=>"Sweden",
                               'SG'=>"Singapore",
                               'SH'=>"Saint Helena",
                               'SI'=>"Slovenia",
                               'SJ'=>"Svalbard and Jan Mayen",
                               'SK'=>"Slovakia",
                               'SL'=>"Sierra Leone",
                               'SM'=>"San Marino",
                               'SN'=>"Senegal",
                               'SO'=>"Somalia",
                               'SR'=>"Suriname",
                               'ST'=>"Sao Tome and Principe",
                               'SV'=>"El Salvador",
                               'SY'=>"Syrian Arab Republic",
                               'SZ'=>"Swaziland",
                               'TC'=>"Turks and Caicos Islands",
                               'TD'=>"Chad",
                               'TF'=>"French Southern Territories",
                               'TG'=>"Togo",
                               'TH'=>"Thailand",
                               'TJ'=>"Tajikistan",
                               'TK'=>"Tokelau",
                               'TL'=>"Timor-Leste",
                               'TM'=>"Turkmenistan",
                               'TN'=>"Tunisia",
                               'TO'=>"Tonga",
                               'TR'=>"Turkey",
                               'TT'=>"Trinidad and Tobago",
                               'TV'=>"Tuvalu",
                               'TW'=>"Taiwan, Province of China",
                               'TZ'=>"Tanzania, United Republic of",
                               'UA'=>"Ukraine",
                               'UG'=>"Uganda",
                               'UM'=>"United States Minor Outlying Islands",
                               'US'=>"United States",
                               'UY'=>"Uruguay",
                               'UZ'=>"Uzbekistan",
                               'VA'=>"Holy See (Vatican City State)",
                               'VC'=>"Saint Vincent and the Grenadines",
                               'VE'=>"Venezuela",
                               'VG'=>"Virgin Islands, British",
                               'VI'=>"Virgin Islands, U.S.",
                               'VN'=>"Viet Nam",
                               'VU'=>"Vanuatu",
                               'WF'=>"Wallis and Futuna",
                               'WS'=>"Samoa",
                               'YE'=>"Yemen",
                               'YT'=>"Mayotte",
                               'ZA'=>"South Africa",
                               'ZM'=>"Zambia",
                               'ZW'=>"Zimbabwe"
                            ),
                       ),
  );

  // Core root directory
  public static $core_dir;

  // Project root directory
  public static $project_dir;

  // relative path to the Config directory
  public static $config_path;

  // Site name
  public static $site_name;

  // Default email sender
  public static $default_sender;

  // UI language
  public static $language;

  // Server ID (for file replication)
  public static $server_id;

  // Naming convention:
  // - $something_{path|url}: absolute path (e.g. /var/www/...../homepage.php) or URL (e.g. http://example.com/foobar/web/homepage.php)
  // - $something_local_url: URL relative to web root (e.g. /foobar/web/homepage.php)
  // - $something_rel_url: URL relative to web folder (e.g. homepage.php)
  public static $path; // absolute path to the root of the site files (web/..); same as $path_prefix
  public static $url; // absolute url of the web subdirectory;
  public static $local_url; // relative url of the web subdirectory; same as BASE_URL_REL (relative to root of url)
  public static $upload_path; // absolute path to web/files directory; same as $uploaddir - DEPRECATED; use Storage functions instead!
  public static $theme_url; // absolute url to web/Themes/Default directory; same as $current_theme_path
  public static $theme_rel; // relative url to web/Themes/Default directory; same as $current_theme_rel_path (relative to web directory)
  public static $theme_path; // path to web/Themes/XYZ directory
  public static $blockmodule_path; // absolute path to web/BlockModules directory
  public static $domain_suffix = FALSE; // copy of $domain_suffix from local_config.php
  public static $network_capable = FALSE; // TRUE if we are capable of running multiple networks, i.e. wildcard DNS is configured

  // current network
  public static $network_info = NULL; // Network object for current network

  // logged-in user
  public static $login_uid = NULL; // uid of logged-in users
  public static $login_user = NULL; // User object for logged-in user
  // user specified on the url
  public static $page_uid = NULL; // uid specified in 'uid' (user_id) or 'user' (login_name) on the query string (if valid)
  public static $page_user = NULL; // User object corresponding to $page_uid
  // user specified on the url, or logged-in user if no user specified
  public static $uid = NULL;
  public static $user = NULL;

  public static $group_noun = 'Group';
  public static $group_noun_plural = 'Groups';
  public static $group_cc_type = 1; // GROUP_COLLECTION_TYPE

  public static $people_noun = 'People';
  public static $mypage_noun = 'Me';

  public static function logged_in() { return !empty(PA::$login_uid); }

  // the IP of the remote user, or the last proxy in the chain used to get to us
  public static $remote_ip = NULL;
  // all nonlocal IPs in the chain: array(PA::$remote_ip, next_nearest_proxy_ip, ..., final_possible_client).
  // (note that we can only trust the first one -- this is just for forensics)
  public static $remote_ip_with_proxies = NULL;

  //this static variable will have the unserialized value of network_info
  public static $extra;

  //tekmedia keys
  public static $video_accesskey;
  public static $video_secretkey;
  public static $tekmedia_server;
  public static $tekmedia_site_url;
  public static $tekmedia_iframe_form_path;

  //Static method which will check whether the content moderation is on for the current network
  //TRUE -> If content moderation is on, FALSE otherwise
  public static function is_moderated_content() {
    return (!empty(PA::$extra['network_content_moderation'])) ? TRUE : FALSE;
  }

  //-- i18N helper functions -----------------------------------------------------------------------BOF
  public static function date($date, $format = 'long') {
   if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$format]['date'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$format]['date'];  // en_US is default culture
    }
    return date($date_format, (is_numeric($date)) ? $date : strtotime($date));
  }

  public static function time($time, $format = 'long') {
    if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$format]['time'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$format]['time'];  // en_US is default culture
    }
    return date($date_format, (is_numeric($time)) ? $time : strtotime($time));
  }

  public static function datetime($date, $date_format = 'long', $time_format = 'long') {
    if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['date_time'])) {
      $date_format = self::$culture_data[self::$language]['date_time'][$date_format]['date'];
      $date_format .= ' ' . self::$culture_data[self::$language]['date_time'][$time_format]['time'];
    } else {
      $date_format = self::$culture_data['default']['date_time'][$date_format]['date'];  // en_US is default culture
      $date_format .= ' ' . self::$culture_data['default']['date_time'][$time_format]['time'];
    }
    return date($date_format, (is_numeric($date)) ? $date : strtotime($date));
  }

  public static function getCountryList($language = null) {
    if(!empty($language) && !empty(self::$culture_data[$language]['countries'])) {
       $country_list = self::$culture_data[$language]['countries'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['countries'])) {
       $country_list = self::$culture_data[self::$language]['countries'];
    } else {
       $country_list = self::$culture_data['default']['countries'];  // en_US is default culture
    }
    return $country_list;
  }

  public static function getStatesList($language = null) {
    if(!empty($language) && !empty(self::$culture_data[$language]['states'])) {
       $states_list = self::$culture_data[$language]['states'];
    } else if(!empty(self::$language) && !empty(self::$culture_data[self::$language]['states'])) {
       $states_list = self::$culture_data[self::$language]['states'];
    } else {
       $states_list = self::$culture_data['default']['states'];  // en_US is default culture
    }
    return $states_list;
  }

  //-- i18N helper functions ------------------------------------------------------------------------EOF

  public static function resolveRelativePath($path) {
    if($path{0} == DIRECTORY_SEPARATOR) {
      $path = substr( $path, 1);                      // remove leading '/'
    }
    if(file_exists(self::$project_dir . DIRECTORY_SEPARATOR . $path)) {
      return (self::$project_dir . DIRECTORY_SEPARATOR . $path);
    } else if(file_exists(self::$core_dir . DIRECTORY_SEPARATOR . $path)) {
      return (self::$core_dir . DIRECTORY_SEPARATOR . $path);
    }
    return false;
  }

}
?>
