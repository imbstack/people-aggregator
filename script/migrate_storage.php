<?php

include dirname(__FILE__)."/../config.inc";
require_once "api/Storage/Storage.php";

while (@ob_end_clean());
$s = new Storage();
$s->migrateLegacyFiles(TRUE);

?>