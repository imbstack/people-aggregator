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
 * @class CSVDataMapper
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 * @brief class CSVDataMapper for converting CSV to PA data format and vice versa
 *
 *
 **/

require_once "api/ProfileIO/map/DataMapper.intf.php";

class CSVDataMapper implements DataMapper {
  public static $CSV_profile_keys = array(
           "Title",
           "First Name"            => 'personal|first_name',
           "Middle Name",
           "Last Name"             => 'personal|last_name',
           "Suffix",
           "Gender"                => 'general|sex',
           "E-mail Address"        => 'personal|email',
           "E-mail Type",
           "E-mail Display Name",
           "E-mail 2 Address"      => 'professional|email',
           "E-mail 2 Type",
           "E-mail 2 Display Name",
           "E-mail 3 Address"      => 'professional|email',
           "E-mail 3 Type",
           "E-mail 3 Display Name",
           "Business Street",
           "Business Street 2",
           "Business Street 3",
           "Business City",
           "Business State",
           "Business Postal Code",
           "Business Country",
           "Home Street"           => 'general|address',
           "Home Street 2"         => 'general|address',
           "Home Street 3"         => 'general|address',
           "Home City"             => 'general|city',
           "Home State",
           "Home Postal Code"      => 'general|postal_code',
           "Home Country"          => 'general|country',
           "Other Street",
           "Other Street 2",
           "Other Street 3",
           "Other City",
           "Other State",
           "Other Postal Code",
           "Other Country",
           "Company"               => 'professional|company',
           "Department",
           "Office Location",
           "Job Title"             => 'professional|title',
           "Profession"            => 'professional|title',
//           "Assistant\'s Phone",
           "Business Fax",
           "Business Phone",
           "Business Phone 2",
           "Callback",
           "Car Phone",
           "Company Main Phone",
           "Home Fax",
           "Home Phone"            => 'general|phone',
           "Home Phone 2"          => 'general|phone',
           "ISDN",
           "Mobile Phone",
           "Other Fax",
           "Other Phone",
           "Pager",
           "Primary Phone"         => 'general|phone',
           "Radio Phone",
           "TTY/TDD Phone",
           "Telex",
           "Assistant's Name",
           "Birthday"              => 'general|dob',
           "Manager's Name",
           "Notes"                 => 'general|summary',
           "Spouse",
           "Personal Web Page"     => 'general|website',
           "Web Page"              => 'general|website',
           "Home Address PO Box",
           "Business Address PO Box",
           "Other Address PO Box",
           "Account",
           "Anniversary",
           "Categories",
           "Children",
           "Directory Server",
           "Billing Information",
           "Government ID Number",
           "Hobby",
           "Initials",
           "Internet Free Busy",
           "Keywords",
           "Language",
           "Location",
           "Mileage",
           "Organizational ID Number",
           "Priority",
           "Private",
           "Referred By",
           "Sensitivity",
           "User 1",
           "User 2",
           "User 3",
           "User 4"
  );

    public static function processInData($data = array()) {
      $result = array();
      if(count($data) > 0) {
        for($i = 0; $i < count($data); $i++) {
          foreach(self::$CSV_profile_keys as $key1 => $key2) {
            if(isset($data[$i][$key1]) && (!empty($key2)) && !is_numeric($key1)) {
              $path = explode('|', $key2);
              $section = $path[0];
              $field   = $path[1];
              if(empty($data[$i][$key1]) && (!empty($result[$i][$section][$field]))) continue;
              if(!empty($data[$i][$key1])) {
                $result[$i][$section][$field] = $data[$i][$key1];
              }
            } else if(is_numeric($key1)) {
              preg_match_all("#([A-Z\-][a-z0-9\-]*)#", $key2, $matches);
              $kname = strtolower(implode('_', $matches[0]));
              if(!empty($data[$i][$key2])) {
//                echo "Data[$i]: ". $data[$i][$key2] . '<br />';
                $result[$i]['extra'][$kname] = $data[$i][$key2];
              }
            }
          }
        }
      }
      return $result;
    }

    public static function processOutData() {

    }

}
