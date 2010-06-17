<html>
<body style="background-color: #ca342c;">
    <div style="background-color: #fff; border: 2px solid black; padding: 12px; width: 70%; margin-top: 120px; margin-left: auto; margin-right: auto;">
      <div style="width: 219px; height: 35px; background-image: url(/images/pa_logo_static_pages.gif)"></div>
      <h1 style="font-weight: bold; font-size: 18px; color: #ad2525;">An Exception was Thrown</h1>
      <p>Code: <b><?= $code_esc ?></b></p>
      <h2 style="font-weight: bold; font-size: 12px; color: #ad2525;">Message:</h2>
      <p><?= $msg_esc ?></p>
      <center>
        <a href="<?= PA::$url . PA_ROUTE_HOME_PAGE ?>" title="Home"><?= __("Click here to go back to the Home page") ?></a>
      </center>
    </div>
</body>
</html>
