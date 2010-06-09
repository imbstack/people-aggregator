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
require_once "web/includes/classes/UrlHelper.class.php";
require_once "web/includes/image_resize.php";
require_once "api/Logger/Logger.php";
require_once "api/User/UserDisplayName.class.php";
/**
* @short class MessageRenderer
* @access public
*/
class MessageRenderer {

  const group_owner      = 'notify_group_owner';
  const group_members    = 'notify_group_members';
  const group_bulletin   = 'group_bulletin';
  const network_owner    = 'notify_network_owner';
  const network_members  = 'notify_network_members';
  const network_bulletin = 'network_bulletin';
  const outgoing_email   = 'outgoing_email';
  const other_types      = 'other';

  /**
  * @short requester object
  * @access public
  * @var $requester_obj
  */
  protected $requester_obj;

  /**
  * @short recipient object
  * @access public
  * @var $recipient_obj
  */
  protected $recipient_obj;

  /**
  * @short associated object
  * @access public
  * @var $associated_obj
  */
  protected $associated_obj;

  /**
  * @short message template data
  * @access public
  * @var $message
  */
  public $message;

  /**
  * @short message template variables
  * @access public
  * @var $template_vars
  */
  public $template_vars;

  private $group;
  private $network;

   public function __construct($message, $recipient_obj, $requester_obj, $assoc_obj) {
      $this->message = $message;
      $this->requester = $requester_obj;
      $this->recipient = $recipient_obj;
      $this->associated_obj = $assoc_obj;
      $this->getTemplateVars();
      $this->renderMessage();
   }

   public function renderMessage() {
      if(is_array($this->template_vars) && (count($this->template_vars) > 0)) {
        foreach ($this->template_vars as $key => $value) {
          $this->message['subject'] = str_replace($key, $value, $this->message['subject']);
          $this->message['message'] = str_replace($key, $value, $this->message['message']);
        }
      }
   }

   public function getTemplateVars() {
      $this->template_vars = array();
      switch($this->message['category']) {
          // for group owner notifications recipient is group owner, and Recipient object should be of the 'Group' type
          case self::group_owner:
              if(is_object($this->recipient) && is_a($this->recipient, 'Group')) {
                  $this->group = $this->recipient;
                  $group_owner = $this->getGroupOwner();
                  $this->addUserData($group_owner, 'recipient');
                  $this->addUserData($this->requester, 'requester');
              } else {
                  Logger::log("Error exit: function MessageRenderer::getTemplateVars() - The Recipient Object must be object of 'Group' type");
                  throw new Exception("MessageRenderer::getTemplateVars() - The Recipient Object must be object of 'Group' type");
              }
          break;
          // for group members notifications requester is group owner, and Requester object should be of the 'Group' type
          case self::group_members:
          case self::group_bulletin:
              if(is_object($this->requester) && is_a($this->requester, 'Group')) {
                  $this->group = $this->requester;
                  $group_owner = $this->getGroupOwner();
                  $this->addUserData($group_owner, 'requester');
                  $this->addUserData($this->recipient, 'recipient');
              } else {
                  Logger::log("Error exit: function MessageRenderer::getTemplateVars() - The Requester Object must be object of 'Group' type");
                  throw new Exception("MessageRenderer::getTemplateVars() - The Requester Object must be object of 'Group' type");
              }
          break;
          // for network owner notifications recipient is network owner, and Recipient object should be of the 'Network' type
          case self::network_owner:
              if(is_object($this->recipient) && is_a($this->recipient, 'Network')) {
                  $this->network = $this->recipient;
                  $network_owner = $this->getNetworkOwner();
                  $this->addUserData($network_owner, 'recipient');
                  $this->addUserData($this->requester, 'requester');
              } else {
                  Logger::log("Error exit: function MessageRenderer::getTemplateVars() - The Recipient Object must be object of 'Network' type");
                  throw new Exception("MessageRenderer::getTemplateVars() - The Recipient Object must be object of 'Network' type");
              }
          break;
          // for network members notifications requster is network owner, and Requester object should be of the 'Network' type
          case self::network_members:
          case self::network_bulletin:
          case self::outgoing_email:
              if(is_object($this->requester) && (is_a($this->requester, 'Network') || is_a($this->requester, 'Group') || is_a($this->requester, 'User'))) {
                  if(is_a($this->requester, 'Network')) {
                    $this->network = $this->requester;
                    $requester = $this->getNetworkOwner();
                  }
                  else if(is_a($this->requester, 'Group')) {
                    $this->group = $this->requester;
                    $requester = $this->getGroupOwner();
                  }
                  else if(is_a($this->requester, 'User')) {
                    $requester = $this->requester;
                  }
                  $this->addUserData($requester, 'requester');
                  $this->addUserData($this->recipient, 'recipient');
              } else if(is_string($this->requester)) {
                  $this->addUserData($this->requester, 'requester');
                  $this->addUserData($this->recipient, 'recipient');
              } else {
                  Logger::log("Error exit: function MessageRenderer::getTemplateVars() - The Requester Object must be email address string or object of 'Network', 'Group' or 'User' type");
                  throw new Exception("MessageRenderer::getTemplateVars() - The Requester Object must be email address string or object of 'Network', 'Group' or 'User' type");
              }
          break;
          // for other notification types, requster and recipient are object of the 'User' type
          case self::other_types:
          default:
              if(is_a($this->requester, 'User') && is_a($this->recipient, 'User')) {
                $this->addUserData($this->requester, 'requester');
                $this->addUserData($this->recipient, 'recipient');
              } else {
                  Logger::log("Error exit: function MessageRenderer::getTemplateVars() - The Requester and Recipient objects must be objects of 'User' type");
                  throw new Exception("MessageRenderer::getTemplateVars() - The Requester and Recipient objects must be objects of 'User' type");
              }
      }
      if(empty($this->network)) {
        $this->network = PA::$network_info;
      }
      $this->getDefaultData();
      $this->getMessageSpecificData();
   }

   private function getDefaultData() {
      if(!empty($this->network)) {
        $this->addNetworkData($this->network);
      }
      if(!empty($this->group)) {
        $this->addGroupData($this->group);
      }
      $this->template_vars['%config_site_name%'] = PA::$site_name;
   }

   private function addNetworkData($network) {
        $network_owner = new User();
        $network_owner->load((int)$network->owner_id);
        $network_owner_info = $this->getUserProfile($network_owner, 'network.owner');
        $this->template_vars = array_merge($this->template_vars, $network_owner_info);
        $this->template_vars["%network.icon_image%"]   = uihelper_resize_mk_img($network->inner_logo_image, 219, 35, DEFAULT_NETWORK_ICON,  'alt="'.$network->name.'"');
        $this->template_vars['%network.name%']         = $network->name;
        $this->template_vars['%network.description%']  = $network->description;
        $this->template_vars['%network.member_count%'] = $network->member_count;
        $this->template_vars['%network.join_url%']     = UrlHelper::url_for(PA::$url.'/network_action.php', array('action' => 'join', 'nid' => $network->network_id));
        $this->template_vars['%network.join_link%']    = UrlHelper::link_to(PA::$url.'/network_action.php', $this->template_vars['%network.join_url%'], null, array('action' => 'join', 'nid' => $network->network_id));
        $this->template_vars['%network.url%']  = UrlHelper::url_for(PA_ROUTE_HOME_PAGE);
        $this->template_vars['%network.link%'] = UrlHelper::link_to(PA_ROUTE_HOME_PAGE, $network->name);
        $this->template_vars['%network.member_moderation_url%']  = UrlHelper::url_for(PA::$url.'/'.FILE_NETWORK_MANAGE_USER);
        $this->template_vars['%network.member_moderation_link%'] = UrlHelper::link_to(PA::$url.'/'.FILE_NETWORK_MANAGE_USER, $this->template_vars['%network.member_moderation_url%']);
        $this->template_vars['%network.reci_relation_count%']   = Relation::get(array('cnt' => true), 'status =\''. APPROVED.'\' AND network_uid=' . $network->network_id);
   }

   private function addGroupData($group) {
        $owner_id = Group::get_owner_id((int)$group->group_id);
        $group_owner = new User();
        $group_owner->load((int)$owner_id);
        $group_owner_info = $this->getUserProfile($group_owner, 'group.owner');        // get group owner profile info
        $this->template_vars = array_merge($this->template_vars, $group_owner_info);
        $this->template_vars["%group.icon_image%"]   = uihelper_resize_mk_img($group->picture, 219, 35, DEFAULT_NETWORK_ICON,  'alt="'.$group->title.'"');
        $this->template_vars["%group.name%"]         = $group->title;
        $this->template_vars["%group.description%"]  = $group->description;
        $this->template_vars['%group.member_count%'] = Group::get_member_count($group->group_id);
        $this->template_vars['%group.join_url%']     = UrlHelper::url_for(PA_ROUTE_GROUP, array('action' => 'join', 'gid' => $group->collection_id));
        $this->template_vars['%group.join_link%']    = UrlHelper::link_to(PA_ROUTE_GROUP, $this->template_vars['%group.join_url%'], null, array('action' => 'join', 'gid' => $group->collection_id));
        $this->template_vars["%group.url%"]          = UrlHelper::url_for(PA_ROUTE_GROUP, array('gid' => $group->collection_id));
        $this->template_vars["%group.link%"]         = UrlHelper::link_to(PA_ROUTE_GROUP, $group->title, null, array('gid' => $group->collection_id));
        $this->template_vars['%group.moderation_url%']  = UrlHelper::url_for(PA_ROUTE_GROUP_MODERATION, array('view' => 'users', 'gid' => $group->collection_id));
        $this->template_vars['%group.moderation_link%'] = UrlHelper::link_to(PA_ROUTE_GROUP_MODERATION, __("Group moderation"), null, array('view' => 'users', 'gid' => $group->collection_id));
   }

   private function addUserData($user, $prefix) {
      if(!empty($user)) {
        if(is_object($user)) {
          $user_info = $this->getUserProfile($user, $prefix);
          $this->template_vars = array_merge($this->template_vars, $user_info);
        }
        else if(is_string($user)) {    // email address given instead User object
          $this->template_vars["%$prefix.email_address%"]  = $user;
        }
      }
   }

   private function getGroupOwner() {
      $owner_id = Group::get_owner_id((int)$this->group->group_id);
      $group_owner = new User();
      $group_owner->load((int)$owner_id);
      return $group_owner;
   }

   private function getNetworkOwner() {
      $network_owner = new User();
      $network_owner->load((int)$this->network->owner_id);
      return $network_owner;
   }

   private function getUserProfile($user, $prefix = null) {
      if(!isset($user->display_name)) {
        $u_dname = new UserDisplayName($user);
        $user->display_name = $u_dname->get();
      }
      $user_info = array();
      $user_info["%$prefix.user_id%"]        = $user->user_id;
      $user_info["%$prefix.first_name%"]     = $user->first_name;
      $user_info["%$prefix.last_name%"]      = $user->last_name;
      $user_info["%$prefix.login_name%"]     = $user->login_name;
      $user_info["%$prefix.display_name%"]   = $user->display_name;
      $user_info["%$prefix.profile_url%"]    = UrlHelper::url_for(PA_ROUTE_USER_PUBLIC, array($user->user_id));
      $user_info["%$prefix.profile_link%"]   = UrlHelper::link_to(PA_ROUTE_USER_PUBLIC, $user->display_name, null, array($user->user_id));
      $user_info["%$prefix.image%"]          = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->display_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
      $user_info["%$prefix.email_address%"]  = $user->email;
      $user_info["%$prefix.messages_link%"]  = UrlHelper::link_to(PA_ROUTE_MYMESSAGE, __("My messages"));
      return $user_info;
   }

   public function getMessageSpecificData() {
      if(is_array($this->associated_obj)) {    // to keep compatibility with some old code, we must accept parameters given in arrays also
          foreach($this->associated_obj as $name => $value) {
             $this->template_vars["%$name%"] = $value;
          }
      }
      else if(is_object($this->associated_obj)) {
        if(is_a($this->associated_obj, 'User')) {
          $this->addUserData($this->associated_obj, 'related.user');
        }
        else if(is_a($this->associated_obj, 'Content')) {
          $author = new User();
          $author->load((int)$this->associated_obj->author_id);
          $this->addUserData($author, 'content.author');
          $this->template_vars["%content.title%"] = $this->associated_obj->title;
          $this->template_vars["%content.url%"]  = UrlHelper::url_for(PA_ROUTE_CONTENT, array('cid' => $this->associated_obj->content_id));
          $this->template_vars["%content.link%"] = UrlHelper::link_to(PA_ROUTE_CONTENT, $this->associated_obj->title, null, array('cid' => $this->associated_obj->content_id));
          $this->template_vars["%content.delete_url%"] = UrlHelper::url_for(PA_ROUTE_CONTENT, array('cid' => $this->associated_obj->content_id, 'action' => 'deleteContent'));
          $this->template_vars["%content.delete_link%"] = UrlHelper::link_to(PA_ROUTE_CONTENT, $this->template_vars["%content.delete_url%"], null, array('cid' => $this->associated_obj->content_id, 'action' => 'deleteContent'));
          $this->template_vars["%content.moderation_url%"] = UrlHelper::url_for(PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT);
          $this->template_vars["%content.moderation_link%"] = UrlHelper::link_to(PA::$url.'/'.FILE_NETWORK_MANAGE_CONTENT, $this->template_vars["%content.moderation_url%"]);
          if((get_class($this->associated_obj) == 'Image') || (get_class($this->associated_obj) == 'Audio') || (get_class($this->associated_obj) == 'Video')){
            if(!empty($this->associated_obj->group_id)) {     // if Group Media
               $this->group = new Group();
               $this->group->load((int)$this->associated_obj->group_id);
               $this->addGroupData($this->group);
            }
            $this->template_vars["%media.title%"] = $this->associated_obj->title;
            $this->template_vars["%media.full_view_url%"] = UrlHelper::url_for(PA::$url.'/'.FILE_MEDIA_FULL_VIEW, array('cid' => $this->associated_obj->content_id, 'login_required' => 'true'));
            $this->template_vars["%media.full_view_link%"] = UrlHelper::link_to(PA::$url.'/'.FILE_MEDIA_FULL_VIEW, $this->template_vars["%media.full_view_url%"], null, array('cid' => $this->associated_obj->content_id, 'login_required' => 'true'));
          }
        }
        else if(is_a($this->associated_obj, 'Comment')) {
          $author = new User();
          $author->load((int)$this->associated_obj->user_id);
          $this->addUserData($author, 'comment.author');
          $this->template_vars["%comment.text%"] = $this->associated_obj->comment;
          $this->template_vars["%comment.url%"]  = UrlHelper::url_for(PA_ROUTE_CONTENT, array('cid' => $this->associated_obj->content_id));
          $this->template_vars["%comment.link%"]  = UrlHelper::link_to(PA_ROUTE_CONTENT, $this->template_vars["%comment.url%"], null, array('cid' => $this->associated_obj->content_id));
          $this->template_vars["%comment.delete_url%"]  = UrlHelper::url_for(PA::$url .'/deletecomment.php', array('comment_id' => $this->associated_obj->comment_id));
          $this->template_vars["%comment.delete_link%"]  = UrlHelper::link_to(PA::$url .'/deletecomment.php', $this->template_vars["%comment.delete_url%"], null, array('comment_id' => $this->associated_obj->comment_id));
        }
        else if(is_a($this->associated_obj, 'Invitation')) {
          $inviter = new User();
          $inviter->load((int)$this->associated_obj->user_id);
          $this->addUserData($inviter, 'invite.inviter');
          if(!empty($this->associated_obj->inv_collection_id) && ($this->associated_obj->inv_collection_id != -1)) {
            $group = new Group();
            $group->load((int)$this->associated_obj->inv_collection_id);
            $this->group = $group;
            $this->addGroupData($this->group);
          }
          if(empty($this->associated_obj->inv_user_id) || ($this->associated_obj->inv_summary == "internal_invitation")) {       // invitation sent

            $reg_link_desc = abbreviate_text($this->associated_obj->register_url, 52, 36);
            $acc_link_desc = abbreviate_text($this->associated_obj->accept_url, 52, 36);
            $reg_link = "<a href=\"{$this->associated_obj->register_url}\">$reg_link_desc</a>";
            $acc_link = "<a href=\"{$this->associated_obj->accept_url}\">$acc_link_desc</a>";

            $this->template_vars["%invite.message%"]            = $this->associated_obj->inv_message;
            $this->template_vars["%invite.accept_url%"]         = $this->associated_obj->accept_url;
            $this->template_vars["%invite.accept_link%"]        = $acc_link;
            $this->template_vars["%invite.register_url%"]       = $this->associated_obj->register_url;
            $this->template_vars["%invite.register_link%"]      = $reg_link;
            $this->template_vars["%invite.invited_user_name%"]  = $this->associated_obj->inv_user_first_name;
            $this->template_vars["%invite.invited_user_email%"] = $this->associated_obj->inv_email;
            $this->template_vars["%recipient.email_address%"]   = $this->associated_obj->inv_email;
          }
          else {                                                // invitation accepted
            $invited = new User();
            $invited->load((int)$this->associated_obj->inv_user_id);
            $this->addUserData($invited, 'invite.invited');
            if(!empty($this->associated_obj->inv_relation_type)) {
              $this->template_vars["%invite.relation_type%"]      = $this->associated_obj->inv_relation_type;
            }
          }
        }
        else if(is_a($this->associated_obj, 'RelationData')) {
          $requester  = new User();
          $relateduser = new User();
          $requester->load((int)$this->associated_obj->user_id);
          $relateduser->load((int)$this->associated_obj->relation_id);

          $this->addUserData($requester, 'relation.inviter');
          $this->addUserData($relateduser, 'relation.invited');
          $this->template_vars["%relation.type%"] = Relation::lookup_relation_type($this->associated_obj->relationship_type);
          $this->template_vars["%relation.friend_list_url%"]  = UrlHelper::url_for(PA::$url.'/'.FILE_VIEW_ALL_MEMBERS, array('view_type' => 'in_relations', 'uid' => $this->associated_obj->user_id, 'login_required' => 'true'));
          $this->template_vars["%relation.friend_list_link%"] = UrlHelper::link_to(PA::$url.'/'.FILE_VIEW_ALL_MEMBERS, $this->template_vars["%relation.friend_list_url%"], null, array('view_type' => 'in_relations', 'uid' => $this->associated_obj->user_id, 'login_required' => 'true'));
          $this->template_vars["%relation.appr_deny_url%"]    = UrlHelper::url_for(PA::$url.'/'.FILE_VIEW_ALL_MEMBERS, array('view_type' => 'in_relations', 'uid' => $this->associated_obj->relation_id, 'login_required' => 'true'));
          $this->template_vars["%relation.appr_deny_link%"]   = UrlHelper::link_to(PA::$url.'/'.FILE_VIEW_ALL_MEMBERS, $this->template_vars["%relation.appr_deny_url%"], null, array('view_type' => 'in_relations', 'uid' => $this->associated_obj->relation_id, 'login_required' => 'true'));
        }
        else if(is_a($this->associated_obj, 'ReportAbuse')) {
          $author = new User();
          $author->load((int)$this->associated_obj->reporter_id);
          $this->addUserData($author, 'abuse.reporter');
          $this->template_vars["%abuse.report%"] = $this->associated_obj->body;
          $this->template_vars["%abuse.url%"]  = UrlHelper::url_for(PA_ROUTE_CONTENT, array('cid' => $this->associated_obj->parent_id));
          $this->template_vars["%abuse.link%"] = UrlHelper::link_to(PA_ROUTE_CONTENT, $this->template_vars["%abuse.url%"], null, array('cid' => $this->associated_obj->parent_id));
          $this->template_vars["%abuse.delete_comment_url%"]   = UrlHelper::url_for(PA::$url .'/deletecomment.php', array('comment_id' => $this->associated_obj->parent_id));
          $this->template_vars["%abuse.delete_comment_link%"]  = UrlHelper::link_to(PA::$url .'/deletecomment.php', $this->template_vars["%abuse.delete_comment_url%"], null, array('comment_id' => $this->associated_obj->parent_id));
          $this->template_vars["%abuse.delete_content_url%"]   = UrlHelper::url_for(PA::$url .'/deletecontent.php', array('cid' => $this->associated_obj->parent_id));
          $this->template_vars["%abuse.delete_content_link%"]  = UrlHelper::link_to(PA::$url .'/deletecontent.php', $this->template_vars["%abuse.delete_content_url%"], null, array('cid' => $this->associated_obj->parent_id));
        }
        else if(is_a($this->associated_obj, 'Group')) {
          $this->addGroupData($this->associated_obj);
        }
        else {
          Logger::log("Error exit: function MessageRenderer::getMessageSpecificData() - Unknown associated object type.");
          throw new Exception("MessageRenderer::getMessageSpecificData() - Unknown associated object type.");
        }
      }
   }
}

?>