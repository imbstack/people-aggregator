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
?>
  <div class="forums">
    <table class="forum_main" align="center">
    <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td class="left_top">&nbsp;</td>
        <td class="top_navigation">&nbsp;</td>
        <td class="right_top">&nbsp;</td>
      </tr>
      <tr>
        <td class="top_pagging" colspan="4">&nbsp;</td>
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
                      <td class="thead" width="30%"><?php echo __('Banned User'); ?></td>
                      <td class="thead" width="70%"><?php echo __('Administrator message'); ?></td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="alt1">
                        <?= $login_user->first_name . ' ' . $login_user->last_name ?>
                      </td>
                      <td class="alt1">
                        <?= $board_settings['banned_message'] ?>
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
        <td class="bottom_pagging" colspan="4">&nbsp;</td>
      </tr>
      <tr>
        <td class="left_bottom">&nbsp;</td>
        <td class="bottom_navigation">&nbsp;</td>
        <td class="right_bottom">&nbsp;</td>
      </tr>
      </table>
    </tr>
  </tfoot>
 </table>
</div> 