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
require_once 'api/Entity/TypedGroupEntity.php';
require_once("api/Entity/TypedGroupEntityRelation.php");

class FamilyTypedGroupEntity extends TypedGroupEntity {
    // all this is supposed to add is the list of profile fields a Business has
    public function get_profile_fields() {
        $profile = array();
        $profile[] = array(
            'name'  => 'established',
            'label' => __("Date the Family was formed"),
            'type'  => 'dateselect',
            'sort'  => true,
        );
        $profile[] = array(
            'name'  => 'city',
            'label' => __("City of Origin"),
            'type'  => 'textfield',
            'sort'  => true,
        );
        $profile[] = array(
            'name'  => 'state',
            'label' => __("State/Province"),
            'type'  => 'stateselect',
            'sort'  => true,
        );
        $profile[] = array(
            'name'  => 'country',
            'label' => __("Country"),
            'type'  => 'countryselect',
        );
        return $profile;
    }

    public function get_avail_relations() {
        return array('grandparent' => __("Grand Parent"), 'parent' => __("Parent"), 'relative' => __("Relative"), 'child' => __("Child"), // 'friend' => __("Friend of Family"));
    }
    // determine if two PA users are member of the same family (or affiliated to it)
    // returns an array of family ids that are shared between the two users
    // array us empty if none a re shared
    static

    function in_same_family($user_id1, $user_id2) {
        $in_same_family = array();
        // get all families for each user
        $user_1_families = TypedGroupEntityRelation::get_relation_for_user($user_id1, 'family', false);
        $user_2_families = TypedGroupEntityRelation::get_relation_for_user($user_id2, 'family', false);
        $fam1            = array();
        $fam2            = array();
        foreach($user_1_families as $i => $fam) {
            // we make a real string of the id here, so we don't get confusion with integer indices
            $fam1["family_".$fam->object_id] = $fam->relation_type;
        }
        foreach($user_2_families as $i => $fam) {
            // we make a real string of the id here, so we don't get confusion with integer indices
            $fam2["family_".$fam->object_id] = $fam->relation_type;
        }
        foreach($fam1 as $id => $relation) {
            if(!empty($fam2[$id])) {
                $in_same_family[] = $id;
            }
        }
        return $in_same_family;
    }
}
?>