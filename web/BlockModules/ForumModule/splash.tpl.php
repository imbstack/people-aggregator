<?php
?>
<div class="forums">
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
                  <tbody>
                    <tr>
                      <td class="" align="center">
                        <?php echo $user_msg ?>
                        <?php if($allowed) : ?>
                          <div class="center">
                          <?php echo __('Click here to create it') . ": " ?>&nbsp;&nbsp;
                          <a href="<?= $url_src ?>">
                             <img STYLE="vertical-align: middle" src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/new_board.gif" ?>" alt="new_board"  class="forum_buttons"/>
                          </a>
                          </div>
                        <?php endif; ?>
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
</div>