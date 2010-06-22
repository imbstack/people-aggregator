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
* @class PlaxoDataMapper
*
* @author     Zoran Hron <zhron@broadbandmechanics.com>
* @version    0.1.0
*
*
* @brief class PlaxoDataMapper for converting Plaxo to PA data format and vice versa
*
*
**/
require_once "api/ProfileIO/map/DataMapper.intf.php";

class PlaxoDataMapper implements DataMapper {

    public static $plaxo_profile_keys = array(
        'AIMScreenName',
        'Anniversary',
        'AssistantName',
        'AssistantPhone',
        'Birthday' => 'general|dob',
        'BusinessEmail' => 'professional|email',
        'BusinessEmail2' => 'professional|email',
        'BusinessEmail3' => 'professional|email',
        'BusinessIM',
        'BusinessMicroBlog',
        'BusinessMobilePhone',
        'BusinessPhoto',
        'BusinessWebPage' => 'professional|website',
        'Category',
        'Company' => 'professional|company',
        'ContactIdentifier',
        'Department',
        'DisplayName',
        'FamilyName' => 'personal|last_name',
        'FirstName' => 'personal|first_name',
        'HomeAddress' => 'general|address',
        'HomeAddress2' => 'general|address',
        'HomeAddress3' => 'general|address',
        'HomeCity' => 'general|city',
        'HomeCountry' => 'general|country',
        'HomeFax',
        'HomePhone' => 'general|phone',
        'HomePhone2' => 'general|phone',
        'HomeState' => 'general|state',
        'HomeZipCode' => 'general|postal_code',
        'JobTitle' => 'professional|title',
        'LastName' => 'personal|last_name',
        'ManagerName',
        'MiddleName',
        'NameSuffix',
        'NameTitle',
        'NickName' => 'personal|nick_name',
        'Notes' => 'general|summary',
        'NumberOfRecentChanges',
        'OtherAddress',
        'OtherAddress2',
        'OtherAddress3',
        'OtherCity',
        'OtherCountry',
        'OtherFax',
        'OtherPhone',
        'OtherState',
        'OtherZipCode',
        'PersonalEmail' => 'personal|email',
        'PersonalEmail2' => 'personal|email',
        'PersonalEmail3' => 'personal|email',
        'PersonalIM',
        'PersonalMicroBlog',
        'PersonalMobilePhone',
        'PersonalPhoto' => 'personal|picture',
        'PersonalWebPage' => 'general|website',
        'PlaxoState',
        'PreferredAddress',
        'SkypeID',
        'SkypeIn',
        'SpouseName',
        'WorkAddress',
        'WorkAddress2',
        'WorkAddress3',
        'WorkCity',
        'WorkCountry',
        'WorkFax',
        'WorkPager',
        'WorkPhone',
        'WorkPhone2',
        'WorkState',
        'WorkZipCode',
    );

    public static function processInData($data = array()) {
        $result = array();
        if(count($data) > 0) {
            for($i = 0; $i < count($data); $i++) {
                foreach(self::$plaxo_profile_keys as $key1 => $key2) {
                    if(isset($data[$i][$key1]) && (!empty($key2)) && !is_numeric($key1)) {
                        $path = explode('|', $key2);
                        $section = $path[0];
                        $field = $path[1];
                        if(empty($data[$i][$key1]) && (!empty($result[$i][$section][$field]))) {
                            continue;
                        }
                        if(!empty($data[$i][$key1])) {
                            $result[$i][$section][$field] = $data[$i][$key1];
                        }
                    }
                    elseif(is_numeric($key1)) {
                        preg_match_all("#([A-Z][a-z0-9]*)#", $key2, $matches);
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
