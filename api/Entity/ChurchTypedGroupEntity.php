<?php
class ChurchTypedGroupEntity extends TypedGroupEntity {
	// all this is supposed to add is the list of profile fields a Business gas
	public function get_profile_fields() {
		$profile = array();
		$profile[] = array(
			'name' => 'religion',
			'label' => __("Religion / Faith"),
			'type' => 'religionselect'
		);
		$profile[] = array(
			'name' => 'established',
			'label' => __("Date Established"),
			'type' => 'dateselect'
		);
		$profile[] = array(
			'name' => 'address',
			'label' => __("Address"),
			'type' => 'textfield'
		);
		$profile[] = array(
			'name' => 'city',
			'label' => __("City"),
			'type' => 'textfield'
		);
		$profile[] = array(
			'name' => 'state',
			'label' => __("State/Province"),
			'type' => 'stateselect',
		);
		$profile[] = array(
			'name' => 'country',
			'label' => __("Country"),
			'type' => 'countryselect',
		);
		$profile[] = array(
			'name' => 'zip',
			'label' => __("Postal Code"),
			'type' => 'textfield'
		);
		$profile[] = array(
			'name' => 'phone',
			'label' => __("Phone Number"),
			'type' => 'textfield'
		);
		$profile[] = array(
			'name' => 'website',
			'label' => __("Website"),
			'type' => 'urltextfield'
		);
		return $profile;
	}

	public function get_avail_relations() {
		return  array(
			'congregational_member' => __("Congregational member"),
			'church_member' => __("Church member"),
			'church:goer' => __("Church goer"),
		);
	}
}
?>