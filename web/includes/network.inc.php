<?php

// NOTE: This function is obsolete now!!!
//
//
//
// function checks the permission
/*
function current_user_can($action) {
  //TODO write call back functions for each action
  if(( 'configure_network' == $action     ||  'delete_network' == $action ||       // permissins used in old pages
       'network_announcement' == $action  ||  'network_bulletins' == $action ||
       'manage_user' == $action           ||  'manage_content' == $action ||
       'new_user_create' == $action       ||  'network_links' == $action ||
       'change_skin' == $action           ||  'configure_splash_page' == $action ||
       'customize_ui' == $action          ||  'moderate_content' == $action ) ||
     ( 'manage_settings' == $action       ||  'meta_networks' == $action ||         // tasks-permissins defined in user roles
       'manage_ads' == $action            ||  'notifications' == $action ||
       'manage_links' == $action          ||  'user_defaults' == $action ||
       'manage_themes' == $action         ||  'manage_events' == $action ||
       'post_to_community' == $action     ||  'super_groups' == $action ))  {
          if ( PA::$network_info ) {
            // fix by Z.Hron - on David's suggestion: User with any administration task should be able to access to Configure Network.
            if (!empty($_SESSION['user']) && ((Network::is_admin(PA::$network_info->network_id, $_SESSION['user']['id']))
                                          || (Roles::check_administration_permissions($_SESSION['user']['id']))
                                          || (Roles::check_permission_by_value((int)$_SESSION['user']['id'], $action)))) {
               return TRUE;
            } else {
               return FALSE;
          }
       }
  } else if( 'configure_system' == $action ) {
     return ($_SESSION['user']['id'] == SUPER_USER_ID) ? true : false;
  }
}
*/
// uploads the network file
function do_file_upload() {
	global $uploaddir;
	$uploadfile = $uploaddir.basename($_FILES['network_image']['name']);
      $myUploadobj = new FileUploader; //creating instance of file.
      $image_type = 'image';
      $file = $myUploadobj->upload_file($uploaddir,'network_image',true,true,$image_type);
      if( $file == false) {
        $r =  array('error'=>TRUE,'error_msg'=>$myUploadobj->error);
      }
      else {
        $r = array('error'=>FALSE,'file'=>$file);
      }
      return $r;
}
function save_network($data){
	return;
}
// this function is used to check errors in form
function check_error($skip=NULL) {
	global $invalid_network_address;
	$mandatory_vars = array('address'=>'Network Address', 'name'=>'Network Title', 'tagline'=>'Network Sub Title', 'category'=>'Network Category','desc'=>'Network Description');
	if ( !empty($skip) ) {
	   if( is_array($skip) ) {
	     foreach( $skip as $key => $value ) {
	       if( array_key_exists($value, $mandatory_vars) ) {
	         unset($mandatory_vars[$value]);
	       }
	     }
	   } elseif (is_string($skip)) {
	   	if( array_key_exists($skip, $mandatory_vars) ) {
	         unset($mandatory_vars[$skip]);
	       }
	   }

	}
	$error  = FALSE;
	$error_msg = 'Network could not be saved due to following errors:<br />';
	if ( $_POST['action'] == 'edit' ) {
		unset($mandatory_vars['address']);
	}
	//check for mandatory vars
	foreach ( $mandatory_vars as $key=>$value ) {
		$_POST[$key] = trim($_POST[$key]);
		if (empty($_POST[$key])) {
			$error = TRUE;
			$error_msg .= $value.' can\'t be empty<br />';
		}
		if ($key=='address') {
			if (!empty($_POST[$key])) {
				if (strlen($_POST[$key])<3) {
					$error = TRUE;
					$error_msg .= $value.' can\'t be less than 3 characters<br />';
				} elseif (strlen($_POST[$key])>20) {
					$error = TRUE;
					$error_msg .= $value.' can\'t be more than 20 characters<br />';
				} elseif ( !Validation::validate_alpha_numeric($_POST[$key]) ) {
					$error = TRUE;
					$error_msg .= $value.' should be alphanumeric and  spaces are not allowed<br />';
				} elseif(Network::check_already ($_POST[$key])) {
					$error = TRUE;
					$error_msg .= $value.' is not available<br />';
				} else if (in_array($_POST[$key],$invalid_network_address)) {
					$error = TRUE;
					$error_msg .= ' Special subdomain names like www,ftp, mail, smtp, pop etc. are not allowed in network address.<br />';

        			}
			}

		}
	}//...end foreach
	if ($error) {
		$r = array('error'=>TRUE,'error_msg'=>$error_msg);
	} else {
		$r = FALSE;
	}
	return $r;
}





///////////////////// following arrays are here for adding default controls to networks
$basic = array('header_image'=>array('name'=>'','option'=>DESKTOP_IMAGE_ACTION_STRETCH));
$notify_owner = array(
                      'some_joins_a_network'=>array(
                                                  'caption'=>'some one joins a network',
                                                  'value'=>NET_NONE
                                                  ),
                      'content_posted'=>array(
                                                  'caption'=>'posts or content is created',
                                                  'value'=>NET_NONE
                                                  ),
                      'group_created'=>array(
                                                  'caption'=>'a group is created on the network',
                                                  'value'=>NET_NONE
                                                  ),
                      'group_settings_updated'=>array(
                                                  'caption'=>'group settings data changed',
                                                  'value'=>NET_NONE
                                                  ),
                      'media_uploaded'=>array(
                                                  'caption'=>'media is uploaded to a user gallery',
                                                  'value'=>NET_NONE
                                                  ),
                      'group_media_uploaded'=>array(
                                                  'caption'=>'media is uploaded to a group gallery',
                                                  'value'=>NET_NONE
                                                  ),
                      'relation_added'=>array(
                                                  'caption'=>'a new relation is established',
                                                  'value'=>NET_NONE
                                                  ),
                      'reciprocated_relation_estab'=>array(
                                                  'caption'=>'a reciprocated relationship establsihed',
                                                  'value'=>NET_NONE
                                                  ),
                      'content_posted_to_comm_blog'=>array(
                                                  'caption'=>'new content is sent to the home page community blog',
                                                  'value'=>NET_NONE
                                                  ),
                      'report_abuse_on_content'=>array(
                                                  'caption'=>'report abuse on contents',
                                                  'value'=>NET_YES
                                                  ),
                      'report_abuse_on_comment'=>array(
                                                  'caption'=>'report abuse on comments',
                                                  'value'=>NET_YES
                                                  ),
                      'content_modified'=>array(
                                                  'caption'=>'content has been modified',
                                                  'value'=>NET_NONE
                                                  ),
                      'new_user_registered'=>array(
                                                  'caption'=>'new user registered on the network',
                                                  'value'=>NET_YES
                                                  )
);
// 3 more notification option are added to the notify_member array by Ekta 28/3/2007
$notify_members = array(
                      'invitation_accept'=>array(
                                 'caption'=>'join network invitations have been accepted.',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'invite_accept_group'=>array(
                                 'caption'=>'join group invitations have been accepted.',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'relationship_created_with_other_member'=>array(
                                 'caption'=>'someone has made them a friend or other kind of relation',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'someone_join_their_group'=>array(
                                 'caption'=>'someone has joined a group they created',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'friend_request_sent'=>array(
                                 'caption'=>'someone has sent them a friend request',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'friend_request_approved'=>array(
                                 'caption'=>'someone has approve your friend request',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                  ),
                      'friend_request_denial'=>array(
                                 'caption'=>'someone has denied to be their friend',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                                    ),
                      'bulletin_sent'=>array(
                                 'caption'=>'network operator has sent a bulletin',
                                 'value'=>NET_NONE,
                                 'user_settable' => true
                                            ),
                      'welcome_message' => array(
                                 'caption' => 'Welcome message',
                                 'value' => NET_EMAIL,
                                 'user_settable' => false
                                            )
);

$user_defaults = array('user_friends'=>'',
		       'desktop_image'=>array('name'=>'',
                                        'option'=>DESKTOP_IMAGE_ACTION_STRETCH	),
		       'default_image_gallery'=>'',
		       'default_audio_gallery'=>'',
		       'default_video_gallery'=>'',
                       'default_links'=>NET_NO,
                       // by default no blog is provided.
                       'default_blog' => NET_NO
                    );

$relationship_options = array('closest_relation'=>array( 'caption' => 'closest relation', 'value' => 'Best Friend' ),
                                              'close_relation'=>array( 'caption' => 'close relation', 'value' => 'Good friend' ),
                                              'relation'=>array( 'caption' => 'relation', 'value' => 'Friend' ),
                                              'distant_relation'=>array( 'caption' => 'distant relation', 'value' => 'Acquaintance' ),
                                              'most_distant_relation'=>array( 'caption' => 'most distant relation', 'value' => 'Haven\'t Met' ),
								);
global $network_controls; // need this when being included by run_scripts.php
$network_controls = array('basic'=>$basic,
                          'notify_owner'=>$notify_owner,
                          'notify_members'=>$notify_members,
                          'msg_waiting_blink'=>NET_NO,
                          'email_validation' => NET_YES,
                          'user_defaults'=>$user_defaults,
                          'relationship_options'=>$relationship_options,
                          'network_group_title'=> 'Community Blog',
                          'network_feature'=>'',
                          'top_navigation_bar' => NET_YES,
                          'network_content_moderation' => NET_NO,
                          'reciprocated_relationship' => NET_YES,
                          'captcha_required'  => NET_NO,
                          'show_people_with_photo'  => NET_NO,
                          'language_bar_enabled'  => NET_YES,
                          'default_language' => 'english'
                          );
/** Array $net_config_navigation is holding the navigation link for
* for configuring a network ie for navigating NOC pages.
*/

// This function will return Previous Next link for Network Operator Control(NOC) pages

function network_config_navigation ($config_navigation) {
  if (PA::$network_info->type == MOTHER_NETWORK_TYPE) {
    $start_navigation_meta_network = 'network_feature.php';
    $end_navigation_meta_network = 'manage_taketour.php';
  } else {
    $start_navigation_meta_network = PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme";
    $end_navigation_meta_network = 'manage_ad_center.php';
  }
  $net_config_navigation = array('stats' => array(
                                          'Previous'=>NULL,                               'Next' => 'manage_ad_center.php'
                                          ),
                              'manage_ad_center' => array(
                                                    'Previous'=> PA::$url . PA_ROUTE_CONFIGURE_NETWORK,
                                                    'Next' => $start_navigation_meta_network
                                                    ),
                              'manage_textpads'=>array(
                                                    'Previous'=>FILE_MANAGE_AD_CENTER,
                                                    'Next'=>FILE_CONFIGURE_EMAIL
                                                    ),
                              'set_featured_network' => array(
                                                        'Previous'=> 'manage_ad_center.php',
                                                        'Next'=> 'manage_emblem.php'
                                                        ),
                              'manage_emblem' => array(
                                                 'Previous'=> 'network_feature.php',
                                                 'Next'=> 'configure_splash_page.php?section=configure'
                                                 ),
                              'configure' => array(
                                       'Previous'=> 'manage_emblem.php',
                                       'Next'=> 'configure_splash_page.php?section=showcased_networks'
                                        ),
                              'showcased_networks' => array(
                                       'Previous'=> 'configure_splash_page.php?section=configure',
                                       'Next'=>'configure_splash_page.php?section=network_of_moment'
                                        ),
                              'network_of_moment' => array(
                                     'Previous'=> 'configure_splash_page.php?section=showcased_networks',
                                     'Next'=> 'configure_splash_page.php?section=video_tours'
                                     ),
                              'video_tours' => array(
                                      'Previous' => 'configure_splash_page.php?section=network_of_moment',
                                      'Next'=> 'configure_splash_page.php?section=register_today'
                                      ),
                              'register_today' => array(
                                            'Previous'=> 'configure_splash_page.php?section=video_tours',
                                            'Next'=> 'manage_taketour.php'
                                            ),
                              'manage_persionalized_video'=>array(
                                  'Previous'=> 'configure_splash_page.php?section=register_today',
                                  'Next'=>'http://www.'.PA::$domain_suffix.'/awstats/awstats.pl?config=www.pa.com'
                                  ),
                              'mis_usage' => array(
                                     'Previous'=> 'manage_taketour.php',
                                     'Next'=> 'misreports.php'
                                     ),
                              'mis_count' => array(
                                      'Previous' => 'http://www.'.PA::$domain_suffix.'/awstats/awstats.pl?config=www.pa.com',
                                      'Next'=> 'misreports.php?mis_type=mkt_rpt'
                                      ),
                              'marketing_report' => array(
                                            'Previous'=> 'misreports.php',
                                            'Next'=> 'ranking.php'
                                            ),
                              'manage_ranking'=>array(
                                  'Previous'=> 'misreports.php?mis_type=mkt_rpt',
                                  'Next'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme"
                                  ),
                              'theme_selector'=>array(
                                                'Previous'=> $end_navigation_meta_network,
                                                'Next'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/module"
                                                ),
                              'module_selector'=>array(
                                                 'Previous'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/theme",
                                                 'Next'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/desktop_image"
                                                 ),
                              'desktop_image'=>array(
                                              'Previous'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/module",
                                              'Next'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/style"
                                              ),
                              'customize_theme'=>array(
                                              'Previous'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/desktop_image",
                                              'Next'=> 'network_user_defaults.php'
                                              ),
                              'user_defaults'=>array(
                                            'Previous'=> PA::$url . PA_ROUTE_CUSTOMIZE_NETWORK_GUI . "/style",
                                            'Next'=>'relationship_settings.php'
                                            ),
                              'relation_settings'=>array(
                                                  'Previous' => 'network_user_defaults.php',
                                                  'Next' => 'new_user_by_admin.php'
                                                  ),
                              'create_user'=>array(
                                            'Previous' => 'relationship_settings.php',
                                            'Next' => 'manage_user.php'
                                            ),
                              'manage_user'=>array(
                                            'Previous'=>'new_user_by_admin.php',
                                            'Next'=>'network_moderate_content.php'
                                            ),
                              'moderate_content'=>array(
                                                'Previous'=>'manage_user.php',
                                                'Next'=>'network_manage_content.php'
                                                ),
                              'manage_content'=>array(
                                                'Previous'=>'network_moderate_content.php',
                                                'Next'=>'manage_groups_forum.php'
                                                ),
                              'manage_forum'=>array(
                                              'Previous'=>'network_manage_content.php',
                                              'Next'=>'manage_comments.php'
                                              ),
                              'manage_comments'=>array(
                                                'Previous'=>'manage_groups_forum.php',
                                                'Next'=>'network_links.php'
                                                ),
                              'manage_link'=>array(
                                            'Previous'=>'manage_comments.php',
                                            'Next'=>'network_calendar.php'
                                            ),
                              'bulletins'=>array(
                                           'Previous'=>'manage_category.php',
                                           'Next'=>'email_notification.php'
                                           ),
                              'email_notification'=>array(
                                                    'Previous'=>'network_bulletins.php',
                                                    'Next'=> PA_ROUTE_CONFIG_ROLES
                                                    ),
                              'manage_roles'=>array(
                                              'Previous'=>'email_notification.php',
                                              'Next'=>'assign_tasks.php'
                                              ),
                              'manage_tasks_relationship'=>array(
                                                          'Previous'=> PA_ROUTE_CONFIG_ROLES,
                                                          'Next'=>NULL
                                                          ),
                               'manage_groups'=>array(
                                                          'Previous'=>'manage_questions.php',
                                                          'Next'=>'manage_category.php'
                                                          ),
                               'manage_category'=>array(
                                                          'Previous'=>'manage_groups.php',
                                                          'Next'=>'network_bulletins.php'
                                                          )
                              );
  if (PA::$network_info->type != MOTHER_NETWORK_TYPE) {
    array_splice($net_config_navigation, 2, -16);
  }
  $links_array = @$net_config_navigation[$config_navigation];
  $config_navigation_url = null;
  if(is_array($links_array)) {
    $config_navigation_url = '<div id="buttonbar" style="float:right;"><br /><ul>';
    if(!empty($links_array['Previous'])) {
      if(strpos($links_array['Previous'], "http://") === false) {
         $config_navigation_url .= '<li><a href="'.PA::$url .'/'.$links_array['Previous'].'">'.__("Previous").'</a></li> ';
      } else {
         $config_navigation_url .= '<li><a href="'.$links_array['Previous'].'">'.__("Previous").'</a></li> ';
      }
    }
    if(!empty($links_array['Next'])) {
      if(strpos($links_array['Next'], "http://") === false) {
         $config_navigation_url .= '<li><a href="'.PA::$url .'/'.$links_array['Next'].'">'.__("Next").'</a></li>';
      } else  {
         $config_navigation_url .= '<li><a href="'.$links_array['Next'].'">'.__("Next").'</a></li>';
      }
    }
    $config_navigation_url .= '</ul></div>';
  }
  return $config_navigation_url;
}

 /** This function sets page default setting for the newly created network
 * it takes network address as parameter
 * and uses global variable $settings_new to populate the page_default_settings table
 * of newly created network
 *
 * NOTE: This function is obsolete now - should be removed!
 *
 */
function default_page_setting($network_address) {
  global $settings_new;
  foreach($settings_new as $page_id => $v1) {
    $page_name = $v1['page_name'];
    $data = $v1['data'];
    $settings_data = serialize($data);
    $sql = 'INSERT into '.$network_address.'_page_default_settings (page_id, page_name, default_settings) values (?, ?, ?)';
    $data = array($page_id, $page_name, $settings_data);
    $res = Dal::query($sql, $data);
  }
}

?>
