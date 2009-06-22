<?php
global $core_dir;
require_once $core_dir . "/web/extra/db_update_page.class.php";

// if (realpath(@$_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
  if (!db_update_page::check_quiet()) {
    echo "<h1>update PeopleAggregator CORE database schema</h1>";
  }

  $p = new db_update_page();
  $p->main();

// }

?>
