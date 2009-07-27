<?php
error_reporting(E_ALL);
require_once "InstallTests.class.php";

global $installer;
$installer->error = 0;

$tester = new InstallTests('dbTest', $installer->form_data);
?>

<html>
  <head>
      <link rel="stylesheet" type="text/css" href="/install/frame.css" media="screen" />
  </head>
  <body>
     <table>
        <?php
           $tester->run();
           echo $tester->showStatus($installer);
           $installer->config['peepagg_dsn'] = $tester->peepagg_dsn;
        ?>
     </table>
  </body>
</html>