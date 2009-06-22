
  <div class="forums">
  
    <table class="forum_main" align="center">
    <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td></td>
        <td align="right" style="padding-bottom:4px">
        <?php if($user_status & PaForumsUsers::_notmember) : ?>
          <a href="<?= $forums_url . "&board_id=" . $board->get_id() . "&action=join"?>">
            <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/join.gif" ?>" alt="join"  class="forum_buttons"/>
          </a>
        <?php endif; ?>
        </td>
        <td></td>
      </tr>
      <tr>
        <td class="left_top"></td>
        <td class="top_navigation">
          <?php echo $board->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="top_navigation">
          <div class="navig_button">
            <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
              <a href="<?= $forums_url . "&board_id=" . $board->get_id() . "&action=editSettings"?>">
                <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/settings.gif" ?>" alt="edit_settings"  class="forum_buttons"/>
              </a>
            <?php endif; ?>
            <?php if((@$board_settings['allow_users_create_category'] && !($user_status & PaForumsUsers::_anonymous))
                  || ($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
              <a href="<?= $forums_url . "&board_id=" . $board->get_id() . "&action=newCategory"?>">
                <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/new_board.gif" ?>" alt="new_board"  class="forum_buttons"/>
              </a>
            <?php endif; ?>
          </div>
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
                      <th class="thead" width="45%" align="left"><?php echo __('Forum'); ?></th>
                      <th class="thead" width="30%"><?php echo __('Forum Last post'); ?></th>
                      <th class="thead" width="10%"><?php echo __('Threads'); ?></th>
                      <th class="thead" width="10%"><?php echo __('Answers'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($categories as $category): ?>
                    <tr>
                      <td class="tcat" colspan="3">
                        <font style="font-weight:normal">
                          <?= __('Category:') ?>&nbsp;
                        </font>
                        <a href="<?= $forums_url . "&category_id=" . $category->get_id()?>"><?php echo $category->get_name() ?></a>
                      </td>
                      <td class="tcat">
                        <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
                         <div style="text-align: right; padding: 2px 8px 0 0">
                           <a href="<?= $forums_url . "&category_id=" . $category->get_id() . "&action=editCategory"?>">
                             <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/edit_small.gif" ?>" alt="edit_category"  class="forum_buttons"/>
                           </a>
                         </div>
                        <?php endif; ?>
                      </td>
                      <td class="tcat">
                        <?php if(($user_status & PaForumsUsers::_owner) || ($user_status & PaForumsUsers::_admin)) : ?>
                         <div style="text-align: right; padding: 2px 8px 0 0">
                           <a href="<?= $forums_url . "&category_id=" . $category->get_id() . "&action=delCategory"?>" onclick="javascript: return confirm_action('<?= __("Are you sure you want to delete this category?") ?>')">
                             <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/del_small.gif" ?>" alt="del_category"  class="forum_buttons"/>
                           </a>
                         </div>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php foreach ($category->statistics['forums'] as $forum): ?>
                    <tr align="center">
                      <td class="alt2">
                        <img src="<?php echo $theme_url . "/images/icons/" . $forum->get_icon('forum_default.gif')?>" alt="icon" />
                      </td>
                      <td class="alt1Active" align="left">
                        <a href="<?= $forums_url . "&forum_id=" . $forum->get_id()?>"><?php echo $forum->get_title() ?></a>
                        <div class="smallfont"><?php echo $forum->get_description() ?></div>
                      </td>
                      <td class="alt2">
                        <div class="smallfont" align="left">
                        <?php if(!empty($forum->statistics['last_post'])) : $post_id = $forum->statistics['last_post']->get_id()?>
                          <a href="<?=  $forums_url . "&thread_id=" .$forum->statistics['last_post']->get_thread_id() . "&post_id=$post_id#p_$post_id"?>">
                            <?= $forum->statistics['last_post']->get_title(24)?>
                          </a>
                          <div class="smallfont">
                            <?php echo __("Posted by") . ': '?>
                            <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $forum->statistics['last_post']->user->login_name ?>">
                              <?= $forum->statistics['last_post']->user->login_name ?>
                            </a>
                          </div>  
                        <?php else : ?>
                          <?= __("no posts") ?>
                        <?php endif; ?>
                        </div>
                      </td>
                      <td class="alt2"><?php echo $forum->statistics['nb_threads'] ?></td>
                      <td class="alt2"><?php echo $forum->statistics['nb_posts'] ?></td>
                    </tr>
                  <?php endforeach ?>
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
          <?php echo $board->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="right_bottom"></td>
      </tr>
      </table>
    </tr>
  </tfoot>
 
 </table>
</div> 