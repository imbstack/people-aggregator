
  <div class="forums">

    <table class="forum_main" align="center">
    <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <?php if(isset($forum->pagination->pagging['pages'])) : ?>
      <tr>
        <td></td>
        <td class="top_pagging" colspan="2">
          <?php echo __("Pages: ") ?>
          <?php echo $forum->pagination->getPaggingLinks($forums_url . "&forum_id=" . $forum->get_id(),
                                                         'page', 'pagging', 'pagging_selected') ?>
        </td>
        <td></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td class="left_top"></td>
        <td class="top_navigation" >
          <?php echo $forum->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="top_navigation">
        <?php if(!($user_status & PaForumsUsers::_waiting) && !($user_status & PaForumsUsers::_limited) &&
                 !($user_status & PaForumsUsers::_anonymous) && ($user_status & PaForumsUsers::_allowed))  : ?>
          <div class="navig_button">
            <a href="<?= $forums_url . "&forum_id=" . $forum->get_id() . "&action=newTopic"?>">
              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/new_topic.gif" ?>" alt="new_topic"  class="forum_buttons"/>
            </a>
          </div>
        <?php endif; ?>
        </td>
        <td class="right_top"></td>
      </tr>
      <tr>
        <td></td>
        <td colspan="2" class="spacer">
          <?php include("forum_header.tpl.php"); ?>
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
                  <table class="board_inner" align="center">
                  <thead>
                    <tr align="center">
                      <th class="thead" width="5%">&nbsp;</th>
                      <th class="thead" width="45%" align="left"><?php echo __('Thread'); ?></th>
                      <th class="thead" width="30%"><?php echo __('Thread Last post'); ?></th>
                      <th class="thead" width="10%"><?php echo __('Posts'); ?></th>
                      <th class="thead" width="10%"><?php echo __('Viewed'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="tcat" colspan="3">
                        <font style="font-weight:normal">
                          <?= __('Forum:') ?>&nbsp;
                        </font>
                        <a href="<?= $forums_url . "&forum_id=" . $forum->get_id()?>"><?php echo $forum->get_title() ?></a>
                      </td>
                      <td class="tcat">
                        <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
                         <div style="text-align: right; padding: 2px 8px 0 0">
                           <a href="<?= $forums_url . "&forum_id=" . $forum->get_id() . "&action=editForum"?>">
                             <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/edit_small.gif" ?>" alt="edit_forum"  class="forum_buttons"/>
                           </a>
                         </div>
                        <?php endif; ?>
                      </td>
                      <td class="tcat">
                        <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
                         <div style="text-align: right; padding: 2px 8px 0 0">
                           <a href="<?= $forums_url . "&forum_id=" . $forum->get_id() . "&action=delForum"?>" onclick="javascript: return confirm_action('<?= __("Are you sure you want to delete this forum?") ?>')">
                             <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/del_small.gif" ?>" alt="del_forum"  class="forum_buttons"/>
                           </a>
                         </div>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php foreach ($threads as $thread) : $user = $thread->statistics['user'] ?>
                    <tr align="center">
                      <td class="alt2">
                        <img src="<?php echo $theme_url . "/images/icons/" . $thread->getThreadIcon("thread_default.gif") ?>" alt="icon" />
                      </td>
                      <td class="alt1Active" align="left">
                        <a href="<?= $forums_url . "&thread_id=" . $thread->get_id()?>"><?php echo $thread->get_title() ?></a>
                          <div class="smallfont">
                            <?php echo __('Created by') . ": " ?>
                            <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->login_name ?>">
                              <?= $user->login_name ?>
                            </a>
                          </div>
                            <div class="post_date">
                              <?= PA::datetime($thread->get_created_at(), 'long', 'short') ?>
                            </div>
                      </td>
                      <td class="alt2">
                        <div class="smallfont" align="left">
                          <?php if(!empty($thread->statistics['last_post'])) : $post = $thread->statistics['last_post']; $post_id = $post->get_id() ?>
                          <a href="<?= $forums_url."&thread_id=".$post->get_thread_id()."&post_id=$post_id#p_$post_id"?>">
                            <?= $post->get_title(24) ?>
                          </a>
                          <div class="smallfont">
                            <?php echo __("Posted by") . ': '?>
                            <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $post->user->login_name ?>">
                              <?= $post->user->login_name ?>
                            </a>
                          </div>
                        <?php else : ?>
                               <?= __("no posts") ?>
                        <?php endif; ?>
                        </div>
                      </td>
                      <td class="alt2"><?php echo $thread->statistics['posts'] ?></td>
                      <td class="alt2"><?php echo $thread->get_viewed() ?></td>
                    </tr>
                  <?php endforeach ?>
                  </tbody>
                  </table>
                </td>
              </tr>
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
          <?php echo $forum->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="right_bottom"></td>
      </tr>
      <?php if(isset($forum->pagination->pagging['pages'])) : ?>
      <tr>
        <td></td>
        <td class="bottom_pagging" colspan="2">
          <?php echo __("Pages: ") ?>
          <?php echo $forum->pagination->getPaggingLinks($forums_url . "&forum_id=" . $forum->get_id(),
                                                         'page', 'pagging', 'pagging_selected') ?>
        </td>
        <td></td>
      </tr>
      <?php endif; ?>
      </table>
    </tr>
  </tfoot>

 </table>
</div>
