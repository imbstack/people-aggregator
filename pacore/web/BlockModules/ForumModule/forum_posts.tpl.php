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

<div class="forums">

  <table class="forum_main">
  <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <?php if(isset($thread->pagination->pagging['pages'])) { :?>
      <tr>
        <td></td>
        <td class="top_pagging" colspan="1">
          <?php echo __("Pages: ")?>
          <?php echo $thread->pagination->getPaggingLinks($forums_url."&thread_id=".$thread->get_id(), 'page', 'pagging', 'pagging_selected')?>
        </td>
        <td></td>
      </tr>
      <?php endif;
}?>
      <tr>
        <td class="left_top"></td>
        <td class="top_navigation" >
          <?php echo $thread->getNavigation($forums_url, 'navigation')?>
        </td>
        <td class="right_top"></td>
      </tr>
      <tr>
        <td></td>
        <td colspan="1" class="spacer">
          <?php include("forum_header.tpl.php");?>
        </td>
        <td></td>
      </tr>
      </table>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td colspan="4">
        <table class="board">
        <thead>
          <tr>
            <th class="board_left_top"></th>
            <th class="board_mid_top"></th>
            <th class="board_right_top"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="board_left_mid"></td>
            <td>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table class="board_inner" >
                  <tbody>
                    <tr>
                      <th class="thead" width="<?=$avatar_size['width']?>" style="font-weight:normal">
                        <?php echo __("Author").': '?>
                        <a href="<?=PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$created_by->login_name?>"><?=$created_by->login_name?></a>
                      </th>
                      <th class="thead">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                          <tr>
                            <td class="post_title">
                              <label><?php echo __('Thread');?> : </label>
                              <a id="<?="p_".$thread->get_id()?>" name="<?="p_".$thread->get_id()?>"><?php echo $thread->get_title()?></a>
                            </td>
                          </tr>
                        </table>
                      </th>
                    </tr>
                    <tr valign="top">
                      <td class="author_info" width="<?=$avatar_size['width']?>">
                      <div class="forum_avatar">
                          <a href="<?=PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$created_by->login_name?>">
                            <?php echo uihelper_resize_mk_user_img($created_by->picture, $avatar_size['width'], $avatar_size['height'], 'alt="'.$created_by->login_name.'"')?>
                          </a>
                        </div>
                        <div class="smallfont">
                          <div><?php echo __('Posts');?>: <?php echo PaForumPost::countPaForumPost("user_id = $created_by->user_id")?> </div>
                          <div><?php echo __('Last login');?>: <?php echo PA::date($created_by->last_login, 'short')// date('Y-m-d',$created_by->last_login)?> </div>
                          <div><?php echo __('Status')?>: <?php echo PaForumsUsers::getStatusString($created_by->user_id)?> </div>
                        </div>
                      </td>
                      <td class="post_odd">
                        <div class='forum_post'>
                          <?php echo $thread->get_content()?>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="post_date" width="<?=$avatar_size['width']?>">
                        <div class="smallfont">
                          <?php echo PA::datetime($thread->get_created_at(), 'long', 'short');?>
                        </div>
                      </td>
                      <td class="buttons_panel" align="right">
                        <?php if(PA::$login_uid == $thread->get_user_id() && !($thread_status&PaForumThread::_locked)) { :?>
                          <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&mode=thread"."&action=edit"?>">
                            <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/modify.gif"?>" alt="modify"  class="forum_buttons"/>
                          </a>
                        <?php endif;
}?>
                        <?php if(($board_settings['allow_anonymous_post'] || ($user_status&PaForumsUsers::_allowed)) && !($thread_status&PaForumThread::_locked)) { :?>
                          <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&mode=thread"."&action=quote"?>">
                            <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/quote.gif"?>" alt="quote"  class="forum_buttons"/>
                          </a>
                          <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&mode=thread"."&action=reply"?>">
                            <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/reply_sm.gif"?>" alt="reply"  class="forum_buttons"/>
                          </a>
                        <?php endif;
}?>
                        <?php if($user_status&PaForumsUsers::_waiting) { :?>
                          <div class="smallfont">
                            <?=__("You will be able to answer this topic after admin approve your membership request")?>
                          </div>
                        <?php endif;
}?>
                      </td>
                    </tr>
                  <?php if(($user_status&PaForumsUsers::_owner) || ($user_status&PaForumsUsers::_admin)) { :?>
                    <tr>
                      <td colspan="2" class="admin_buttons_panel">
                        <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&user_id=".$created_by->user_id."&action=banUser"?>">
                          <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/ban_user.gif"?>" alt="ban_user"  class="forum_buttons"/>
                        </a>
                        <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&mode=lock"."&action=threadStatus"?>">
                          <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/lock.gif"?>" alt="lock"  class="forum_buttons"/>
                        </a>
                        <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&mode=sticky"."&action=threadStatus"?>">
                          <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/sticky.gif"?>" alt="sticky"  class="forum_buttons"/>
                        </a>
                        <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&action=edit"?>">
                          <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/modify.gif"?>" alt="modify"  class="forum_buttons"/>
                        </a>
                        <a href="<?=$forums_url."&thread_id=".$thread->get_id()."&action=removeThread"?>">
                          <img src="<?php echo $theme_url."/images/buttons/".PA::$language."/remove.gif"?>" alt="remove"  class="forum_buttons" onclick="javascript: return confirm_action('<?=__("Are you sure you want to delete this topic?")?>')"/>
                        </a>
                      </td>
                    </tr>
                  <?php endif;
}?>
                  </tbody>
                  </table>
                </td>
              </tr>
              <?php $i = 0;
foreach($posts as $post) { : $even_odd = ((++$i%2) ? "even" : "odd")?>
              <tr>
                <td>
                  <?php include("_display_post.php")?>
                </td>
              </tr>
              <?php endforeach;
}?>
            </table>
          </td>
          <td class="board_right_mid"></td>
        </tr>
        </tbody>
        <tfoot>
          <tr>
            <th class="board_left_bottom"></th>
            <th class="board_mid_bottom"></th>
            <th class="board_right_bottom"></th>
          </tr>
        </tfoot>
        </table>
      </td>
    </tr>
  </tbody>

  <tfoot>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td></td>
        <td colspan="1" class="spacer"></td>
        <td></td>
      </tr>
      <tr>
        <td class="left_bottom"></td>
        <td class="bottom_navigation">
          <?php echo $thread->getNavigation($forums_url, 'navigation')?>
        </td>
        <td class="right_bottom"></td>
      </tr>
      <?php if(isset($thread->pagination->pagging['pages'])) { :?>
      <tr>
        <td></td>
        <td class="bottom_pagging" colspan="2">
          <?php echo __("Pages: ")?>
          <?php echo $thread->pagination->getPaggingLinks($forums_url."&thread_id=".$thread->get_id(), 'page', 'pagging', 'pagging_selected')?>
        </td>
        <td></td>
      </tr>
      <?php endif;
}?>
      </table>
    </tr>
  </tfoot>

  </table>
</div>
