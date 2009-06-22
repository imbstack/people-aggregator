<?php
 /**
 *
 * @class MSLiveDataMapper
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 * @brief class MSLiveDataMapper for converting MSLive to PA data format and vice versa
 *
 *
 **/

require_once "api/ProfileIO/map/DataMapper.intf.php";

class MSLiveDataMapper implements DataMapper {


  public static $mslive_pers_profile_keys = array(
   'NameToFileAs',
   'NameTitle',
   'FirstName'  => 'personal|first_name',
   'MiddleName',
   'LastName'   => 'personal|last_name',
   'Suffix',
   'YomiFirstName',
   'YomiLastName',
   'Birthdate'  => 'general|dob',
   'Anniversary',
   'Gender'     => 'general|sex',
   'TimeZone',
   'SpouseName'
  );

  public static $mslive_prof_profile_keys = array(
   'JobTitle' => 'professional|title',
   'Profession',
   'Manager',
   'Assistant'
  );

  private static $email_type = 'EmailType';          // Personal/Business
  public static $mslive_email_keys = array(
   'Business' => array(
     'Address' => 'professional|email'
//     'IsDefault'
    ),
   'Personal' => array(
     'Address' => 'personal|email'
//     'IsDefault'
    ),
   'Other' => array(
     'Address' => 'personal|email'
//     'IsDefault'
    ),
   'WindowsLiveID' => array(
     'Address' => 'personal|email'
//     'IsDefault'
    ),
  );

  private static $phone_type = 'PhoneType';                        // Personal/Business
  public static $mslive_phone_keys = array(
   'Personal' => array(
     'Number' => 'general|phone'
//     'IsDefault'
    ),
   'Business' => array(
     'Number' => 'general|phone'
//     'IsDefault'
    ),
   'Mobile' => array(
     'Number',
//     'IsDefault'
    ),
   'Other' => array(
     'Number',
//     'IsDefault'
    )
  );


  private static $location_type = 'LocationType';
  public static $mslive_location_keys = array(
   'Business' => array(
     'Office',
     'Department',
     'CompanyName'       => 'professional|company',
     'YomiCompanyName',
     'StreetLine',
     'StreetLine2',
     'PrimaryCity',
     'SecondaryCity',
     'Subdivision',
     'PostalCode',
     'CountryRegion',
     'Latitude',
     'Longitude'
//     'Locations|Location|IsDefault',
    ),
   'Personal' => array(
     'StreetLine'       => 'general|address',
     'StreetLine2'      => 'general|address',
     'PrimaryCity'      => 'general|city',
     'SecondaryCity',
     'Subdivision',
     'PostalCode'       => 'general|postal_code',
     'CountryRegion'    => 'general|country',
     'Latitude',
     'Longitude'
//     'Locations|Location|IsDefault',
    )
  );

  private static $uri_type = 'URIType';           // Personal/Business
  public static $mslive_uri_keys = array(
   'Personal' => array(
     'Name',
     'Address'  => 'general|website'
    ),
   'Business' => array(
     'Name',
     'Address'  => 'professional|website'
    )
  );

    public static function processInData($data = array()) {
      $root_paths = array('Profiles|Personal'     => array('keys' => self::$mslive_pers_profile_keys, 'type_field' => null),
                          'Profiles|Professional' => array('keys' => self::$mslive_prof_profile_keys, 'type_field' => null),
                          'Emails|Email'          => array('keys' => self::$mslive_email_keys, 'type_field' => self::$email_type),
                          'Phones|Phone'          => array('keys' => self::$mslive_phone_keys, 'type_field' => self::$phone_type),
                          'Locations|Location'    => array('keys' => self::$mslive_location_keys, 'type_field' => self::$location_type),
                          'URIs|URI'              => array('keys' => self::$mslive_uri_keys, 'type_field' => self::$uri_type)
                         );
     $result = array();
//         echo '<pre>'.print_r($data,1).'</pre>'; die();
      if(count($data) > 0) {
        for($i = 0; $i < count($data); $i++) {
          $result[$i] = array();
          self::parseContactsArray($data[$i], &$result[$i], $root_paths);
        }
      }
      return $result;
    }

    public static function processOutData() {

    }

    private static function parseContactsArray($data = array(), &$result, $root_paths) {
      if(count($data) > 0) {
        foreach($root_paths as $path => $params) {
          $path_parts = explode('|', $path);
          $k0 = $path_parts[0];
          $k1 = $path_parts[1];
          if(empty($data[$k0][$k1])) continue;
          $data_node = $data[$k0][$k1];
          foreach($data_node as $item) {
          $map_table  = $params['keys'];
          $type_field = $params['type_field'];
            if($type_field) {
              $item_type = $item[$type_field];
              $map_table = $map_table[$item_type];
//              echo "searching for: ". $params['type_field'] . '<br/>';
//              echo "ITEM: $item_type<pre>".print_r($item,1).'</pre>';
//              echo 'MAPP: <pre>'.print_r($map_table,1).'</pre>';
            }
            foreach($map_table as $key1 => $key2) {
              if(is_numeric($key1)) {
                $kname = $k1 . '_' . $key2;
                if(!empty($item[$key2])) {
                  $result['extra'][$kname] = $item[$key2];
                }
              } else {
                if(isset($item[$key1]) && (!empty($key2))) {
                  $path = explode('|', $key2);
                  $section = $path[0];
                  $field   = $path[1];
                  if(empty($item[$key1]) && (!empty($result[$section][$field]))) continue;
                  $result[$section][$field] = $item[$key1];
                }
              }
            }
//            echo '<pre>'.print_r($map_table,1).'</pre>';
//            echo '<pre>'.print_r($item,1).'</pre>';
          }
        }
//        echo '<pre>'.print_r($result,1).'</pre>';
      }
    }
  }
