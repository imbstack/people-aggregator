<?php 
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
$error = FALSE;
$error_msg = '';

if (isset($_POST['submit_ranking'])) {  
  // global var $_base_url has been removed - please, use PA::$url static variable

  $error = "";
  require_once "ext/Ranking/Ranking.php";
  $parameters["1"] = is_numeric($_POST['param_1'])?$_POST['param_1']:set_error('Only numbers are allowed');
  $parameters["2"] = is_numeric($_POST['param_2'])?$_POST['param_2']:set_error('Only numbers are allowed');
  $parameters["3"] = is_numeric($_POST['param_3'])?$_POST['param_3']:set_error('Only numbers are allowed');
  $parameters["4"] = is_numeric($_POST['param_4'])?$_POST['param_4']:set_error('Only numbers are allowed');
  $parameters["5"] = is_numeric($_POST['param_5'])?$_POST['param_5']:set_error('Only numbers are allowed');
  $parameters["6"] = is_numeric($_POST['param_6'])?$_POST['param_6']:set_error('Only numbers are allowed');
  if (empty($error)) {
    $obj = new Ranking();
    foreach ($parameters as $id => $point) {
      $obj->ranking_id = $id;
      $obj->point = $point;
      $obj->update_parameter();
    }
    set_error('Ranking has been saved successfully');
  }
}

$page = new PageRenderer("setup_module",PAGE_SITE_RANKING, "Site Ranking", "container_two_column.tpl", "header.tpl", PUB, HOMEPAGE, $network_info);

$page->html_body_attributes ='class="no_second_tier network_config"';

uihelper_get_network_style();
uihelper_error_msg($error_msg);
echo $page->render();


function set_error($er) {
  global $error, $error_msg;
  $error = TRUE;
  $error_msg = $er;
}

function setup_module($column, $moduleName, $obj) {
  
  switch ($moduleName) {
    case 'RankingModule':
    break;
  }
}
?>


