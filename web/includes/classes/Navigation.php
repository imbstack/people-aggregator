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
 * Class for creating navigation links
 * This file is having a class Navigation.
 * @package web
 * @author Tekriti Software (http://www.tekritisoftware.com)
 * Date of creation 10 July 2006
 */

require_once "api/Group/Group.php";

/**
 * constant for superuser Session ID
 */
 define("SUPERUSER",1);
class Navigation {

  /**
   * $level_1
   * @var array - holds first level links
   * @access public
  */
  public $level_1 = array();

  /**
   * $level_2
   * @var array - holds second level links
   * @access public
  */
  public $level_2 = array();

  /**
   * $level_3
   * @var array - holds third level links
   * @access public
  */
  public $level_3 = array();

  /**
   * $uid
   * @var integer - user id of logged in user or get variable
   * @access public
  */
  public $uid;

  /**
   * $friend_uid
   * @var integer - user id of logged in user's friend
   * @access public
  */
  public $friend_uid;

  /**
   * $mothership_info
   * @var array - holds the address and other info of mother network
   * @access public
  */
  public $mothership_info;

  /**
   * $network_info
   * @var array - holds the info of current network
   * @access public
  */
  public $network_info;

  /**
   * $base_url
   * @var string - holds url of base directory, gets value from global $base_url
   * @access public
  */
  public $base_url;

  /**
   * $current_page
   * @var string - holds name of running php script e.g. homepage.php
   * @access public
  */
  public $current_page;

  /**
   * $group_id
   * @var integer - holds gid of group generally from get variable
   * @access public
  */
  public $group_id;

  /**
   * $users_first_group_id
   * @var integer - holds gid of first group of a user generally from get variable
   * @access public
   */
  public $users_first_group_id;

  public $is_anonymous=FALSE;
  /**
  constructor
  It is initialized by :
  i) mothership_info - info of mother network
  ii) network info - global - info of current network if any
  iii) base url of the site
  iv) current page - holding the name of the script currently running

  **/
  public function __construct(){
    Logger::log("Enter: Content::__construct");

    $this->mothership_info = mothership_info();
    $this->network_info = PA::$network_info;
    if ( !empty($this->network_info) ) {
      $this->nid = $this->network_info->network_id;
    }
    $this->base_url = PA::$url;                                                   // NOTE:
    if (preg_match("|/([a-zA-Z0-9._-]+\.php)$|", $_SERVER['SCRIPT_NAME'], $m)) {  // This code part should be removed
      if(false !== strpos($_SERVER['SCRIPT_NAME'] , 'dynamic.php')) {             // after all modules has been
        $this->current_page = $_REQUEST['page_id'];                               // refactored and adapted to new architecture
      } else {                                                                    // after that all constants that beginning with
        $this->current_page = $m[1];                                              // FILE_ should be replaced with PAGE_
      }
    } else {
      $this->current_page = NULL;
    }
    Logger::log("Exit: Content::__construct");
  }

  /**
  Purpose : this function is used to set is_anonymous var. if user is not logged in set it to TRUE
  Scope : public
  **/
  public function set_anonymous(){
    $this->is_anonymous = TRUE;
  }

  /**
  Purpose : this function is used to set the uid of concerned user
  Scope : public
  @param $uid - user id
  @return - it returns nothing just sets the class variable
  **/
  public function set_uid($uid) {
    $this->uid = $uid;
  }

  /**
  Purpose : this function is used to get the value of uid
  Scope : public
  @param - it needs no direct input
  @return - it returns uid - value of class var
  **/
  function get_uid() {
    return $this->uid;
  }

  /**
  Purpose : this function is used to set the friend uid of concerned user
  Scope : public
  @param $friend_uid - friend id
  @return - it returns nothing just sets the class variable
  **/
  function set_friend_uid($friend_uid) {
    $this->friend_uid = $friend_uid;
  }

  /**
  Purpose : this function is used to get the value of friend uid
  Scope : public
  @param - it needs no direct input
  @return - it returns friend_uid - value of class var
  **/
  function get_friend_uid() {
    return $this->friend_uid;
  }

  /**
  Purpose : this function is used to set the group id
  Scope : public
  @param $group_id - group id
  @return - it returns nothing just sets the class variable
  **/
  public function set_group_id($group_id){
    $this->group_id = $group_id;
  }

  /**
  Purpose : this function is used to get the value of group id
  Scope : public
  @param - it needs no direct input
  @return - it returns group_id - value of class var
  **/
  function get_group_id() {
    return $this->group_id;
  }

  /**
  Purpose : this function is used to set the first group's id of user
  Scope : public
  @param $user_group_id - group id
  @return - it returns nothing just sets the class variable
  **/
  public function users_first_group_id($users_first_group_id){
    $this->users_first_group_id = $users_first_group_id;
  }

  /**
  Purpose : this function is used to get the value of first group's id
  Scope : public
  @param - it needs no direct input
  @return - it returns group_id - value of class var
  **/
  function get_users_first_group_id() {
    return $this->users_first_group_id;
  }

  /**
  Purpose : this function is core funtion of this Navigation class. It is used to make links level_1,level_2,level_3
  Some links need extra parameters
  append them here but first set them in their methods
  e.g.
    public function set_group_id($group_id){
      $this->group_id = $group_id;
    }
    public function get_group_id() {
      return $this->group_id();
    }
  Scope : public
  @param - it needs no direct input
  @return - it sets class variables level_1,level_2,level_3 which can be used further.
  **/
  function make_links() {
     

    $user_id = (isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : 0;
    ////These are level 1 links shown in top navigation bar
    $level_1 = array(
      'home_network' => array(
	'caption'=>__('Return to home network'),
	'url'=>$this->mothership_info['url'])
	);
    // Display network directory, if network operation is enabled.
    if (PA::$network_capable) {
      $level_1['networks_directory'] = array(
	'caption'=>__('Network directory'),
	'url'=>$this->base_url .'/'. FILE_NETWORKS_HOME);
    }
  $owner = Network::is_admin($this->network_info->network_id, $user_id) ;

  //is_member will be true when user is registered member of the nework.
  $is_member = Network::member_exists($this->network_info->network_id, $this->get_uid());
    if (!$this->is_anonymous && $this->network_info && !$is_member && $this->network_info->type != MOTHER_NETWORK_TYPE) {
      $level_1['join_network'] = array(
      'caption'=>__('JOIN Network'),
      'url' => $this->base_url .'/'. FILE_NETWORK_ACTION. '?action=join&amp;nid='. $this->network_info->network_id .'&amp;cid='. $this->network_info->category_id);
    } else if(!$this->is_anonymous && $is_member && !$owner && $this->network_info->type != MOTHER_NETWORK_TYPE) {
      $level_1['unjoin_network'] = array(
      'caption'=>__('Unjoin Network'),
      'url' => $this->base_url .'/'. FILE_NETWORK_ACTION. '?action=leave&amp;nid='. $this->network_info->network_id .'&amp;cid='. $this->network_info->category_id);
    } else if ((Network::is_admin($this->network_info->network_id, (int)$user_id)) ||
                // fix by Z.Hron - on David's suggestion: User with any administration task should be able to access to Configure Network.
                (($user_id > 0) and Roles::check_administration_permissions((int)$user_id))) {
      $level_1['configure_network'] = array('caption'=>__('Configure'),
                                                'url'=>$this->base_url . PA_ROUTE_CONFIGURE_NETWORK
                                              );
    }
    if ($this->network_info->type == MOTHER_NETWORK_TYPE)  {
      unset($level_1['home_network']);
    }

    if (PA::$config->enable_network_spawning) {
      $level_1['create_network'] = array(
	'caption'=>__('Create a network'),
	'url'=>$this->mothership_info['extra']['links']['create_network']);
    }

    ////END OF These are level 1 links shown in top navigation bar
    ////These are level 2 links shown in second navigation bar
    $level_2 = array('home' => array('caption'=>__('Home'),
                                  'url'=>$this->base_url . PA_ROUTE_HOME_PAGE
                                  ),
                       'user' => array('caption'=> __(PA::$mypage_noun),
                                  'url'=>$this->base_url . PA_ROUTE_USER_PRIVATE
                                  ),
                       'people' => array('caption'=> __(PA::$people_noun),
                                  'url'=>$this->base_url . PA_ROUTE_PEOPLES_PAGE
                                  ),
                       'groups' => array('caption'=> __(PA::$group_noun_plural),
                                  'url'=>$this->base_url . PA_ROUTE_GROUPS
																	),
    										);
		if (!empty(PA::$config->useTypedGroups)) {
			$level_2 = $level_2 + array(
                       'directory' => array('caption'=>__('Orgs'),
                                  'url'=>$this->base_url.PA_ROUTE_TYPED_DIRECTORY
                              ),
    										);
    	$level_2 = $level_2 + array(
                       'families' => array('caption'=>__('Neighbors'),
                                  'url'=>$this->base_url.PA_ROUTE_FAMILY_DIRECTORY
                              ),
    										);

    }
    
		
		$level_2 = $level_2 + array('forum' => array('caption'=>__('Forum'),
                                  'url'=>$this->base_url . PA_ROUTE_FORUMS . "/network_id=" .$this->network_info->network_id,
                                  ),
                       'search' => array('caption'=>__('Search'),
                                  'url'=>$this->base_url . PA_ROUTE_SEARCH_HOME .'/btn_searchContent=Search+Content',
                                  )
                       );
    ////END OF These are level 2 links shown in second navigation bar

    /// children of user 2nd level link
    $uid = $this->get_uid();
    //we need uid for some links
    $user_children = array();
    $user_children = $user_children + array(
                       'user_private' => array('caption'=>__('My Page'),
                                  'url'=>$this->base_url . PA_ROUTE_USER_PRIVATE),
                                  );
    $user_children = $user_children + array(
                       'user_widgets' => array('caption'=>__('My Widgets'),
                                  'url'=>$this->base_url.'/'.FILE_WIDGET
                                   ),
                                  );
    $user_children = $user_children + array(
                       'messages' => array('caption'=>__('My Messages'),
                                  'url'=>$this->base_url . PA_ROUTE_MYMESSAGE
                                  ),
                                  );
    $user_children = $user_children + array(
                       'my_gallery' => array('caption'=>__('My Gallery'),
                                  'url'=>$this->base_url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=$uid"
                                  ),
                                  );
    $user_children = $user_children + array(
                       'my_events' => array('caption'=>__('My Events'),
                                  'url'=>$this->base_url.'/'.FILE_USER_CALENDAR
                                  ),
                       'my_friends' => array('caption'=>__('My Friends'),
                                  'url'=>$this->base_url . "/view_all_members.php?view_type=in_relations&amp;uid=$uid"
                                  ),
                                  );
    $user_children = $user_children + array(
                       'my_forum'  => array('caption'=>__('My Forum'),
                                  'url'=>$this->base_url. PA_ROUTE_FORUMS . "/network_id=" .$this->network_info->network_id . '&user_id='.$uid
                                  ),
                                  );
    $user_children = $user_children + array(
                       'my_points'  => array('caption'=>__('My Points'),
                                  'url'=>$this->base_url. PA_ROUTE_POINTS_DIRECTORY . "?uid=$uid"
                                  ),
                                  );

		// get this users Family or Families
		require_once "api/Entity/TypedGroupEntityRelation.php";
		$userfamilyRelations = TypedGroupEntityRelation::get_relation_for_user($uid, 'family');
		if (count($userfamilyRelations) == 1) {
    $user_children = $user_children + array(
    	'my_family'  => array('caption'=>__('My Family'),
    	'url' => $this->base_url. PA_ROUTE_FAMILY . "?gid=" . $userfamilyRelations[0]->object_id
    	));
		} else {
			$html = "<ul>";
			foreach($userfamilyRelations as $i=>$relation) {
				$group = ContentCollection::load_collection((int)$relation->object_id, PA::$login_uid);
// echo "<pre>".print_r($group, 1)."</pre>";exit;
				$html .= "<li>";
				$html .= "<a href=\"".
					$this->base_url. PA_ROUTE_FAMILY . "?gid=" . $relation->object_id
				."\">".$group->title."</a>";
				$html .= "</li>";
			}
			$html .= "</ul>";
    $user_children = $user_children + array( 
    	'my_family'  => array('caption'=>__('My Families'),
    	'html' => $html
    	));
		}

    $user_children = $user_children + array(
                       'settings' => array('caption'=>__('Edit My Account'),
                                  'url'=>$this->base_url.PA_ROUTE_EDIT_PROFILE
                                  ),
                                  );
    $user_children = $user_children + array(
                       'customize_ui' => array('caption'=>__('Themes'),
                                  'url'=>$this->base_url . PA_ROUTE_CUSTOMIZE_USER_GUI . "/theme/uid=$uid"
                                  ) 
                       );
     if ( $this->is_anonymous ) {
      //these links are not for anonymous
      unset($user_children);
     }
    ///END OF children of user 2nd level link

    /// children of people 2nd level link
    //required friend id in some places
    $friend_id = $this->get_friend_uid();
    $people_children = array('find_people' => array('caption'=>sprintf(__('Find %s'), __(PA::$people_noun)),
                                  'url'=>$this->base_url . PA_ROUTE_PEOPLES_PAGE
                                  ),
                       'my_friends' => array('caption'=>__('My friends'),
                                  'url'=>$this->base_url.'/'.FILE_VIEW_ALL_MEMBERS.'?view_type=relations&amp;uid='.$uid
                                  ),
                       /* 'people_who_call_me_friend' => array('caption'=>sprintf(__('%s who call me friend'), __(PA::$people_noun)),
                                  'url'=>$this->base_url.'/'.FILE_VIEW_ALL_MEMBERS.'?view_type=in_relations&amp;uid='.$uid
                                  ), */
                       'friends_gallery' => array('caption'=>__('Friends gallery'),
                                  'url'=>$this->base_url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=$friend_id&view=friends",
                                 )
                       );
    if ( $this->is_anonymous ) {
      //these links are not for anonymous
      unset($people_children);
     }
    ///EOF children of people 2nd level link
    
    $family_children = array(
    	'neighbors' => array(
    		'caption' => __("Neighbors"),
    		'url' => $this->base_url . PA_ROUTE_FAMILY_DIRECTORY
    	),
    	'family_home' => array(
    		'caption' => __("Family Homepage"),
    		'url' => $this->base_url . PA_ROUTE_FAMILY . "/gid=" . $this->group_id
    	),
    	'family_members' => array(
    		'caption' => __("Family Members"),
    		'url' => $this->base_url . PA_ROUTE_FAMILY_MEMBERS . "/gid=" . $this->group_id
    	),
    );

    /// group general children
    //    $users_first_group_id = $this->get_users_first_group_id();
    $groups_general =  array('find_groups' => array('caption'=>sprintf(__('Find %s'), __(PA::$group_noun_plural)),
                                  'url'=>$this->base_url . PA_ROUTE_GROUPS
                                  ),
                       'create_group' => array('caption'=>__('Create'),
                                  'url'=>$this->base_url.'/'.FILE_ADDGROUP
                                  ),
                       'invite' => array('caption'=>__('Invite'),
                                  'url'=>$this->base_url.'/'.FILE_GROUP_INVITATION
                                  ),
			     );
     if ( $this->is_anonymous ) {
      //these links are not for anonymous
      unset($groups_general['create_group']);
      unset($groups_general['invite']);
      unset($groups_general['group_media_gallery']);
     }
    /// EOF group general children

    ///group specific menu children
    $group_id = $this->get_group_id();
    $group_specific =  array('group_home' =>
                              array('caption'=>sprintf(__('%s Home'), __(PA::$group_noun)),
                                      'url'=>$this->base_url . PA_ROUTE_GROUP . '/gid='.$group_id
                                   ),
                             'group_forum'=>
                             array('caption'=>sprintf(__('%s Forum'), __(PA::$group_noun)),
                                   'url'=> $this->base_url . PA_ROUTE_FORUMS . "/network_id=" .$this->network_info->network_id . '&gid='.$group_id
                                  ),
                             'group_members' =>
                              array('caption'=>sprintf(__('%s Members'), __(PA::$group_noun)),
                                    'url'=> $this->base_url.'/'.FILE_VIEW_ALL_MEMBERS.'?gid='.$group_id
                                    ),
                            'group_gallery' =>
                             array('caption'=>sprintf(__('%s Gallery'), __(PA::$group_noun)),
                                   'url'=> $this->base_url . PA_ROUTE_MEDIA_GALLEY_IMAGES . '/view=groups_media&amp;gid='.$group_id
                                  ),
                            'group_events' =>
                             array('caption'=>sprintf(__('%s Events'), __(PA::$group_noun)),
                                   'url'=> $this->base_url.'/'.FILE_GROUP_CALENDAR.'?gid='.$group_id
                                  ),
                       'join' => array('caption'=>__('Join'),
                                  'url'=>$this->base_url . PA_ROUTE_GROUP . '/gid='.$group_id.'&amp;action=join'
                                  ),
                        'unjoin' => array('caption'=>__('Unjoin'),
                                  'url'=>$this->base_url . PA_ROUTE_GROUP .'/gid='.$group_id.'&amp;action=leave'
                                  ),
                       'delete_group' => array('caption'=>__('Delete'),
                                 'url'=>$this->base_url . PA_ROUTE_GROUP . '/action=delete&amp;gid='.$group_id,
                                  'extra'=>' onclick ="return delete_confirmation_msg(\''.__('Are you sure you want to delete this group').'?\') "'
                                  ),
                      'group_customize_ui' => array('caption'=>__('Themes'),
                                      'url'=>$this->base_url. PA_ROUTE_CUSTOMIZE_GROUP_GUI . '/theme/gid='.$group_id
                                   ),
                            );
    /// group links are having some more complicated logic
    // following links are not visible to anonymous

    if ($this->is_anonymous) {
      unset($group_specific['create_group']);
      unset( $group_specific['join'] );
      unset( $group_specific['unjoin'] );
      unset($group_specific['edit_group']);
      unset($group_specific['invite']);
      unset($group_specific['delete_group']);
      unset($group_specific['moderate_group']);
      unset($group_specific['group_customize_ui']);
    } else if(!empty($group_id) && !Group::is_admin($group_id, $user_id)) {
      unset($group_specific['edit_group']);
      unset($group_specific['delete_group']);
      unset($group_specific['moderate_group']);

      if( Group::member_exists($group_id, $user_id) == TRUE)  {
      unset( $group_specific['join'] );
    }
    else if (Group::member_exists($group_id, $user_id) == FALSE ){
      unset( $group_specific['unjoin'] );
    }
     unset($group_specific['group_customize_ui']);
   }
   else if(!empty($group_id) && Group::is_admin($group_id, $user_id)) {
      unset( $group_specific['join'] );
      unset( $group_specific['unjoin'] );
   }

    ///EOF group specific menu children

    /// children of group

    $groups_children = array('groups_general' => $groups_general,
                          'group_specific' => $group_specific
                        );

    ///EOF children of group



    //for network option at 3 level

    $network =  array(
                       'configure_network'=> array('caption'=> __('Configure'),
                                  'url'=>$this->base_url . PA_ROUTE_CONFIGURE_NETWORK
                                 ),

                       )
                    + $level_2;


    $network_notify = array(
                             'email_notification' => array('caption'=>__('Email Notification'),
                                  'url'=>$this->base_url.'/'.FILE_EMAIL_NOTIFICATION
                                  ),
                             'network_bulletins' => array('caption'=>__('Bulletins'),
                                  'url'=>$this->base_url.'/'.FILE_NETWORK_BULLETINS
                                  )

                           );

    $network_setting = array(
                            'network_feature' =>
                             array('caption'=>__('Set Feature Network'),
                                   'url'=>$this->base_url.'/'.FILE_NETWORK_FEATURE
                                  ),
                            'manage_emblem' => array('caption'=>__('Manage Emblem'),
                                  'url'=>$this->base_url.'/'.FILE_MANAGE_EMBLEM
                                  ),
                           'manage_taketour' => array('caption'=>__('Personalized Video'),
                                  'url'=>$this->base_url.'/'.FILE_MANAGE_TAKETOUR
                                  ),
                           'splash_page' => array('caption'=>__('Configure Splash Page'),
                                  'url'=>$this->base_url.'/'.FILE_CONFIGURE_SPLASH_PAGE
                                 ),
                            'top_bar' => array('caption'=>__('Top Bar Enable/Disable'),
                                                    'url'=>'#'
                                                   )
                           );
   $network_default = array(
                            'new_user_by_admin' => array('caption'=>__('Create User'),
                                  'url'=>$this->base_url.'/'.FILE_NEW_USER_BY_ADMIN
                                  ),
                            'user_defaults' => array('caption'=>__('User Defaults'),
                                  'url'=>$this->base_url.'/'.FILE_NETWORK_USER_DEFAULTS
                                  ),
                            'relationship_settings' => array('caption'=>__('Relationships'),
                                  'url'=>$this->base_url.'/'.FILE_RELATIONSHIP_SETTINGS
                                  )
                           );
   $manage_network = array(
                            'manage_user' => array('caption'=>__('Manage Users'),
                                  'url'=>$this->base_url.'/'.FILE_NETWORK_MANAGE_USER
                                  ),
                            'manage_content' => array('caption'=>__('Manage Contents'),
                                  'url'=>$this->base_url.'/'.FILE_NETWORK_MANAGE_CONTENT
                                  ),
                            'manage_links' => array('caption'=>__('Manage Links'),
                                  'url'=>$this->base_url.'/'.FILE_NETWORK_LINKS
                                  )
                           );

    $network_stats = array ( 'statistics' => array('caption'=>__('General'),
                                      'url'=>$this->base_url . PA_ROUTE_CONFIGURE_NETWORK
                                     ),
         'customize_ui' => array('caption'=>__('Customize UI'),
                                 'url'=>$this->base_url.'/'.FILE_NETWORK_CUSTOMIZE_UI_PAGE
                                )
            );

     $network_module_selector = array('home_page_id' =>
                                     array('caption'=>__('Home Page'),
                                          'url'=>$this->base_url.'/'
                                          .FILE_MODULE_SELECTOR.
                                          '?page_id=home_page_id'
                                          ),
                                     'user_default_page_id' =>
                                     array('caption'=>__('User Default Page'),
                                           'url'=>$this->base_url.'/'.
                                           FILE_MODULE_SELECTOR
                                           .'?page_id=user_default_page_id'
                                           ),
                                    'group_directory_page_id'
                                     => array('caption'=>__('Group Directory Page'),
                                    'url'=>$this->base_url.'/'.FILE_MODULE_SELECTOR.
                                    '?page_id=group_directory_page_id'
                                             ),
                                    'network_directory_page_id'
                                    => array('caption'=>__('Network Directory Page'),
                                             'url'=>$this->base_url.'/'
                                             .FILE_MODULE_SELECTOR.
                                             '?page_id=network_directory_page_id'
                                             )
                                       );

    if ($this->network_info->type != MOTHER_NETWORK_TYPE) {
      unset($network['meta_network']);
    }
    if ($this->network_info->type != MOTHER_NETWORK_TYPE) {

      unset($network['manage_taketour']);
       unset($network['manage_emblem']);
    }
    //end

    /// second level menu for network
    $level_3 = array('user'=>@$user_children,
                         'people'=>@$people_children,
                         'family'=>@$family_children,
                         'groups'=>$groups_children,
                         'network'=>$network,
                         'network_notify'=>$network_notify,
                         'network_module_selector'=>$network_module_selector,
                         'network_setting'=>$network_setting,
                         'network_default'=>$network_default,
                         'manage_network'=>$manage_network,
                         'network_stats'=>$network_stats
                        );
    ///EOF second level menu children

    ///set level menu items

    $this->level_1 = $level_1;
    $this->level_2 = $level_2;
    $this->level_3 = $level_3;
  }

  /**

  Purpose : this function is used to get top nav links
  Scope : public
  @param - it needs no direct input
  @return - array of links
  **/
  public function get_level_1() {
     return $this->level_1;
  }

  /**
  Purpose : this function is used to get second nav links
  Scope : public
  @param - it needs no direct input
  @return - array of links
  **/
  public function get_level_2() {
     return $this->level_2;
  }

  /**
  Purpose : this function is used to get the value of group id
  Scope : public
  @param - string or array
  if string it takes input of second level identification of link array e.g. get_level_3('user'); => will return children of user

  if array - it is special case of groups where group links can be of two types - group_general and group_specific links
  so passed in following manner
  get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
  @return - array of links
  **/
  public function get_level_3($param=NULL) {
    if (is_array($param)) {
      if ( $param['type'] == 'groups' ) {
        $level3 = $this->level_3[$param['type']][$param['sub_type']];
       }
    }
    else {
      $level3 = $this->level_3[$param];
    }
    return $level3;
  }
  /**
  Purpose : this function is used to get navigation links for the whole page.
  Scope : public
  @param - it needs no direct input. But works only on the basis of current page initialized in __construct()
  @return - array of links
  **/
  public function get_links($optional = NULL) {
    //initialization

    global $dynamic_page;

    if (isset($_SESSION['user']['id'])) {
      $extra = unserialize($this->network_info->extra);
      if (@$extra['reciprocated_relationship'] == NET_YES) {
        $status = APPROVED;
      } else $status = FALSE;
      $relations_ids = Relation::get_relations((int)$_SESSION['user']['id'], $status, PA::$network_info->network_id);
      $user_groups = Group::get_user_groups((int)$_SESSION['user']['id']);
      /* $gid isn't defined in this function, so the following call
       * will probably always return FALSE.  To get rid of the warning
       * under E_ALL, I've replaced the following expression with
       * FALSE.  Maybe $gid should be get_group_id()? */
      $is_owner_of_group = FALSE; //Group::is_admin($gid,(int)$_SESSION['user']['id']) ;
    }
    if ( isset($relations_ids) && sizeof($relations_ids) ) {
      $this->set_friend_uid($relations_ids[0]);
    }
    if ( isset($user_groups) && sizeof($user_groups) ) {
      $this->users_first_group_id($user_groups[0]['gid']);
    }
    if ( PA::$login_uid ) {
      $this->set_uid(PA::$login_uid);
    }
    else {
      $this->set_anonymous();
    }

    $is_group_content = FALSE;
    if ( @$_GET['gid'] ) {
      $this->set_group_id($_GET['gid']);
    } else if ( (FILE_FORUM_MESSAGES == $this->current_page||FILE_CONTENT == $this->current_page ) && !empty($_REQUEST['ccid']) && $_REQUEST['ccid']>0) {
      $this->set_group_id($_REQUEST['ccid']);
      $is_group_content = TRUE;
    } else if (PAGE_PERMALINK == $this->current_page && !empty($_GET['cid'])) {
	try {
	  $content_data = Content::load_content($_GET['cid'], $this->get_uid());
	} catch (PAException $e) {
	  if ($e->getCode() != CONTENT_NOT_FOUND) throw $e;
	}
	if (isset($content_data)) {
	  if ($content_data->parent_collection_id > 0) {
	    $content_collection_data = ContentCollection::load_collection($content_data->parent_collection_id, $this->get_uid());
	    if ($content_collection_data->type == GROUP_COLLECTION_TYPE) {
	      $this->set_group_id($content_data->parent_collection_id);
	      $is_group_content = TRUE;
	    }
	  }
	}
     }
    //test
    //$this->current_page='test.php';
    // make links for current page
    $this->make_links();
    $level_1 = $this->get_level_1();
    $level_2 = $this->get_level_2();
    $level_3 = NULL;
    $left_user_public_links = NULL;
    if (Network::is_admin($this->network_info->network_id, (int)@$_SESSION['user']['id']) )
      $level_3 = $this->get_level_3( 'network' );
      //if no network_info then it means mother network, here this check is followed by superuser check
    else if( !($this->network_info) && ($_SESSION['user']['id'] == SUPERUSER) ) {
        $level_3 = $this->get_level_3( 'network' );
    }
    $level_3 = NULL;
    switch ( $this->current_page ) {

      /*----------------------------------------------------*/
      case PAGE_HOMEPAGE :
        $level_3 = NULL;
        $level_2['highlight'] = 'home';
      break;
      case FILE_LOGIN:
        $level_2['highlight'] = 'home';
      break;
      case PAGE_SEARCH:
        //fix by Zoran Hron: constants FILE_SEARCH_HOME and FILE_SHOWCONTENT points to the same value !!!
        if(!empty($_GET['gid']))  {
          $level_2['highlight'] = 'groups';
          $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
        } else if (!empty($_GET['btn_searchContent'])){
          $level_2['highlight'] = 'search';
        } else {
          $level_3 = NULL;
          $level_2['highlight'] = 'home';
        }
      break;
      case FILE_TAG_SEARCH:
        $level_2['highlight'] = 'tag_search';
      break;
      /*----------------------------------------------------*/
      case PAGE_USER_PRIVATE:
        global $app;
        $app->setRequestParam('uid', PA::$login_uid, 'POST');
      case PAGE_USER_PUBLIC:
      case PAGE_USER_PRIVATE:
        if (!PA::$page_uid && !PA::$login_uid) {
          throw new PAException("", "Invalid page access");
        }
        if ( PA::$page_uid ) {//uid get variable set
          //these links are to be added in front
          $def_relations_term = 'Friend';
          if (isset($extra['relationship_show_mode']['term'])) {
            $def_relations_term = $extra['relationship_show_mode']['term'];
          }
          $relation_already_exists_links = array('send_message' => array('caption'=>__('Send a message'),
                                  'url'=>$this->base_url.PA_ROUTE_ADDMESSAGE.'/uid='.PA::$page_uid
                                  ),
                       'change_relationship' => array('caption'=> __('Change Relation'),
                                  'url'=>$this->base_url.PA_ROUTE_EDIT_RELATIONS.'/uid='.PA::$page_uid.'&amp;do=change&amp;action=EditRelation'
                                  ),
                        'delete_relationship' => array('caption'=>sprintf(__('Delete as %s'), __($def_relations_term)),
                                  'url'=>$this->base_url.PA_ROUTE_EDIT_RELATIONS.'/do=delete&amp;uid='.PA::$page_uid.'&amp;action=EditRelation',
                                  'extra'=>' onclick ="return delete_confirmation_msg(\''.__('Are you sure you want to delete this Relationship?').'\') "'
                                  ),
/*
                      'send_testimonial' => array('caption'=>__('Write Testimonial'),
                                  'url'=>$this->base_url.'/'.FILE_WRITE_TESTIMONIAL.'?uid='.PA::$page_uid
                                  ),
                       'user_comment' => array ('caption' =>__('Write Comment'),
                                 'url' => $this->base_url.'/'.FILE_WRITE_USER_COMMENT.'?uid='.PA::$page_uid
                                  )
*/
                         );
          $relation_does_not_exists_links = array('send_message' => array('caption'=>__('Send a message'),
                                  'url'=>$this->base_url.PA_ROUTE_ADDMESSAGE.'/uid='.PA::$page_uid
                                  ),
                       'make_connection' => array('caption'=> sprintf(__('Add as %s'), __($def_relations_term)),
                                  'url'=>$this->base_url.PA_ROUTE_EDIT_RELATIONS.'/uid='.PA::$page_uid.'&amp;do=add&amp;action=EditRelation'
                                  ),
/*
                       'send_testimonial' => array('caption'=>__('Write Testimonial'),
                                  'url'=>$this->base_url.'/'.FILE_WRITE_TESTIMONIAL.'?uid='.PA::$page_uid
                                  ),
                       'user_comment' => array ('caption' =>__('Write Comment'),
                              'url' => $this->base_url.'/'.FILE_WRITE_USER_COMMENT.'?uid='.PA::$page_uid
                                 )
*/

                       );
          if (PA::$page_uid==PA::$login_uid) {//login and get uid same means user's public page
            $level_2['highlight'] = 'user';
            $level_3 = $this->get_level_3('user');
          } else {
            // make left and right links
            //user's public page requires different link rendering
            if (!empty($relations_ids)) {
              if (in_array(PA::$page_uid, $relations_ids)) {
                $left_user_public_links = $relation_already_exists_links;
              } else {
                $left_user_public_links = $relation_does_not_exists_links;
              }
            } else {
              $left_user_public_links = $relation_does_not_exists_links;
            }
           }
        }
        else {  //means user's private page
          $level_2['highlight'] = 'user';
          $level_3 = $this->get_level_3('user');
          $level_3['highlight'] = 'user_private';
        }
      break;
      /*----------------------------------------------------*/
      case PAGE_PEOPLES :
        $level_2['highlight'] = 'people';
        $level_3 = $this->get_level_3('people');
        $level_3['highlight'] = 'find_people';
      break;


      /*----------------------------------------------------*/
      case PAGE_FAMILY :
      case PAGE_FAMILY_EDIT :
      case PAGE_FAMILY_MEMBERS :
      case PAGE_FAMILY_MODERATION :
        $level_2['highlight'] = 'people';
        $level_3 = $this->get_level_3('family');
      break;

      /*----------------------------------------------------*/
       case FILE_VIEW_ALL_MEMBERS :
       if (@$_GET['gid']) {
         $level_2['highlight'] = 'groups';
         $level_3 = $this->get_level_3(array('type'=>'groups', 'sub_type'=>'group_specific'));
         $level_3['highlight'] = 'group_members';
       }
       else {
          $level_2['highlight'] = 'people';
          if (PA::$page_uid == PA::$login_uid) {
          $level_3 = $this->get_level_3('people');
          if ((!empty($_GET['view_type'])) && ($_GET['view_type'] == 'relations')) {
            $level_3['highlight'] = 'my_friends';
          }
          else if ((!empty($_GET['view_type'])) && ($_GET['view_type'] == 'in_relations')) {
            $level_3['highlight'] = 'people_who_call_me_friend';
          }
          else {
            $level_3['highlight'] = 'find_people';
          }
        }
      }
      break;
      /*----------------------------------------------------*/
      case FILE_INVITATION :
        $level_2['highlight'] = 'people';
        $level_3 = $this->get_level_3('people');
        $level_3['highlight'] = 'invite';
      break;
      /*----------------------------------------------------*/
      case FILE_UPLOAD_MEDIA :
      case PAGE_MEDIA_GALLERY :
        if (PA::$login_uid) {
          if(isset($_GET['view']) && 'groups_media' == $_GET['view']){ //user is viewing group gallery
            $level_2['highlight'] = 'groups';
            $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
            $level_3['highlight'] = 'group_gallery';
          } else if (isset($_GET['view']) && 'friends' == $_GET['view']) { //user is viewing his friends gallery
            $level_2['highlight'] = 'people';
            $level_3 = $this->get_level_3('people');
            $level_3['highlight'] = 'friends_gallery';
          } else if( (PA::$page_uid != PA::$login_uid) && (PA::$page_uid!='') ) { //user is viewing his private page gallery
            $level_2['highlight'] = 'people';
          } else if ((PA::$page_uid == PA::$login_uid) || (!PA::$page_uid)) {
            $level_2['highlight'] = 'user';
            $level_3 = $this->get_level_3('user');
            $level_3['highlight'] = 'my_gallery';
          }
        }
        else {

          if(!empty(PA::$page_uid)) {// for anonymous user
             $level_2['highlight'] = 'people';
          }
        }
         break;
      /*----------------------------------------------------*/
      case PAGE_USER_CUSTOMIZE_UI :
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'customize_ui';
       break;
      case FILE_USER_CALENDAR:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'my_events';
      break;
      case PAGE_EDIT_PROFILE :
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'settings';
       break;
      /*----------------------------------------------------*/
      case FILE_EDIT_RELATIONS :
        $level_2['highlight'] = 'people';
        $level_3 = $this->get_level_3('people');
        $level_3['highlight'] = 'find_people';
       break;
      /*----------------------------------------------------*/
       case PAGE_GROUPS_HOME :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
        $level_3['highlight'] = 'find_groups';
       break;
       /*----------------------------------------------------*/
       case FILE_GROUPS_CATEGORY :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
        $level_3['highlight'] = 'find_groups';
       break;
       /*----------------------------------------------------*/
       case FILE_ADDGROUP :
        $level_2['highlight'] = 'groups';
        if ( !empty($_GET['gid']) ) {
          $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
          $level_3['highlight'] = 'edit_group';
        } else {
          $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
          $level_3['highlight'] = 'create_group';
        }
       break;
       /*----------------------------------------------------*/
       case PAGE_GROUP :
       case PAGE_GROUP_AD_CENTER :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'group_home';
       break;
       /*----------------------------------------------------*/
       case FILE_FORUM_MESSAGES :
       case FILE_FORUM_HOME :
       case FILE_CREATE_FORUM :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'group_forum';
       break;
       case FILE_GROUP_CALENDAR :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'group_events';
       break;

        /*----------------------------------------------------*/
       case FILE_GROUP_INVITATION :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
        $level_3['highlight'] = 'invite';
       break;
        /*----------------------------------------------------*/
/*
       case FILE_GROUP_MEDIA_GALLERY :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'group_gallery';
       break;
*/
       /*----------------------------------------------------*/
       case FILE_EDIT_FORUM:
       case FILE_FORUM_MESSAGES :
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));

       break;
       /*----------------------------------------------------*/
       case FILE_WIDGET:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'user_widgets';
       break;
       /*----------------------------------------------------*/
       case PAGE_MESSAGE :
       case PAGE_ADDMESSAGE:
       case PAGE_VIEW_MESSAGE:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'messages';

       break;
       /*----------------------------------------------------*/
       case FILE_POST_CONTENT:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'create_post';
       break;
       /*----------------------------------------------------*/
       case FILE_CONTENT_MANAGEMENT:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'manage_posts';
       break;
         /*----------------------------------------------------*/
       case FILE_ADDGROUP:
        $level_2['highlight'] = 'group';
        if ($_GET['gid']) {
          $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
          $level_3['highlight'] = 'edit_group';
        } else {
          $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
          $level_3['highlight'] = 'create_group';
        }
      break;
        /*----------------------------------------------------*/
       case PAGE_GROUP_MODERATION:
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        if ('members'==$_GET['view']) {
          $level_3['highlight'] = 'moderate_users';
        }
        if ('content'==$_GET['view']) {
          $level_3['highlight'] = 'moderate_posts';
        }
        if ('users'==$_GET['view']) {
          $level_3['highlight'] = 'moderate_membership_requests';
        }
      break;
      case FILE_MANAGE_GROUP_CONTENTS:
        $level_1['highlight'] = 'networks_directory';
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'manage_group_content';
      break;
      case PAGE_GROUP_THEME:
        $level_1['highlight'] = 'networks_directory';
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
        $level_3['highlight'] = 'group_customize_ui';
      break;
          /*----------------------------------------------------*/
       case FILE_NETWORKS_HOME:

       case FILE_NETWORKS_CATEGORY:
            $level_1['highlight'] = 'networks_directory';

      break;
      /*----------------------------------------------------*/

      case FILE_NETWORK_USER_DEFAULTS:
      case FILE_RELATIONSHIP_SETTINGS:
      case FILE_EMAIL_NOTIFICATION:
      case FILE_MANAGE_TAKETOUR:
      case FILE_MANAGE_EMBLEM:
      case FILE_CONFIGURE_SPLASH_PAGE:
      case FILE_NETWORK_FEATURE:
      case FILE_NETWORK_CUSTOMIZE_UI_PAGE:
      case FILE_NETWORK_STATS :
      case FILE_NETWORK_LINKS:
      case FILE_NEW_USER_BY_ADMIN:
      case FILE_NETWORK_MANAGE_CONTENT:
      case FILE_MODULE_SELECTOR:
      case FILE_NETWORK_BULLETINS:
      case FILE_NETWORK_MANAGE_USER:
      case FILE_NETWORK_CALENDAR:
      case FILE_MANAGE_AD_CENTER:
      case FILE_MANAGE_GROUP_FORUM:
      case FILE_MANAGE_COMMENTS:
      case FILE_RANKING:
      case FILE_MISREPORTS:
      case PAGE_ROLE_MANAGE:
      case FILE_ASSIGN_TASK:
        $level_2 = $this->get_level_3('network');
//         $level_3 = $this->get_level_3('manage_network');
        $level_1['highlight'] = 'configure_network';
        $level_2['highlight'] = 'configure_network';
 /*       $level_3['highlight'] = 'manage_user'*/;
      break;

      case FILE_CREATENETWORK:
            $level_1['highlight'] = 'create_network';
            $level_3['highlight'] = 'statistics';
      break;
      /*----------------------------------------------------*/
      case FILE_SHOWCONTENT:
      // remark by Zoran Hron: this never will be executed because
      // constants FILE_SEARCH_HOME and FILE_SHOWCONTENT points to the same value !!!
      if(!empty($_GET['gid']))  {
        $level_2['highlight'] = 'groups';
        $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'groups_general'));
      }
      break;
      /*----------------------------------------------------*/
      case FILE_LINKS_MANAGEMENT:
         $level_2['highlight'] = 'user';
      break;
      /*----------------------------------------------------*/



     case FILE_MEDIA_FULL_VIEW:
        if(!empty($_GET['gid'])){
               $level_2['highlight'] = 'groups';
               $level_3 = $this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
            }
            else{
               $level_2['highlight'] = 'user';
               $level_3 = $this->get_level_3('user');
            }

      break;



      case FILE_REGISTER:
        $level_2['highlight'] = 'home';
      break;
      case FILE_EDIT_MEDIA:
        $level_2['highlight'] = 'user';
        $level_3 = $this->get_level_3('user');
        $level_3['highlight'] = 'my_gallery';
      break;
      /*----------------------------------------------------*/
      case FILE_EDITNETWORK:
      case PAGE_PERMALINK:
       if ($is_group_content == TRUE) {
         if ($this->get_uid()) {
          $is_group_content = FALSE;
          $level_2['highlight'] = 'groups';
          $level_3 = $this->get_level_3(
          array('type'=>'groups','sub_type'=>'group_specific'));
          $level_3['highlight'] = 'group_home';
         } else {
          $is_group_content = FALSE;
          $level_2['highlight'] = 'groups';
          $level_3 = $this->get_level_3(
          array('type'=>'groups','sub_type'=>'groups_general'));
          $level_3['highlight'] = 'group_home';
         }
       }
      elseif (!empty($_GET['cid'])) {
        try {
            $content_data = Content::load_content($_GET['cid'], $this->get_uid());
            if ($content_data->parent_collection_id != -1 &&
						$content_data->parent_collection_id != 0) {
						$content_collection_data = ContentCollection::load_collection($content_data->parent_collection_id, $this->get_uid());
						if ($content_collection_data->type == GROUP_COLLECTION_TYPE) {
						$this->get_level_3(array('type'=>'groups','sub_type'=>'group_specific'));
						}
					}
					else{
						$level_3 = $this->get_level_3('user');
							}
				 }
			   catch (PAException $e) {
	       }
			}
      break;
      default:
      break;
     }//--end of switch

		/* moved this code outside of the switch statement...
			we wabt this to run in any case, so that xml files can override default navigation!
			-Martin
		*/
		if(isset($dynamic_page) && !empty($dynamic_page->navigation_code)) {
			if(false == eval($dynamic_page->navigation_code ."return true;")) {
				echo "<b>Evaluation of navigation links code for page ID=$dynamic_page->page_name failed".
				"Please check your dynamic page configuration file. Page ID: $dynamic_page->page_id";
			}
    }
    /* ------- */
    $menu = array('level_1'=>$level_1,'level_2'=>$level_2,'level_3'=>$level_3,'left_user_public_links'=>$left_user_public_links);
//    echo '<pre>'.print_r($menu,1).'</pre>';
    return $menu;
  }
}
?>
