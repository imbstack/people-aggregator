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
        <td class="top_navigation">&nbsp;</td>
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
                      <td class="thead" width="100%">
                      <?php if($edit_type == 'post') : ?>
                        <?php echo "<b>".__('Edit post') ."</b> " ?>
                      <?php else : ?>  
                        <?php echo "<b>".__('Edit thread') ."</b> " ?>
                      <?php endif; ?>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="alt1">
                        <div class="edit_reply">
<!--                          
                          <form name="edit_form" action="<?= "$forums_url/action=".(($edit_type == 'post') ? "updatePost" : "updateThread") ?>" method="POST" id="edit_form" class="reply_form">
-->                          
                          <form name="edit_form" action="<?= $forums_url ?>" method="POST" id="edit_form" class="reply_form">
                            <div class="reply_title">
                              <label for="form_data_edit_title"><span class="required">*</span><?= ($edit_type == 'post') ? __("Post title") : __("Thread title") ?>: </label>
                              <input type="text" name="form_data[edit_title]" id="form_data_edit_title" value="<?= ($edit_type == 'post') ? $post->get_title() : $thread->get_title() ?>" />
                            </div>
                            <div class="reply_content">
                              <label for="form_data_edit_content"><span class="required">*</span><?= ($edit_type == 'post') ? __("Post content") : __("Thread content") ?>: </label>
                              <textarea class="reply_content" name="form_data[edit_content]" id="form_data_edit_content" >
                                <?= ($edit_type == 'post') ? $post->get_content() : $thread->get_content() ?><br />
                              </textarea>
                            </div>
                            <?php if($edit_type == 'post') : ?>
                              <input name="post_id" id="post_id" type="hidden" value="<?= $post->get_id() ?>" />
                            <?php endif; ?>
                            <input name="thread_id" id="thread_id" type="hidden" value="<?= $thread->get_id() ?>" />
                            <input name="page" id="page" type="hidden" value="<?= $current_page ?>" />
                            <input name="mode" id="mode" type="hidden" value="<?= $edit_type ?>" />
                            <input name="action" id="action" type="hidden" value="<?= (($edit_type == 'post') ? "updatePost" : "updateThread") ?>" />
                            <div class="buttons_panel">
                            <a href="<?= $back_url ?>">
                              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/cancel.gif" ?>" alt="cancel" class="forum_buttons"/>
                            </a>&nbsp;
                            <a href="#" onclick="javascript: document.forms['edit_form'].submit(); return false;">
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
        <td class="bottom_navigation">&nbsp;</td>
        <td class="right_bottom"></td>
      </tr>
      </table>
    </tr>
  </tfoot>
  
 </table>
</div> 