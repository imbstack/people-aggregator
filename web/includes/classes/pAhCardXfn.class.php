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
 
 /** 
 * 
 * @class pAhCardXfn
 * @author Zoran Hron
 *
 * @brief extended hCardXFN parser for People Aggregator
 * 
 *
 **/
 

 /**************************************************************************
 *   Copyright (C) 2007 by Zoran Hron                                      *
 *   zhron@net.hr                                                          *
 *                                                                         *
 *     extended hCardXFN parser for People Aggregator                      *
 *                                                                         *
 *                                                                         *
 *  v0.1.1 changes:                                                        *
 *                                                                         *
 *  hCard:                                                                 *
 *   - added section "unclassified"                                        *
 *                                                                         *
 *  XFN relations:                                                         *
 *   - get network name from profile_url                                   *
 *   - use "fn" or "xfnfriendly" as network_uid                            *
 ***************************************************************************/

 require_once "web/includes/classes/hCardXFN.class.php";
 
 define('HCARD_CURRENT_COMPANY', 0);
 define('HCARD_PRIOR_COMPANY', 1);
 
 
 class pAhCardXfn extends hCardXFN {
 
   private $profile_records;
   private $relations_records;
   private $company_type;
   private $ctype_depended = array( 'org', 'organization-name', 'locality', 'title' );      // depended of the company type
   
   private $sections = array('core'         => array(
                                                     'first_name'          => 'given-name',
                                                     'last_name'           => 'family-name',
                                                     'email'               => 'email',
                                                     'picture'             => 'photo'
                                                    ),
                             'basic'        => array(
                                                     'first_name'          => 'given-name',
                                                     'last_name'           => 'family-name'
                                                    ),
                             'general'      => array(
                                                     'country'             => 'country-name',
                                                     'state'               => 'region',
                                                     'city'                => 'locality' ,
                                                     'dob'                 => 'bday',
// not implemented                                   'sex',
// not implemented                                   'sub_caption',
// not implemented                                   'user_caption',
                                                     'postal_code'         => 'postal-code',
// not implemented                                   'outputthis_username',
// not implemented                                   'outputthis_password',
// not implemented                                   'user_tags'
                                                    ),
                             'personal'     => array(
                                                     'ethnicity'           => 'ethnicity',
                                                     'religion'            => 'religion',
                                                     'political_view'      => 'political',
                                                     'passion'             => 'passion',
                                                     'activities'          => 'activities',
                                                     'books'               => 'books',
                                                     'movies'              => 'movies',
                                                     'music'               => 'music',
                                                     'tv_shows'            => 'tv',
                                                     'cusines'             => 'food',
                                                    ),
                             'professional' => array(
                                                     'headline'            => 'headline',
// not implemented                                   'user_cv',
                                                     'industry'            => 'industry',
                                                     'company'             => array('org', 'organization-name'),
                                                     'title'               => array('title',),
                                                     'website'             => 'url',
                                                     'career_skill'        => 'skills',
                                                     'prior_company'       => array('org', 'organization-name'),
                                                     'prior_company_city'  => 'locality',
                                                     'prior_company_title' => array('title',),
                                                     'college_name'        => 'college',
                                                     'degree'              => 'degree',
                                                     'summary'             => array('note', 'summary',),
                                                     'languages'           => 'languages',
                                                     'awards'              => 'awards',
                                                    )
                      );
                                               
  
  
  public function __construct() {
      parent::__construct();
  }
  
  public function hCardToPaProfile($indx) {
    $this->normalize_email_address();                                  // remove "type" attrib from email record
    $card_data = $this->outData[$indx]['value'];
    $this->company_type = HCARD_CURRENT_COMPANY;                       // set default company type 
    if(isset($card_data['org']['type'])) {
      if($card_data['org']['type'] == 'prior') 
        $this->company_type = HCARD_PRIOR_COMPANY;
    }
    $this->profileDataToArray($card_data);
/*    
    echo '<pre>';
    echo print_r($this->profile_records,1);
    echo print_r($this->outData,1);
    echo '</pre>';
*/    
    return $this->profile_records;
  }
                                             
  public function getRelationsAsArray($rselected) {
    $cnt = 0;
    foreach($rselected as $k => $v) {
       $relation = $this->outData[$v];
       if(isset($relation['value']['url'])) {
         $url_parsed = parse_url($relation['value']['url']);
         $host_arr   = explode('.', $url_parsed['host']);
         $network    = (count($host_arr) <= 2) ? $host_arr[0] : $host_arr[1];
       } else {
         $network    = $relation['type'];
       }
       
       $network_uid = (isset($relation['value']['xfnfriendly']))   ? $relation['value']['xfnfriendly']  // Tantek stuff :)
                                                                   : $relation['value']['fn'];
       
       $this->relations_records['relation'][$cnt]['network']       = $network;
       $this->relations_records['relation'][$cnt]['network_uid']   = $network_uid;
       $this->relations_records['relation'][$cnt]['display_name']  = $relation['value']['fn'];
       $this->relations_records['relation'][$cnt]['thumbnail_url'] = (isset($relation['value']['photo'])) 
                                                                   ?  $relation['value']['photo']
                                                                   :  '';
       $this->relations_records['relation'][$cnt]['profile_url']   = (isset($relation['value']['url']))
                                                                   ?  $relation['value']['url']
                                                                   :  '';
       ++$cnt;
    }   
    return $this->relations_records;
  }
                                             
  private function profileDataToArray($in_array) {
    foreach( $in_array as $k => $v ) {
      if(is_array($v)) {
        $this->profileDataToArray($v);
      } else {
        $res = $this->searchArrayRecursive($k, $this->sections);
        if(count($res) > 0) {
          if(in_array($k, $this->ctype_depended)) {
            $path_arr = explode('/', $res[$this->company_type]);           // depended of the company type
            $section  = $path_arr[0];
            $name     = $path_arr[1];
            $value    = trim($v, " ;"); 
            if(strlen($value) > 0)
              $this->profile_records[$section][$name] = $value;
          } elseif(($k == 'given-name') || ($k == 'family-name')) {        // multiple defined (in 'core' and 'basics')
            foreach($res as $_k => $_v) {
              $path_arr = explode('/', $_v);                
              $section  = $path_arr[0];
              $name     = $path_arr[1];
              $value    = trim($v, " ;"); 
              if(strlen($value) > 0)
                $this->profile_records[$section][$name] = $value;
            }
          } else {
            $path_arr = explode('/', $res[0]);                             
            $section  = $path_arr[0];
            $name     = $path_arr[1];
            $value    = trim($v, " ;"); 
            if(strlen($value) > 0)
              $this->profile_records[$section][$name] = $value;
          }
        } else {
            $value = trim($v, " ;"); 
            if((strlen($value) > 0) && !array_key_exists($k, $this->sub_classes))
              $this->profile_records['unclassified'][$k] = $value;
        }
      }
    }
  }
  
  public function searchArrayRecursive($needle, $haystack, $path=array()) {
    $res = array();
    foreach ($haystack as $key => $arr) {
      if(is_array($arr)) {
        array_push($path,$key); 
        $ret = $this->searchArrayRecursive($needle, $arr, $path);
        if(is_array($ret)) { 
          if(count($ret) > 0) {
             $res = array_merge($res, $ret);
          } else {
             array_pop($path);
          }
        } else {
           $path_arr = $path;
           $path_arr[] = $ret;
           $res[] = implode('/', $path_arr);
           array_pop($path);
        }  
      } else {
        if($arr == $needle) return (string)$key;
      }
    }
    return $res;
  }   
  
  private function normalize_email_address() {
     foreach($this->outData as $k => &$v) {
        if(isset($v['value']['email'])) {
           if(is_array($v['value']['email'])) {
              $mails = array();
              foreach($v['value']['email'] as $_k => $_v) {
                if(is_numeric($_k)) $mails[] = $_v;
              }
              unset($v['value']['email']);
              $v['value']['email'] = implode(',', $mails);
           }
        } 
     }
  }

} 


 