<?php
include "center_inner_private.tpl";
?>
 
<div class="description"><?= __("Use these settings to customize the look of your Network pages") ?><br><?= __("that others will see.") ?></div>
<div id="conf_container"></div>
<script type="text/javascript" language="javascript">
  var user_js_data = '<?= $json_data ?>';
  var settings_type = '<?= $settings_type ?>';
  var uid = '<?= $uid ?>';
  var gid = '<?= $gid ?>';
  var formAction = '<?= $page_url ?>';
  var base_url = '<?= PA::$url ?>';
</script>