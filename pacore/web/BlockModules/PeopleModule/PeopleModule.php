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
 * Project:     PeopleAggregator: a social network developement platform
 * File:        PeopleModule.php, BlockModule file to generate FaceWall
 * @author:     Tekriti Software (http://www.tekritisoftware.com)
 * Version:     1.1
 * Description: This file contains a class PeopleModule which generates html of
 *              Members list - it is center module
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit
 * http://wiki.peopleaggregator.org/index.php
 *
 */

require_once "web/includes/classes/Pagination.php";
require_once "web/includes/blocks/peoples_helper.php";

class PeopleModule extends Module {

  public $module_type = 'network';
  public $module_placement = 'middle';

   /**
   * $outer_template - name of the outer tpl file that contains header, title etc.
   * it is tpl file that contains predefined outer body structure
   * @var string
   */
  public $outer_template = 'outer_public_group_center_module.tpl';

   /**
   * these are variables used for pagination
   * @var int
   */
  public $Paging, $page_links, $page_prev, $page_next, $page_count,$network_id;

  /**
  * Paramter that decides the sorting direction of the users data
  * Default is created.
  */
  public $sort_by;

  /**
  * user id of the user who is viewing the people page
  * it will be null for anonymous user
  */
  public $viewer_uid;

  /**
  * $show_advance_search_options: variable will be set if user has selected advance search options
  */
  public $show_advance_search_options;

  /**
  * Array having the seach parameters
  */
  public $search_vars = array('allnames', 'first_name', 'last_name');

  /**
  * Array having the advanced search parameters
  */
  public $advance_search_options = array('sex', 'city', 'state', 'company', 'user_tags', 'industry', 'age',
                                         'music', 'movies', 'college', 'passion', 'activities', 'books',
                                         'tv_shows', 'cusines'
                                        );
  /**
  * The default constructor for FacewallModule class.
  */
  function __construct() {
    parent::__construct();
    $this->sort_by = 'created';
    $this->viewer_uid = 0;//for anonymous user.
    $this->show_advance_search_options = false;
  }

  /**
    *  Function : render()
    *  Purpose  : produce html code from tpl file
    *  @return   type string
    *            returns rendered html code
  */
  function render() {
    $user_data = NULL;
    $this->get_links();
    $this->inner_HTML = $this->generate_inner_html();
    $this->config_HTML = '';
    $content = parent::render();
    return $content;
  }

  public function initializeModule($request_method, $request_data) {
    global $paging, $aim_api_key, $aim_presence_key;
    $this->search = $this->search_data = $this->getSearchData($request_data);
    $this->show_people_with_photo = (isset(PA::$extra['show_people_with_photo']) && (PA::$extra['show_people_with_photo'] == true)) ? TRUE : FALSE;

    if($this->show_people_with_photo) {
      if(!isset($request_data['no_photo_ok']) && (@$request_data['no_photo_ok'] != 0)) {
        $this->no_photo_ok = false;
      } else {
        $this->no_photo_ok = @$request_data['no_photo_ok'];
      }
    } else {
      if(!isset($request_data['no_photo_ok']) || (@$request_data['no_photo_ok'] != 0)) {
        $this->no_photo_ok = true;
      } else {
        $this->no_photo_ok = $request_data['no_photo_ok'];
      }
    }
    $this->facewall_path = PA::$theme_path.'/javascript/facewall';
    $this->viewer_uid = (!empty(PA::$login_uid)) ? PA::$login_uid : 0;
    $this->sort_by = (!empty($request_data['sort_by'])) ? $request_data['sort_by'] : NULL;

    //setting the facewall rows, if set
    if (!empty($request_data['rows']) && is_numeric($request_data['rows']) && $request_data['rows'] > 0) {
      $rows = $request_data['rows'];
    } else {
      $rows = FACEWALL_ROW_COUNT;
    }

    //setting the page variables.Paging is working on querystring parameter page, but that has been used in dynamic module generator implementation. Need to change it.
    $this->Paging["page"] = $paging["page"];
    $this->Paging["show"] = ($rows*FACEWALL_COLUMN_COUNT);

    //AOL's AIM api for running Web AIM and Buddy List widgets
    $this->renderer->add_header_html('<script type="text/javascript" src="http://o.aolcdn.com/aim/web-aim/aimapi.js"></script>
      <script type="text/javascript" language="javascript"><!--
        var AIM_PRESENCE_KEY = "'.$aim_presence_key.'";
      // --></script>');

    $this->renderer->add_header_html('<script type="text/javascript" language="javascript"><!--');

    if(isset($aim_api_key))
      $this->renderer->add_header_html('var AIM_API_KEY = "'.$aim_api_key.'";');
    else
      $this->renderer->add_header_html('var AIM_API_KEY = false;');

    $this->renderer->add_header_html('// --></script>');

    $this->renderer->add_header_html('<script type="text/javascript">
      window.addEventListener ? window.addEventListener("load",AIM.widgets.presence.launch,false):
      window.attachEvent("onload",AIM.widgets.presence.launch);
      </script>');

  }

  function handleRequest($request_method, $request_data) {
    $class_name = get_class($this);
    if(!empty($request_data['action']) && !empty($request_data['module']) && ($request_data['module'] == $class_name)) {
      $action = $request_data['action'];
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handleGET_tooltip ($request_data) {
    $extra = unserialize(PA::$network_info->extra);
    $this->rel_term = __('Friend'); // default title
    if(isset($extra['relationship_show_mode']['term'])) {
      $this->rel_term = $extra['relationship_show_mode']['term'];
    }
    $data = unserialize(base64_decode($request_data['data']));
            $templ = PA::$blockmodule_path .'/'. get_class($this) . "/tooltip.tpl";
            $html_gen = new Template($templ);
            $html_gen->set('link', $data);
            $html_gen->set('rel_term', $this->rel_term);
            $html = $html_gen->fetch();
            echo $html;
            exit;
  /*
   echo "<font style='color:white'>This is an IFRAME <br />";
   echo "<pre>" . print_r($data, 1) . "</pre></font>";
   exit;
  */
  }

  /**
  * This method will look for the searchable item in the request data and will sanitize it.
  */
  public function getSearchData($request_data) {
    $search_data_array = array();
    if (!empty($request_data['submit_search'])) {

      $search_vars = array_merge($this->search_vars, $this->advance_search_options);
      $total_search_vars = count($search_vars);
      for ($counter = 0; $counter < $total_search_vars; $counter++) {
        $var = $search_vars[$counter];
        if (!empty($request_data[$var])) {
          if(in_array($var, $this->advance_search_options)) {
            $this->show_advance_search_options = TRUE;
          }
          if ($var == 'age') {
            //check for valid date age range
            $age_range = array();
            $age_range = explode("-", $request_data[$var]);
            $age_range_count = count($age_range);
            switch($age_range_count) {
              case 1:
                //for more than 50 years
                $search_data_array['dob']['value'] = array('lower_limit'=>$age_range[0], 'upper_limit'=>150);
                //giving upper limit as 150 years
                $search_data_array['dob']['type'] = AGE_SEARCH;
              break;
              case 2:
                $search_data_array['dob']['value'] = array('lower_limit'=>$age_range[0], 'upper_limit'=>$age_range[1]);
                $search_data_array['dob']['type'] = AGE_SEARCH;
              break;
              default:
            }
          } else if ($var == 'allnames') {
            $search_data_array[$var]['value'] = $request_data[$var];
            $search_data_array[$var]['type'] = GLOBAL_SEARCH;
          } else {
            $search_data_array[$var]['value'] = $request_data[$var];
            $search_data_array[$var]['type'] = LIKE_SEARCH;
          }
        }
      }
    }
    return $search_data_array;
  }

  function get_links() {
     // Loading the Searching data
    if ($this->sort_by == 'alphabetic')  {
      $this->sort_by = 'login_name';
      $sorting_direction = 'ASC';
    } else {
      $this->sort_by = 'created';
      $sorting_direction = 'DESC';
    }

    $this->only_with_photo = !$this->no_photo_ok;
    $extra_condition = null;
    if(($this->only_with_photo)) {
      $extra_condition = "U.picture IS NOT NULL";
    }

    $users = array();
    $this->users_data = array();

    if (empty($this->search_data)) $this->search_data = @$this->search;

    if ($this->search_data) {
    // load users on the basis of the search parameters.
       if($this->only_with_photo) {
         $users_count = User::user_search($this->search_data, $this->viewer_uid, PA::$network_info->network_id, TRUE, 'ALL', 0, 'U.'.$this->sort_by, 'DESC', $extra_condition);
         $this->Paging["count"] = $users_count['total_users'];
         $users = User::user_search($this->search_data, $this->viewer_uid, PA::$network_info->network_id, FALSE, $this->Paging["show"], $this->Paging["page"], 'U.'.$this->sort_by, $sorting_direction, $extra_condition);
       } else {
         $users_count = User::user_search($this->search_data, $this->viewer_uid, PA::$network_info->network_id, FALSE);
         $this->Paging["count"] = $users_count['total_users'];
         $users = User::user_search($this->search_data, $this->viewer_uid, PA::$network_info->network_id, FALSE, $this->Paging["show"], $this->Paging["page"], 'U.'.$this->sort_by, $sorting_direction);
       }
    } else {
       if($this->only_with_photo) {
         $this->Paging["count"] = Network::get_network_members(PA::$network_info->network_id, array('cnt'=>TRUE, 'extra_condition'=>$extra_condition));
         $params = array('page'=>$this->Paging["page"],'show'=>$this->Paging["show"], 'sort_by'=> $this->sort_by, 'direction'=>$sorting_direction, 'extra_condition'=>$extra_condition);
         $users = Network::get_network_members(PA::$network_info->network_id, $params);
       } else {
         $this->Paging["count"] = Network::get_network_members(PA::$network_info->network_id, array('cnt'=>TRUE));
         $params = array('page'=>$this->Paging["page"],'show'=>$this->Paging["show"], 'sort_by'=> $this->sort_by, 'direction'=>$sorting_direction);
         $users = Network::get_network_members(PA::$network_info->network_id, $params);
       }
    }
    $users_count = count(@$users['users_data']);
    if ($users_count) {
      $user_profiles = $this->get_profile_data($users['users_data']);
      for($cnt = 0; $cnt < $users_count; $cnt++) {
        if (empty($users['users_data'][$cnt]['picture'])) {
          $users['users_data'][$cnt]['picture'] = 'files/default.png';
          $big_img = uihelper_resize_img($users['users_data'][$cnt]['picture'], 120, 120, NULL, "alt=\"".$users['users_data'][$cnt]['login_name'] ."\"");
          $users['users_data'][$cnt]['big_picture'] = $big_img['url'];
        } else {
          $img = uihelper_resize_mk_user_img($users['users_data'][$cnt]['picture'], 80, 80, 'alt="PA"');
          $big_img = uihelper_resize_img($users['users_data'][$cnt]['picture'], 120, 120, NULL, "alt=\"".$users['users_data'][$cnt]['login_name']."\"");
          $users['users_data'][$cnt]['big_picture'] = $big_img['url'];
          preg_match("/src=\"([^']*?)\"/", $img, $match);//preg_match to get the src of the image
          $users['users_data'][$cnt]['picture'] = $match[1];
        }
        $users['users_data'][$cnt] = array_merge($users['users_data'][$cnt], $user_profiles[$cnt]);
      }
      $this->users_data = $users['users_data'];
    }
  }

  function get_profile_data($users_data) {

    $facewall_trunkwords = 7;
    $facewall_maxlength = 8;
    $out_data = array();
    $viewer_uid = 0;
    if (!empty(PA::$login_uid)) {
      $viewer_uid = PA::$login_uid;
    }

    $i = 0;
    foreach ($users_data as $user) {
      $u = new User();
      $u->load((int)$user['user_id']);
      $profile_data = User::load_user_profile($user['user_id'], $viewer_uid);
      $profile_data = sanitize_user_data($profile_data);
      $out_data[$i]['display_name'] = $u->display_name;
      $out_data[$i]['user_url'] = url_for('user_blog', array('login'=>urlencode($user['login_name'])));
      $out_data[$i]['nickname'] = (strlen($user['login_name']) > $facewall_maxlength) ? substr($user['login_name'], 0, $facewall_trunkwords) . ' ...'
                                                                                       : $user['login_name'];
      $out_data[$i]['location'] = field_value(@$profile_data['city'], ' ');

      $age = ' ';
      if (!empty($profile_data['dob'])) {
        $age = convert_birthDate2Age($profile_data['dob']);
      }
      $out_data[$i]['age'] = $age;

      $out_data[$i]['gender'] = field_value(@$profile_data['sex'],' ');

      $out_data[$i]['effect']['event'] = 'baloon.say';
      if(empty($profile_data['sub_caption'])) {
        $profile_data['sub_caption'] = 'Hi.';
      }
      $out_data[$i]['effect']['shoutout'] = $profile_data['sub_caption'];
      $i++;
    }
    return $out_data;
  }


  function generate_inner_html() {
    global $number_user;

    $extra = unserialize(PA::$network_info->extra);
    $this->rel_term = __('Friend'); // default title
    if(isset($extra['relationship_show_mode']['term'])) {
      $this->rel_term = $extra['relationship_show_mode']['term'];
    }



    $msg = @$this->msg;

    $Pagination = new Pagination;
    $Pagination->setPaging($this->Paging);

    $this->page_first = $Pagination->getFirstPage($this->search_data);
    $this->page_last = $Pagination->getLastPage($this->search_data);
    $this->page_links = $Pagination->getPageLinks($this->search_data);

    switch ( $this->mode ) {
     default:
       $tmp_file = PA::$blockmodule_path .'/'. get_class($this) . '/facewall.tpl';
    }

    $inner_html_gen = new Template($tmp_file);
    $inner_html_gen->set('links', $this->users_data);
    $inner_html_gen->set('error_msg', $msg);
    $inner_html_gen->set('page_first', $this->page_first);
    $inner_html_gen->set('page_last', $this->page_last);
    $inner_html_gen->set('facewall_path', @$this->facewall_path);
    $inner_html_gen->set('page_links', $this->page_links);
    $inner_html_gen->set('people_count', $this->Paging["count"]);
    $row_count= ( ceil($this->Paging["count"]/FACEWALL_COLUMN_COUNT) > 10)?10:ceil($this->Paging["count"]/FACEWALL_COLUMN_COUNT) ;
    $number_user=$this->Paging["count"];
    $inner_html_gen->set("row_count", $row_count);
    $inner_html_gen->set('show_advance_search_options', $this->show_advance_search_options);
    $inner_html_gen->set('search_data', $this->search_data);
    $inner_html_gen->set('rel_term', $this->rel_term);
    $inner_html_gen->set('no_photo_ok', $this->no_photo_ok);
    $inner_html_gen->set('show_people_with_photo', $this->show_people_with_photo);

    $inner_html_gen->set('only_with_photo', $this->only_with_photo);


    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
?>