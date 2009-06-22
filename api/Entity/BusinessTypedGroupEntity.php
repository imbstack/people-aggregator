<?php
class BusinessTypedGroupEntity extends TypedGroupEntity {

	// all this is supposed to add is the list of profile fields a Business has
	public function get_profile_fields() {
		$profile = array();
		$profile[] = array(
			'name' => 'established',
			'label' => __("Date Established"),
			'type' => 'dateselect',
			'sort' => true
		);
		$profile[] = array(
			'name' => 'address',
			'label' => __("Address"),
			'type' => 'textfield'
		);
		$profile[] = array(
			'name' => 'city',
			'label' => __("City"),
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
		$profile[] = array(
			'name' => 'industry',
			'label' => __("Industry"),
			'type' => 'industryselect',
			'sort' => true
		);
		$profile[] = array(
			'name' => 'honors',
			'label' => __("Honors / Awards"),
			'type' => 'textfield'
		);
		return $profile;
	}

	public function get_avail_relations() {
		return  array(
			'supporter' => __("Supporter"),
			'customer' => __("Customer"),
			'employee' => __("Employee")
		);
	}

}
?>