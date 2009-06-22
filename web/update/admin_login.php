<?php

if (!defined("INCLUDED_PAGE_PHP")) {
  echo __("This file may not be called directly.");
  exit;
}

// include this file to require an admin login and to generate the login interface.


if (!@$_SESSION['is_admin']) {
    if (!isset($admin_password)) {
        ?>

<h1><?= __('No admininstrator password configured') ?></h1>

<p><?= __('To use the updater, add a line like this to') ?> <code>local_config.php</code>:</p>

<code>$admin_password = "<i>password</i>";</code>

<p>(replacing <i><code>password</code></i> with a hard-to-guess password.)</p>

        <?
	exit;
    }

    $msg = "";
    $pwd = @$_REQUEST['admin_password'];
    if ($pwd) {
        if ($pwd == $admin_password) {
            $_SESSION['is_admin'] = TRUE;
        } else {
            $msg = <<<EOF
<div style="color: red">Password incorrect.  Try again!</div>
EOF;
        }
    }

    if (!@$_SESSION['is_admin']) {
        ?>

<h1>Administrator login</h1>

<p>You must enter the administrator password (configured when setting up the system) to access this page.</p>

<?=$msg?>

<form method="POST">
<input type="hidden" name="op" value="login"/>
Admin password: <input id="admin_password" type="password" name="admin_password" size="40"/> <input type="submit" value="<?= __('Log in') ?>"/>
</form>

<script><!--
document.getElementById("admin_password").focus();
// --></script>

        <?
        exit;
    }
}

?>