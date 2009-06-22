<?php
 
if(is_object($tiny_mce)) {
  echo $tiny_mce->installTinyMCE();
}

?>

  <div class="forums">
  
    <table class="forum_main" align="center">
    <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td class="left_top"></td>
        <td class="top_navigation" >
          <?php echo $thread->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="right_top"></td>
      </tr>
      <tr>
        <td></td>
        <td colspan="1" class="spacer">
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
                      <th class="thead align_center" width="100%">
                      <?php if($reply_type == 'post') : ?>
                        <?php echo __('Reply to').": <b>".$post->get_title() ."</b> ". __("post") ?>
                      <?php else : ?>  
                        <?php echo __('Reply to').": <b>".$thread->get_title() ."</b> ". __("thread") ?>
                      <?php endif; ?>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="alt1">
                        <div class="edit_reply">
<!--                        
                          <form name="reply_form" action="<?= "$forums_url&action=".(($quote_post) ? "quote" : "reply") ?>" method="POST" id="reply_form" class="reply_form">
-->                          
                          <form name="reply_form" action="<?= $forums_url ?>" method="POST" id="reply_form" class="reply_form">
                            <div class="reply_title">
                              <label for="form_data_reply_title"><span class="required">*</span><?= __("Reply title") ?>: </label>
                              <input type="text" name="form_data[reply_title]" id="form_data_reply_title" value="<?= ($reply_type == 'post') ? $post->get_title() : $thread->get_title() ?>" />
                            </div>
                            <div class="reply_content">
                              <label for="form_data_reply_content"><span class="required">*</span><?= __("Reply content") ?>: </label>
                              <textarea class="reply_content" name="form_data[reply_content]" id="form_data_reply_content" >
                              <?php if($quote_post) : ?>
                                  <div class="quote_header">
                                    <?= __("User ") . "<b>" . (($reply_type == 'post') ? $post->getAuthor()->login_name : $thread->getAuthor()->login_name) ."</b> " . __("wrote") . ":" ?>
                                  </div>
                                <div class="quote">
                                  <?= ($reply_type == 'post') ? $post->get_content() : $thread->get_content() ?>
                                </div><br />
                              <?php endif; ?>   
                              </textarea>
                            </div>
                            <?php if($allow_anonymous && ($user_status & PaForumsUsers::_anonymous)) : ?>
                            <div class="anonymous_data">
                              <div class="capcha1">
                                <label for="txtNumber"><span class="required">*</span><?= __("Enter the verification code") ?>: </label>
                                <input name="txtNumber" type="text" size="12">
                              </div>  
                              <div class="capcha">
                                  <?= __('Code') . ": " ?>
                                  <img src="/comment_verification.php" class="captcha" alt="captcha">
                              </div>
                            </div>
                            <div class="anonymous_data">
                              <div>
                                <label for="form_data_anonymous_name"><span class="required">*</span><?= __("Your name or email") ?>: </label>
                                <input name="form_data[anonymous_name]" id="form_data_anonymous_name" type="text">
                              </div>
                            </div>  
                            <?php endif; ?>
                            <?php if($reply_type == 'post') : ?>
                              <input name="post_id" id="post_id" type="hidden" value="<?= $post->get_id() ?>" />
                            <?php endif; ?>
                            <input name="thread_id" id="thread_id" type="hidden" value="<?= $thread->get_id() ?>" />
                            <input name="page" id="page" type="hidden" value="<?= $current_page ?>" />
                            <input name="mode" id="mode" type="hidden" value="<?= $reply_type ?>" />
                            <input name="action" id="action" type="hidden" value="<?= (($quote_post) ? "quote" : "reply") ?>" />
                            <div class="buttons_panel">
                            <a href="<?= $back_url ?>">
                              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/cancel.gif" ?>" alt="cancel" class="forum_buttons"/>
                            </a>&nbsp;
                            <a href="#" onclick="javascript: document.forms['reply_form'].submit(); return false;">
                              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/send.gif" ?>" alt="send" class="forum_buttons"/>
                            </a>
                            </div>
                          </form>
                        </div>
                      </td>
                    </tr>
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
          <?php echo $thread->getNavigation($forums_url, 'navigation') ?>
        </td>
        <td class="right_bottom"></td>
      </tr>
      </table>
    </tr>
  </tfoot>
  
 </table>
</div> 