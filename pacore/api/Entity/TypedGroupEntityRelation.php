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
require_once("api/Entity/EntityRelation.php");
require_once("api/Entity/TypedGroupEntity.php");

class TypedGroupEntityRelation extends EntityRelation {

    public static function set_relation($uid, $gid, $relation_type) {
        if(empty($relation_type)) {
            $relation_type = 'member';
        }
        // we need more info about the entity
        $entity_info = TypedGroupEntity::load_for_group($gid, false);
        if(empty($entity_info)) {
            return FALSE;
        }
        $relation = array(
            'subject_service' => 'internal',
            'subject_type'    => 'user',
            'subject_id'      => $uid,
            'object_service'  => 'typedGroup',
            'object_type'     => $entity_info['entity_type'],
            'object_id'       => $gid,
        );
        parent::delete_match($relation);
        $relation['relation_type'] = $relation_type;
        $relation['start_time']    = time();
        $relation['attributes']    = array();
        parent::sync($relation);
    }

    public static function delete_relation($uid, $gid) {
        // we need more info about the entity
        $entity_info = TypedGroupEntity::load_for_group($gid, false);
        $relation = array(
            'subject_service' => 'internal',
            'subject_type'    => 'user',
            'subject_id'      => $uid,
            'object_service'  => 'typedGroup',
            'object_type'     => $entity_info['entity_type'],
            'object_id'       => $gid,
        );
        parent::delete_match($relation);
    }

    public static function delete_all_relations($gid) {
        // we need more info about the entity
        $entity_info = TypedGroupEntity::load_for_group($gid, false);
        $relation = array(
            'subject_service' => 'internal',
            'subject_type'    => 'user',
            'object_service'  => 'typedGroup',
            'object_type'     => $entity_info['entity_type'],
            'object_id'       => $gid,
        );
        parent::delete_match($relation);
    }

    public static function get_relation_to_group($uid, $gid) {
        // we need more info about the entity
        $entity_info = TypedGroupEntity::load_for_group($gid, false);
        $relation = array(
            'subject_service' => 'internal',
            'subject_type'    => 'user',
            'subject_id'      => $uid,
            'object_service'  => 'typedGroup',
            'object_type'     => $entity_info['entity_type'],
            'object_id'       => $gid,
        );
        $match = parent::load_match($relation);
        if(!empty($match[0])) {
            // get info about what profile fields this has
            $type = $match[0]->object_type;
            $classname = ucfirst($type)."TypedGroupEntity";
            @include_once "api/Entity/$classname.php";
            if(!class_exists($classname)) {
                $classname = "TypedGroupEntity";
            }
            $instance       = new $classname();
            $label          = (!empty($availRelations[$match[0]->relation_type])) ? $availRelations[$match[0]->relation_type] : ucfirst($match[0]->relation_type);
            $availRelations = $instance->get_avail_relations();
            return array($match[0]->relation_type, $label);
        }
        return NULL;
    }

    public static function get_relation_for_user($uid, $type = NULL, $load_attrs = true) {
        $relation = array(
            'subject_service' => 'internal',
            'subject_type'    => 'user',
            'subject_id'      => $uid,
            'object_service'  => 'typedGroup',
        );
        if($type) {
            $relation['object_type'] = $type;
        }
        $match = parent::load_match($relation, $load_attrs);
        return $match;
    }
}
?>