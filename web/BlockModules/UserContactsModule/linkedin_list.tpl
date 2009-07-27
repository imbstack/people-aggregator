<?php

require_once "web/includes/classes/xHtml.class.php";

  $tags        = new xHtml();
  $js_submit = '
                   modal_hide();
                   document.forms[\'linkedin_invite_form\'].submit();
                   return false;
                ';
  $js_set_reset_all_chkbx = <<<EOT
  <script type="text/javascript" language="javascript">
    function select_all_chkbx() {
        $("input[@id^='contact[']").each(function() {
            this.checked = "checked";
        });
    }
    function reset_all_chkbx() {
        $("input[@id^='contact[']").each(function() {
            this.checked = "";
        });
    }
  </script>
EOT;
?>

<?php echo $js_set_reset_all_chkbx; ?>


<div id="pl_response" class="invite_container">


    <?php if((count($contacts) > 0)){ ?>
    <form name="linkedin_invite_form" id="linkedin_invite_form" action="" method="post">
      <input type="hidden" name="ajax_submit" value="submit" />
      <input type="hidden" name="import_type" value="linkedin" />
      <input type="hidden" name="action" value="import_contacts" />
      <input type="hidden" name="contacts_encoded" value="<?= base64_encode(serialize($mapped_contacts)) ?>" />
      <div class="invite_list_container">
      <ul class="invite_list_list">
      <?php
        $i = 0;
        foreach($contacts as $k => $contact) {
          $even_odd = (++$i % 2) ? "even" : "odd";
          $name  = $contact['name'];
          $email = $contact['email'];
          $contact_type = $contact['type'];
          $email_tag = "contact[$k][email]";
          $name_tag  = "contact[$k][name]"; ?>
          <li class="invite_list_row invite_list_<?=$even_odd?>">
            <ul class="invite_list_row">
            <li class="invite_name">
              <label for="<?= $email_tag ?>"><?= $name ?></label>
            </li>
            <li class="invite_email">
              <?= $email ?>
              <span class="field_text">(<?= $contact_type ?>)</span>
              <?php $opts = array('type' => 'hidden', 'id'    => $name_tag,
                                  'name' =>  $name_tag, 'value' => $name);
                echo $tags->xhtml_tag('input', $opts, true );
              ?>
            </li>
            <li class="invite_select">
               <?php $opts = array('type' => 'checkbox', 'id'    => $email_tag,
                                   'name' =>  $email_tag, 'value' => $email);
                 if ($email != '-no email address-') {
                    $opts['checked'] = 'checked';
                 } else {
                    $opts['readonly'] = 'readonly';
                 }
                 echo $tags->xhtml_tag('input', $opts, true );
               ?>
            </li>
            </ul>
          </li>
      <?php } ?>
      </ul>
      </div>
      <div class="invite_list_button" style="padding-bottom: 4px; margin-bottom:4px;">
         <input type="button" name="select_all_contacts" id="cont_select_all" onclick="javascript: select_all_chkbx();" value="Select All"/>
         <input type="button" name="reset_all_contacts" id="cont_reset_all" onclick="javascript: reset_all_chkbx();" value="Unselect All"/>
         <input type="submit" name="submit_contacts_linkedin" id="submit_contacts_linkedin" onclick="javascript:<?= $js_submit ?>" value="Submit"/>
<!--
         <img src="<?= PA::$theme_url ?>/images/bt_submit.gif" class="input_image" name="submit_contacts_linkedin" id="submit_contacts_linkedin" style="cursor: pointer" alt="Login" titile="Login" onclick="javascript:<?= $js_submit ?>"/>
-->
      </div>
    </form>
   <?php } ?>
</div>
