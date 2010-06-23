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
require_once 'api/Entity/Entity.php';
require_once 'api/Group/Group.php';

class TypedGroupEntity extends Entity {

	public static function load_for_group($group_id, $load_entity=true) {
		$result = Entity::search(
			array(
				'entity_service' => 'typedGroup',
				'entity_id' => $group_id,
			)
		);
		$entity = NULL;
		if (!empty($result)) {
			$entity_result = $result[0];
			if (empty($load_entity)) {
				return $entity_result;
			}
			$entity = Entity::load( // TypedGroup
				$entity_result['entity_service'],
				$entity_result['entity_type'],
				$entity_result['entity_id']);
			// load extra group info for logo and sloagan
			try {
				$group = ContentCollection::load_collection((int)$entity_result['entity_id']);
				$entity->attributes['name']['value'] = $group->title;
				$entity->attributes['logo']['value'] = $group->picture;
				$entity->attributes['slogan']['value'] = $group->description;
			} catch (PAException $e) {
				return NULL;
			}
		}
		return $entity;
	}
	
	public static function delete_for_group($group_id) {
		Entity::delete_match(
			array(
				'entity_service' => 'typedGroup',
				'entity_id' => $group_id,
			)
		);
	}
	
	public static function get_count($entityType) {
		$result = Entity::search(
			array(
				'entity_service' => 'typedGroup',
				'entity_type' => $entityType,
			)
		);
		return count($result);
	}
	
	public static function get_entities($entityType, $sort_by=null, $page=1, $show=null) {
		$result = Entity::search(
			array(
				'entity_service' => 'typedGroup',
				'entity_type' => $entityType,
			),
			null, // attributes
			'=', // search method
			$sort_by, $page, $show
		);
		$entities = array();
		foreach ($result as $i=>$entity_result) {
			if ($entity = TypedGroupEntity::load_for_group($entity_result['entity_id'])) {
				$entities[$i] = $entity;
			} 
		}
		return $entities;
	}
	
	public static function get_avail_types() {
		 
		return PA::$config->enum_typed_group_types;
	}
	public function get_avail_relations() {
		 
		return PA::$config->enum_typed_group_relations;
	}
	public function get_profile_fields() {
		 
		return PA::$config->typed_group_profilefields;
	}

	public static function sync($data) {
		$entity = (object)array(
			'entity_service' => 'typedGroup',
			'entity_type' => $data['type'],
			'entity_id' =>	$data['group_id'],
			'entity_name' => $data['name'],
			'attributes' => $data,
		);
		parent::sync($entity);
		// also update the group that this corresponds to
		$g = new Group();
		try {
			$g->load((int)$data['group_id']);
			$g->group_type = 'typedgroup';
			$g->save();
		} catch (PAException $e) {
			throw $e;
		}
	}
}
?>