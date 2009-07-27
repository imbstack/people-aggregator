<?php

require_once PA::$core_dir . "/web/extra/net_extra.class.php";

// This is called from db_update.php, ONCE, i.e. during installation.
function run_net_extra_once_only() {
  $ne = new net_extra();
  $ne->once_only_updates();
}

// This is called at the end of every db_update.php run, and if you
// run net_extra.php at the command line.
function run_net_extra() {
  $ne = new net_extra();
  $ne->safe_updates();
}

/* REMOVED - this script will not be called from command line anymore

// if we're browsing to this script directly, run everything now.
if (count(@$_SERVER['argv']) && (basename($_SERVER['argv'][0]) == basename(__FILE__))) {
  run_net_extra();
}
*/
?>