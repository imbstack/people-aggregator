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
// global var $path_prefix has been removed - please, use PA::$path static variable
require_once "api/Network/Network.php";
require_once "api/Content/Content.php";
require_once "api/Image/Image.php";
require_once "web/includes/classes/Pagination.php";
require_once "api/Ranking/Ranking.php";

class MISReportModule extends Module {
  
  public $module_type = 'network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_public_center_module.tpl';
  
  public $email_domain_array;
  public $blog_post;
  public $images;
  public $profile_views;
  public $profile_visits_by_user;
  public $relationship_stats;
  public $email_addresses;

  function __construct() {
    parent::__construct();
    $this->title = __('MIS Reports');
    $this->html_block_id = 'MISReportModule';
  }

  function render() {
    $param['network_id'] = PA::$network_info->network_id; 
    $res = Network::get_members($param);
    $links['registered_users'] = count($res['users_data']);
    $this->email_domain_array = $this->get_email_by_domain($res['users_data']);
    $this->blog_post = Content::load_content_id_array (0, NULL, TRUE, 'ALL', 'created', 'DESC');
    $image_array = Image::load_images();
    $this->images = count($image_array);
    $this->profile_views = User::get_profile_view_stats('profile_visitor_count');
    $this->profile_visits_by_user = User::get_profile_view_stats('profile_visited_count');
    $relationship_stats = array();
    $maximum_relation = 0;
    $minimum_relation = 0;
    $average_relation = 0;
    $relationship_stats = Relation::relation_stats();
    if(count($relationship_stats) > 0) {
      $maximum_relation = max($relationship_stats);
      $minimum_relation = min($relationship_stats);
      $average_relation = (array_sum($relationship_stats)) / count($relationship_stats);
    }
    $this->relationship_stats = array('min'=>$minimum_relation,
                                      'max'=>$maximum_relation,
                                      'avg'=>$average_relation);
    $this->inner_HTML = $this->generate_inner_html ($links);
    $content = parent::render();
    return $content;
  }
  
  function generate_inner_html ($links) {    
    if ($this->market_report == TRUE) { // if marketting report is to be viewed
      $params = NULL;
      $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_market_report.tpl';
      $next_prev_navigation = 'marketing_report';
      if ($this->email_sorting == NULL || $this->email_sorting == 'all' ) {        
      $params = array('network_id' => PA::$network_info->network_id,
                     'neglect_owner' => FALSE,
                     'cnt' => TRUE);      
      // setting variables for pagination
      $this->Paging["count"] =  Network::get_members($params);
      unset($params['cnt']);      
      $params['show'] = $this->Paging['show'];
      $params['page'] = $this->Paging['page'];      
      $users = Network::get_members($params);
      $this->email_addresses = $users['users_data'];
      } else {
        if ($this->email_sorting == 'dormant') {
          $params = array("order_by" => 3);// 3 for sort by dormant user
        }        
        $this->Paging["count"] = Ranking::get_top_ranked_users(TRUE, $params);
        $params['show'] = $this->Paging['show'];
        $params['page'] = $this->Paging['page']; 
        $this->email_addresses = Ranking::get_top_ranked_users(FALSE, $params);
      }
      // Set pagination variable
      $Pagination = new Pagination;
      $Pagination->setPaging($this->Paging);    
      $this->page_first = $Pagination->getFirstPage();
      $this->page_last = $Pagination->getLastPage();
      $this->page_links = $Pagination->getPageLinks();
    } else {
      $inner_template = PA::$blockmodule_path .'/'. get_class($this) . '/center_inner_private.tpl';
      $next_prev_navigation = 'mis_count';
    } 
    $obj_inner_template = new Template($inner_template);
    $obj_inner_template->set('links', $links);
    $obj_inner_template->set('email_domain_array', $this->email_domain_array);
    $obj_inner_template->set('blog_post', $this->blog_post);
    $obj_inner_template->set('images', $this->images);
    $obj_inner_template->set('profile_views', $this->profile_views);
    $obj_inner_template->set('profile_visits_by_user', $this->profile_visits_by_user);
    $obj_inner_template->set('relationship_stats', $this->relationship_stats);    
    $obj_inner_template->set('emails', $this->email_addresses);
    $obj_inner_template->set('page_first', $this->page_first);
    $obj_inner_template->set('page_last', $this->page_last);
    $obj_inner_template->set('page_links', $this->page_links);
    $obj_inner_template->set('parameters', Ranking::get_parameters());
    $obj_inner_template->set('config_navigation_url',
                       network_config_navigation($next_prev_navigation));
    $inner_html = $obj_inner_template->fetch();
    return $inner_html;
  }

  function get_email_by_domain($users) {
    $count_users = count($users);
    $email_domain = array();
    for ($i = 0; $i < $count_users; $i++) {
      $email_address = $users[$i]['email'];
      $domain = explode('@', $email_address); 
      if (is_array($domain)) {
        if (array_key_exists($domain[1], $email_domain)) {
          $email_domain[$domain[1]]['count']++;
        } else {
          $email_domain[$domain[1]] = array('caption' => $domain[1],
                                            'count' => 1);
        }
      }
    }
    sortByFunc($email_domain, create_function('$email_domain','return $email_domain["count"];'), 'desc');
    array_splice($email_domain, 10); //only top 10 email domain
    return $email_domain;
  }
}
?>