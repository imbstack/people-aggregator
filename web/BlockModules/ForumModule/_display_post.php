        <table class="board_inner" align="center">
        <tbody>
          <tr>
            <th class="thead" width="<?= $avatar_size['width']?>" style="font-weight:normal">
              <?php echo __("Author") . ': '?>
              <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $post->user->login_name ?>"><?= $post->user->login_name ?></a>
            </th>
            <th class="thead">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td class="post_title">
                    <label><?php echo __('Title'); ?> : </label>
                    <a id="<?= "p_".$post->get_id() ?>" name="<?= "p_".$post->get_id() ?>"><?php echo $post->get_title() ?></a>
                  </td>
                </tr>
              </table>
            </th>
          </tr>

          <tr valign="top">
            <td class="author_info" width="<?= $avatar_size['width']?>">
              <div class="forum_avatar">
                <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $post->user->login_name ?>">
                  <?php echo uihelper_resize_mk_user_img($post->user->picture, $avatar_size['width'], $avatar_size['height'], 'alt="'.$post->user->login_name.'"') ?>
                </a>
              </div>
              <div class="smallfont">
                <div><?php echo __('Posts') ?>: <?php echo ($post->user->user_id != -1) ? PaForumPost::countPaForumPost("user_id = ". $post->user->user_id) : '' ?> </div>
                <div><?php echo __('Last login') ?>: <?php echo ($post->user->user_id != -1) ? PA::date($post->user->last_login, 'short') /* date('Y-m-d',$post->user->last_login) */ : '' ?> </div>
                <div><?php echo __('Status') ?>: <?php echo ($post->user->user_id != -1) ? PaForumsUsers::getStatusString($post->user->user_id) : '' ?> </div>
              </div>
            </td>
            <td class="post_<?= $even_odd ?>">
              <div class='forum_post'>
                <?php echo $post->get_content() ?>
              </div>
            </td>
          </tr>
          <tr>
            <td class="post_date" width="<?= $avatar_size['width']?>">
              <div class="smallfont">
                <?php echo PA::datetime($post->get_created_at(), 'long', 'short') ?>
              </div>
            </td>
            <td class="buttons_panel" align="right">
              <?php if(PA::$login_uid == $post->get_user_id() && !($thread_status & PaForumThread::_locked)) : ?>
                <a href="<?= $forums_url . "&post_id=" . $post->get_id() .  "&page=" . $current_page . "&action=edit"?>">
                  <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/modify.gif" ?>" alt="modify"  class="forum_buttons"/>
                </a>
              <?php endif; ?>
              <?php if(($board_settings['allow_anonymous_post'] || ($user_status & PaForumsUsers::_allowed)) && !($thread_status & PaForumThread::_locked)) : ?>
                <a href="<?= $forums_url . "&thread_id=" . $thread->get_id() . "&post_id=" . $post->get_id() .  "&page=" . $current_page . "&mode=post" . "&action=quote"?>">
                   <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/quote.gif" ?>" alt="quote"  class="forum_buttons"/>
                </a>
                <a href="<?= $forums_url . "&thread_id=" . $thread->get_id() . "&post_id=" . $post->get_id() . "&page=" . $current_page . "&mode=post" . "&action=reply"?>">
                   <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/reply_sm.gif" ?>" alt="reply"  class="forum_buttons"/>
                </a>
              <?php endif; ?>
              <?php if($user_status & PaForumsUsers::_waiting) : ?>
                <div class="smallfont">
                  <?= __("You will be able to answer this post after admin approve your membership request") ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
          <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
          <tr>
            <td colspan="2" class="admin_buttons_panel">
                <a href="<?= $forums_url . "&thread_id=" . $thread->get_id() . "&user_id=" . $post->get_user_id() . "&action=banUser"?>">
                  <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/ban_user.gif" ?>" alt="ban_user"  class="forum_buttons"/>
                </a>
                <a href="<?= $forums_url . "&post_id=" . $post->get_id() .  "&page=" . $current_page . "&action=edit"?>">
                  <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/modify.gif" ?>" alt="modify"  class="forum_buttons"/>
                </a>
                <a href="<?= $forums_url . "&post_id=" . $post->get_id() .  "&page=" . $current_page . "&action=delPost"?>">
                  <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/delete.gif" ?>" alt="delete"  class="forum_buttons"/>
                </a>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
        </table>
