<html>
<body style="background-color: #ff1010;">
    <div style="background-color: #fff; border: 4px solid black; padding: 12px; width: 70%; margin-top: 120px; margin-left: auto; margin-right: auto;">
      <div style="width: 219px; height: 35px; background-image: url(/images/pa_logo_static_pages.gif)"></div>
      <center><h1 style="font-weight: bold; font-size: 28px; color: #ad2525;">Attention!</h1></center>
      <?php if(!empty($message)) : ?>
        <p><?= $message ?></p>
      <?php endif; ?>
      <?php if(!empty($details)) : ?>
        <p>Details: <b><?= $details ?></b></p>
      <?php endif; ?>
    </div>
</body>
</html>
