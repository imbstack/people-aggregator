<?php
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        ajax_default_links.php, ajax file to display links module
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file gets called from links module. It renders html
 *              for the links(inner html)
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 * TODO:      Need to call here link module to generate inner html
 */
  $login_required = TRUE;
  require_once dirname(__FILE__)."/includes/page.php";
  require_once "ext/NetworkLinks/NetworkLinks.php";
  
  if(!empty($_GET['category_id'])) {      
      $condition = array('category_id'=> $_GET['category_id'], 'is_active'=> 1);
      if( Network::is_mother_network(PA::$network_info->network_id) ){
        $uid = SUPER_USER_ID;
      } else {
        $uid = Network::get_network_owner(PA::$network_info->network_id);
      }
      $params_array = array('user_id'=> $uid);
      $Links = new NetworkLinks();      
      $Links->set_params ($params_array);
      $result_array = $Links->network_owner_link($condition);
      $return_string = "";
      if(count($result_array) > 0) {
           $return_string .= "<table width='100%' cellspacing='0' cellpadding='0'>";
          for($counter =0; $counter < count($result_array); $counter++) {
              $return_string .= "
              <tr>
                <td width='6%' rowspan='2'>
                  <input type='checkbox' name='link_id[]' id='link_id_".$result_array[$counter]->link_id."' value='link_id:".$result_array[$counter]->link_id."' />
                </td>
                <td width='94%'>  
                  <input type='text' size=65 value=\"".$result_array[$counter]->title."\"  id='link_id_".$result_array[$counter]->link_id."_title' style='border:0px;'/>
                </td>
               </tr>
               <tr>
                 <td>
                   <input type='text' size=65 value='".$result_array[$counter]->url."' id='link_id_".$result_array[$counter]->link_id."_url' style='border:0px;'/>
                 </td>  
               </tr><br /> ";
           } 
           $return_string .="</table>";
      }  
      else {
            $return_string = "<center>There are no links under this category</center> ";
      }
      print $return_string;
  }
?>