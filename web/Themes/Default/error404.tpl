<html>
<body style="background-color: #fed7d7;">
    <div style="background-color: #fff; border: 2px solid red; padding: 12px; width: 70%; margin-top: 120px; margin-left: auto; margin-right: auto;">
      <div style="width: 219px; height: 35px; background-image: url(/images/pa_logo_static_pages.gif)"></div>
      <h1 style="font-weight: bold; font-size: 18px; color: #ad2525;">The requested URL not found on this server</h1>
      <?php if(!empty($file_name)) : ?>
        <p>URL: <b><?= $file_name ?></b></p>
      <?php endif; ?>

      <?php if(!empty($message)) : ?>
      <h2 style="font-weight: bold; font-size: 12px; color: #ad2525;">Server message:</h2>
        <p><?= $message ?></p>
      <?php endif; ?>
    </div>
</body>
</html>
