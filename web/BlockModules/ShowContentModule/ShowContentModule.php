<?php
require_once "ext/Group/Group.php";
require_once "web/includes/classes/Pagination.php";
require_once "api/Tag/Tag.php";
require_once "api/Content/Content.php";
require_once "api/Permissions/PermissionsHandler.class.php";

define('IS_MEMBER',1);
class ShowContentModule extends Module {

  public $module_type = 'user|group|network';
  public $module_placement = 'middle';
  public $outer_template = 'outer_showcontent_module.tpl';

  public $max_height;
  public $tag_id, $search_string_array, $html_block_id_flag, $show_all, $Paging;
  public $page_links, $page_prev, $page_next, $page_count, $message, $type, $group_details;
  public $group;

  function __construct() {
    parent::__construct();
    $this->title = __("Contents");
    $this->html_block_id = "ShowContentModule";
  }




  public function initializeModule($request_method, $request_data)  {
    global $error_msg, $post_type_message, $paging;

    $this->request_data = $request_data;

    $content_type = NULL;
    if(!empty($request_data['post_type'])) {
      $post_type = $request_data['post_type'];
    } else {
      $post_type = 'all';
    }
    $content_type = get_content_type($post_type);

    if (!empty($this->shared_data['ShowContentModule']['show'])) {
    	$this->Paging["show"] = $this->shared_data['ShowContentModule']['show'];
    } else {
    	$this->Paging["show"] = 10;
    }

    if (!empty($this->shared_data['ShowContentModule']['page'])) {
    	$this->Paging["page"] = $this->shared_data['ShowContentModule']['page'];
    } else {
    	$this->Paging["page"] = 1;
    }

    if (!empty($this->shared_data['ShowContentModule']['hide_paging'])) {
    	$this->do_pagination = false;
    } else {
    	$this->do_pagination = true;
    }

    if(!empty($this->shared_data['group_info'])) {
      $this->type = 'group';
      $group = $this->shared_data['group_info'];
      $member_type = $this->shared_data['member_type'];
      if (PA::$login_uid) {
        if ($group->reg_type == REG_INVITE && $member_type == NOT_A_MEMBER) {
          $error_msg = 9005;
          return "skip";
        }
      } else {
        if ($group->reg_type == REG_INVITE) {
          $error_msg = 9005;
          return "skip";
        }
      }

      $this->content_type = $content_type;
      $this->group = $group;
      $this->gid = $request_data['gid'];
      $this->message = $post_type_message[$post_type];
      $this->block_heading = 'Group Blog';
      $this->title = 'Group Blog';
      $this->Paging["page"] = $paging["page"];
      // $this->Paging["show"] = 10;
    }

    if(!empty($this->shared_data['network_info'])) {
      $this->cid = (isset($request_data['cid'])) ? $request_data['cid'] : null;
      $this->mode = PUB;
      $this->block_type = HOMEPAGE;
      $this->content_type = $content_type;
      $this->uid = 0;
      $this->message = $post_type_message[$post_type];
      // This message array is defined in config.inc
      $this->Paging["page"] = $paging["page"];;
      // $this->Paging["show"] = 10;
    }

    if(!empty($this->shared_data['search_data'])) {
      $error_msg = null;
      $this->type = "search";
      $this->search_string_array = $this->buildSearchStringArray($this->shared_data['search_data'], $request_data);
      if($error_msg) {
         return "skip";
      }
      $this->show_filters = TRUE;
/*
      $this->Paging["page"] = $paging["page"];
      $this->Paging["show"] = 5;
*/
    }

    if(!empty($this->shared_data['user_info'])) {
      $user = $this->shared_data['user_info'];
      if(empty(PA::$config->simple['use_simpleblog'])) {
	      $data_profile = $this->shared_data['user_profile'];
        if (count($data_profile) == 0) {
          return 'skip';
        }
        if($data_profile[0]->field_value == BLOG_SETTING_STATUS_NODISPLAY || ($data_profile[0]->field_value == EXTERNAL_BLOG_SETTING_STATUS))  {
            return 'skip';
        }
      }

      $this->cid = (!empty($request_data['cid'])) ? $request_data['cid'] : null;
      $this->mode = ($this->page_id == PAGE_USER_PRIVATE) ? PRI : PUB;
      $this->content_type = $content_type;
      $this->block_type = HOMEPAGE;
      $this->type = 'user';
      $this->uid = $user->user_id;
      $this->orientation = CENTER;
      $this->message = $post_type_message[$post_type];
      $this->Paging['page'] = $paging['page'];
      // $this->Paging["show"] = 10;
      $this->title = $user->first_name . '\'s Blog';
      $this->block_heading = $user->first_name . '\'s Blog';
    }
  }

  private function buildSearchStringArray($search_data, $request_data) {
    global $error_msg;
    $search_string_array = null;
    $is_date_range_search = (
                   !empty($search_data["yFrom"]) || !empty($search_data["yTo"]) ||
                   !empty($search_data["mFrom"]) || !empty($search_data["mTo"]) ||
                   !empty($search_data["dFrom"]) || !empty($search_data["dTo"])
    );
    // Search with Date Range
    if($is_date_range_search) {
      if( empty($search_data["yTo"])) {
        $error_msg =  __('Date range selected is invalid. Please, select a valid date range.');
        return $search_string_array;
      }
      $from = mktime(0, 0, 0, $search_data["mFrom"], $search_data["dFrom"], $search_data["yFrom"]);
      $to = mktime(0, 0, 0, $search_data["mTo"], $search_data["dTo"], $search_data["yTo"]);
      if($to > $from) {
        $search_string_array["date"]["from"] = $from;
        $search_string_array["date"]["to"] = $to;
      }
      else if( $to == $from ){
        // Get the content for a single day. Adding 86400 = no. of seconds in a day.
        $search_string_array["date"]["from"] = $from;
        $search_string_array["date"]["to"] = $from + 86400;
      }
      else {
        $error_msg =  __('Date range selected is invalid. Please, select a valid date range.');
        return $search_string_array;
      }
    }

    if(!empty($search_data["allwords"])) {
      $allwords_array = explode(" ", trim($search_data["allwords"]));
      if(count($allwords_array) > 0) {
        for($counter = 0; $counter < count($allwords_array); $counter++) {
            $search_string_array["allwords"][$counter] = trim($allwords_array[$counter]);
        }
      }
    }

    // For exact phrase
    if(!empty($search_data["phrase"])) {
      $search_string_array["phrase"][0] = trim($search_data["phrase"]);
    }

    // For Any words
    if(!empty($search_data["anywords"])) {
      $anywords_array = explode(" ", trim($search_data["anywords"]));
      if(count($anywords_array) > 0) {
        for($counter = 0; $counter < count($anywords_array); $counter++) {
            $search_string_array["anywords"][$counter] = trim($anywords_array[$counter]);
        }
      }
    }

    // For None of the words
    if(!empty($search_data["notwords"])) {
      $notwords_array = explode(" ", trim($search_data["notwords"]));
      if(count($notwords_array) > 0) {
        for($counter = 0; $counter < count($notwords_array); $counter++) {
            $search_string_array["notwords"][$counter] = trim($notwords_array[$counter]);
        }
      }
    }

    if(!isset($search_string_array) && $is_date_range_search) {
      $error_msg = __("Please enter either data or date to search");
    }

    return $search_string_array;
  }

  function render() {
    if ($this->type == 'group') {
      $this->outer_template = 'outer_show_content_group_module.tpl';

      if ($this->content_type==NULL) {
        $this->content_type = 'all';
      }
      //$type = 'all',$cnt=FALSE, $show='ALL', $page=0, $sort_by='created', $direction='DESC'

      if (!empty($this->content_type)) {
        $this->Paging["count"] = $this->links =  $this->group->get_contents_for_collection($this->content_type,TRUE, 10, 1, 'created', 'DESC',TRUE);

        $this->contents =  $this->group->get_contents_for_collection($this->content_type,FALSE, $this->Paging["show"], $this->Paging["page"], 'created', 'DESC',TRUE);
      } else {
        $this->Paging["count"] = $this->links =  $this->group->get_contents_for_collection($type = 'all',$cnt=TRUE,'all' , 0, $sort_by='created', $direction='DESC');
        $this->contents = $this->group->get_contents_for_collection($type = 'all', $cnt=FALSE, $this->Paging["show"], $this->Paging["page"],'created','DESC');
      }

      $this->group_owner = FALSE;
      $this->group_member = FALSE;
      $this->group_moderator = FALSE;
      $this->ad_manager = FALSE;
      if (PA::$login_uid) {
      	// all permission tests only make sense if we HAVE a user
				$perm_params = array('permissions' => 'manage_groups, manage_roles');
				$has_adm_permission  = PermissionsHandler::can_group_user(PA::$login_uid, $this->group->collection_id, $perm_params);
				if ($has_adm_permission || Group::is_admin($this->group->collection_id, PA::$login_uid)) {
					$this->group_owner = TRUE;
				}
				$this->group_member = FALSE;
				if (Group::member_exists($this->group->collection_id, PA::$login_uid)) {
					$this->group_member = TRUE;
				}

				$perm_params = array('permissions' => 'manage_groups');
				$has_mod_permission  = PermissionsHandler::can_group_user(PA::$login_uid, $this->group->collection_id, $perm_params);
				$this->group_moderator = FALSE;
				if($has_mod_permission || ($this->shared_data['member_type'] == 'moderator')) {
					$this->group_moderator = TRUE;
				}

				$this->ad_manager = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => 'manage_ads'));
				// check for manageads of group permissions
				if (!$this->ad_manager) {
					// we do this checl only if the user is not already permitted to manage ads
					$this->ad_manager = PermissionsHandler::can_group_user(PA::$login_uid, $this->group->collection_id, array('permissions' => 'manage_ads'));
				}
      }

      $this->title = chop_string(sprintf(__("%s's Group Blog"), $this->group->title,32));

    }  else if($this->type == "tag") {
         $this->Paging["count"] = Tag::get_associated_content_ids((int)$this->tag_id, $cnt=TRUE);
         $this->contents = Tag::get_associated_content_ids((int)$this->tag_id, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
         if(!empty($this->contents)) {
          foreach($this->contents as $key=>$value) {
           $this->contents[$key]['content_id'] = $value['id'];
          }
        }
    }  else if($this->type == "search") {
        $this->Paging["count"] = Content::content_search($this->search_string_array, $cnt=TRUE);
        $this->contents = Content::content_search($this->search_string_array, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
    }  else {
      $this->Paging["count"] = Content::load_content_id_array($this->uid, $this->content_type, $cnt=TRUE);
      $contents = Content::load_content_id_array($this->uid, $this->content_type, $cnt=FALSE, $this->Paging["show"], $this->Paging["page"]);
      $this->contents = $contents;
    }

    $this->orientation = LEFT;
    if ($this->type == 'user') {
      $this->block_type = 'ShowContentUserBlock';
      // $this->do_pagination=TRUE;
    }
    else if ($this->type == 'group') {
        if($this->html_block_id_flag == 1) {
           $this->block_type = 'ShowAllContent';
           // $this->do_pagination=TRUE;
        } else {
           $this->block_type = 'ShowContentGroupBlock';
        }
    }  else if($this->type == "tag") {
        $this->block_type = 'ShowAllContent';
        // $this->do_pagination=TRUE;
    }  else if($this->type == "search") {
        $this->block_type = 'ShowAllContent';
        // $this->do_pagination=TRUE;
    } else if ($this->show_all==1) {
        $this->block_type = 'ShowAllContent';
        // $this->do_pagination=TRUE;
    }

      else {
      $this->block_type = 'ShowContentBlock';
      // $this->do_pagination=TRUE;
    }


    $this->inner_HTML = $this->generate_inner_html($this->contents, $this->type);
    if ($this->do_pagination) {
			$Pagination = new Pagination;
			$Pagination->setPaging($this->Paging);
			$this->page_first = $Pagination->getFirstPage();
			$this->page_last = $Pagination->getLastPage();
			$this->page_links = $Pagination->getPageLinks();
    }

    $content = parent::render();
    return $content;
  }

  function generate_inner_html($contents) {
    $request_data = $this->request_data;

    $inner_html = '';
     if (($this->mode == PRI) && ($this->type == 'user')) {
       $inner_html .= '<div id="buttonbar">
        <ul>
          <li><a href="'.PA::$url .'/post_content.php">
            '.__("Create post").'</a>
          </li>
          <li><a href="'.PA::$url .'/content_management.php">
            '.__("Manage posts").'</a>
          </li>
          <li><a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . PA::$login_uid . '">
            '.__("View my public page").'</a>
          </li>
        </ul>
      </div>';
    } else if($this->type == 'user') {
       if(PA::$page_uid == PA::$login_uid) {
        $inner_html .= '<div id="buttonbar">
          <ul>
            <li><a href="'.PA::$url . PA_ROUTE_USER_PRIVATE . '">
              '.__("Return to private page").'</a>
            </li>
          </ul>
        </div>';
      }
    }
    if ($this->type == 'tag') {
      $tag_name = Tag::get_tag_name($this->tag_id);
      $inner_html .= "<h1>".sprintf(__("Showing results for tag %s."), $tag_name)."</h1>";
    }
    
    if ($this->type == 'group') {
        $inner_html .= '<div id="buttonbar">
        <ul>';
        if ($this->group_member) {
          $inner_html .= '<li><a href="'.PA::$url . '/post_content.php?ccid='.$request_data['gid'].'">'.__("Create post").'</a></li>';
          $inner_html .= '<li><a href="'.PA::$url . PA_ROUTE_GROUP_INVITE . '/gid='.$request_data['gid'].'">'.__("Invite").'</a></li>';
        }
        if ($this->group_owner) {
          $inner_html .= '<li><a href="'.PA::$url .'/addgroup.php?gid='.$request_data['gid'].'">'.__("Group Settings").'</a></li>';
          $inner_html .= '<li><a href="'.PA::$url . PA_ROUTE_GROUP_MODERATION . '/view=members&amp;gid='.$request_data['gid'].'">'.__("Moderate").'</a></li>';
          $inner_html .= '<li><a href="'.PA::$url .'/manage_group_content.php?gid='.$request_data['gid'].'">'.__("Manage content").'</a></li>';
        } else if($this->group_moderator) {
          $inner_html .= '<li><a href="'.PA::$url . PA_ROUTE_GROUP_MODERATION . '/view=members&amp;gid='.$request_data['gid'].'">'.__("Moderate").'</a></li>';
        }
        if (!empty($this->ad_manager)) {
        	$inner_html .= '<li><a href="'.PA::$url.PA_ROUTE_GROUP_AD_CENTER .'?gid='.$request_data['gid'].'">'.__("Manage Ads").'</a></li>';
        }
        if ((empty($this->group_member) && empty($this->group_owner)) && !empty(PA::$login_uid)) {
          if (!empty($this->join_this_group_string)) {
              $inner_html .= '<li><a href="'. PA::$url . PA_ROUTE_GROUP . '/action=join&amp;gid='.$request_data['gid'].'">'. $this->join_this_group_string.'</a></li>';
          } else {
          $inner_html .= '<li><a href="'. PA::$url . PA_ROUTE_GROUP . '/action=join&amp;gid='.$request_data['gid'].'">'.__("Join this group").'</a></li>';
        }
       }
       $inner_html.='</ul></div>';
     }
    if ($contents) {
// echo "<pre>".print_r($contents, 1)."</pre>";
      //foreach ($contents as $content) {
      for ($i = 0; $i < count($contents); $i++) {
        if ($i == 0) {
          $inner_html .= uihelper_generate_center_content($contents[$i]['content_id'], 0, 1);
        }
        else {
          $inner_html .= uihelper_generate_center_content($contents[$i]['content_id']);
        }
      }
    }
    else {
        if($this->type == "search") {
          $inner_html .= "<div class=\"center\" style=\"text-align:center; padding: 16px\"><b>".
                          __("No items match your search criteria.").
                          "</b><br /><br /><input type=\"button\" value=\"".__('Back')."\" onclick=\"history.back()\"/>".
                          "</div>";
        } else {
              $inner_html .= '<div class="auto">'.$this->message['message'];
              if($this->mode == 'private' || isset($this->group))   {
                if(!empty($this->message['queryString'])) {
                  $link = PA::$url .'/post_content.php?'.$this->message['queryString'];
                  if(isset($this->group->collection_id) && $this->group->collection_id > 0) {
                    $link = PA::$url .'/post_content.php?'.$this->message['queryString'].'&ccid='.$this->group->collection_id;
                  }
                } else {
                  $link = PA::$url .'/post_content.php';
                  if(isset($this->group->collection_id) && $this->group->collection_id > 0) {
                    $link = PA::$url .'/post_content.php?ccid='.$this->group->collection_id;
                  }
                }
                    $inner_html .= "  ".sprintf(__('Click <a href="%s">here</a> to add content.'), $link);
              }

              $inner_html .= "</div>";
         }
    }

    return $inner_html;
  }

  function get_caption ($caption, $lenght) {
    //FIXME: use the proper html truncation code
    if(strlen($caption) > $lenght) {
      $start =  substr ($caption, 0, ($lenght - 5));
      $caption = $start.'....';
    }
    return $caption;
  }

}

?>
