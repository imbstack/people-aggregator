<?php
require_once 'api/Entity/TypedGroupEntity.php';
require_once("api/Entity/TypedGroupEntityRelation.php");
class FamilyTypedGroupEntity extends TypedGroupEntity {

	// all this is supposed to add is the list of profile fields a Business has
	public function get_profile_fields() {
		$profile = array();
		$profile[] = array(
			'name' => 'established',
			'label' => __("Date the Family was formed"),
			'type' => 'dateselect',
			'sort' => true
		);
		$profile[] = array(
			'name' => 'city',
			'label' => __("City of Origin"),
			'type' => 'textfield',
			'sort' => true
		);
		$profile[] = array(
			'name' => 'state',
			'label' => __("State/Province"),
			'type' => 'stateselect',
			'sort' => true
		);
		$profile[] = array(
			'name' => 'country',
			'label' => __("Country"),
			'type' => 'countryselect',
		);
		return $profile;
	}

	public function get_avail_relations() {
		return  array(
			'grandparent' => __("Grand Parent"),
			'parent' => __("Parent"),
			'relative' => __("Relative"),
			'child' => __("Child")
		);
	}
	

}
?>