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
  /*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        network_feature.php, web file to set Featured network for PeopleAggregator
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file displays, all the network present over in the PeopleAggregator
                One of the listed network can be set as Featured network by SUPER_USER, 
                ie admin of mother network.
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
  /*This page us used for network settings
  * anonymous user can not view this page;
  */
  $login_required = TRUE;
  //including necessary files
  $use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
  include_once("web/includes/page.php");
  require_once "web/includes/network.inc.php";
  $authorization_required = TRUE;

  $msg = '';
  if( !$_POST ) {
    $extra = unserialize(PA::$network_info->extra);
//     $featured_network = NULL;
    if ( ( int )$extra['network_feature'] != '' ) {
      $network = New Network();
      $network->network_id = ( int )$extra['network_feature'];
      $featured_network = $network->get();
    } 
  }   
  // if a network is posted to be a featured network
  if (!empty($_POST['feature_network_save'])) {
    global $featured_network;
    if ( @$_POST['feature_network'] == 0 ) { // 0 for no selection
      $msg = 'Please select a network';
    } else {
      $network_basic_controls = PA::$network_defaults;
      $network_basic_controls['network_feature'] = $_POST['feature_network'];
      $data = array(
        'extra'=>serialize($network_basic_controls),
        'network_id'=>PA::$network_info->network_id,
        'changed'=>time()
      );
      $network = new Network;
      $network->set_params($data);
      if ( empty($msg) ) {
        try{
          $nid = $network->save();
          $network_object = new Network();
          $network_object->network_id = (int) $_POST['feature_network'];
          $featured_network = $network_object->get();
          $msg = 'Network Information Successfully Updated';
        } catch (PAException $e) {
          $msg = "$e->message";
        } 
      }  
    }  
  }  
  
  
  $page = new PageRenderer("setup_module", PAGE_NETWORK_FEATURE, __("Featured Network"), 'container_two_column.tpl', 'header.tpl', PRI, HOMEPAGE, PA::$network_info);
  
if ( !empty($msg) ) {
  $msg_tpl = & new Template(CURRENT_THEME_FSPATH."/display_message.tpl");
  $msg_tpl->set('message', $msg);
  $m = $msg_tpl->fetch();
  $page->add_module("middle", "top", $m);
}
  $page->html_body_attributes ='class="no_second_tier network_config"';
  $css_array = get_network_css();
  if (is_array($css_array)) {
    foreach ($css_array as $key => $value) {
      $page->add_header_css($value);
    }
  }
  
  $css_data = inline_css_style();
  if (!empty($css_data['newcss']['value'])) {
    $css_data = '<style type="text/css">'.$css_data['newcss']['value'].'</style>';
    $page->add_header_html($css_data);
  }
  echo $page->render();
  
  function setup_module($column, $module, $obj) {
    global $featured_network, $perm;
    $obj->featured_network = $featured_network;
    $obj->perm = $perm;
  }
  
?>
