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
function get_content_type($post_type) {
    $content_type = NULL;
    switch($post_type) {
        case 'blog':
            $content_type = BLOGPOST;
            break;
        case 'image':
            $content_type = IMAGE;
            break;
        case 'audio':
            $content_type = AUDIO;
            break;
        case 'video':
            $content_type = VIDEO;
            break;
        default:
            $content_type = NULL;
    }
    return $content_type;
}

function sanitize_user_data($user_data_array) {
    $user_data_array_final = array();
    for($counter = 0; $counter < count($user_data_array); $counter++) {
        $field_name                              = $user_data_array[$counter]['name'];
        $field_value                             = $user_data_array[$counter]['value'];
        $permission_name                         = $field_name."_perm";
        $permission_value                        = $user_data_array[$counter]['perm'];
        $user_data_array_final[$field_name]      = $field_value;
        $user_data_array_final[$permission_name] = $permission_value;
    }
    return $user_data_array_final;
}

/* Function to unset modules from the setting array. */
function delete_module_from_array($module, $setting_array) {
    if(!is_array($module)) {
        $modules[] = $module;
    }
    else {
        $modules = $module;
    }
    if(!empty($setting_array)) {
        $setting_array = array_flip($setting_array);
        foreach($modules as $key) {
            if(array_key_exists($key, $setting_array)) {
                unset($setting_array[$key]);
            }
        }
        $setting_array = array_flip($setting_array);
    }
    return $setting_array;
}
?>