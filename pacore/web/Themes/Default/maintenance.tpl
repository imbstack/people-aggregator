<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Site Under Maintenance</title>
    <style type="text/css">

    body {
      font-family: Arial, Verdana;
      font-size: 14px;
    }

    p {
      margin: 12px;
      margin-left: 68px;
      margin-right: 68px;
    }
    #box_login {
       width: 470px;
       margin: 0 0 30px 50px;
       border-bottom: 1px solid #989898;
       border-right: 1px solid #989898;
       border-top: 1px solid #DDD;
       border-left: 1px solid #DDD;
    }

    #login_simple ul {
      list-style: none;
      display: block;
    }

    #login_simple ul li {
      display: block;
    }

    #login_simple ul.login_box_simple li ul.row_simple {
      text-align: left;
    }

    #login_simple ul.login_box_simple li ul.box_simple li {
      display: inline;
    }

    #login_simple ul.login_box_simple li ul.row_simple li.label_simple {
      display: block;
      float: left;
      width: 124px;
    }
    #login_simple ul.login_box_simple li ul.row_simple li.input_simple input.input_box {
      height: 18px;
      width: 156px;
      font-family: Arial,Helvetica,sans-serif;
      font-size: 12px;
      border: 1px solid silver;
    }
</style>
<!--
    <link rel="stylesheet" href="<?=PA::$theme_url?>/network.css" />
-->
</head>
<body style="background-color: silver;">
    <div style="background-color: #fff; border: 2px solid orange; padding: 12px; width: 70%; margin-top: 120px; margin-left: auto; margin-right: auto;">
      <div style="width: 219px; height: 35px; background-image: url(/images/pa_logo_static_pages.gif)"></div>
      <h1 style="font-weight: bold; font-size: 18px; color: #ad2525;">Site Under Maintenance</h1>
       <p><?=__("Network is temporarily closed. We apologize for any inconvenience and thank you for your patience as we improve the system. Please try after some time. Currently, only site administrator can login.") ?></p>
       <center>
        <div id="box_login">
          <form name="login_form" action="dologin.php?action=login" method="post" id="login_simple" class="loginform active">
            <ul class="login_box_simple">
              <li>
                <ul class="row_simple">
                  <li class="label_simple"><label for="username">User name: </label></li>
                  <li class="input_simple"><input type="text" size="15" name="username" class="input_box" id="username" value="<?= $uname ?>" /></li>
                </ul>
              </li>
              <li>
                <ul class="row_simple">
                  <li class="label_simple"><label for="password">Password: </label></li>
                  <li class="input_simple"><input type="password" size="15" name="password" class="input_box" id="password" /></li>
                </ul>
              </li>
              <li>
                <ul class="box_simple">
                  <li><input  type="image" id="loginbutton" alt="log in" value="log in" src="<?= $theme_url ?>/images/login-butt.gif" />
                </ul>
              </li>
            </ul>
          </form>
      </div>
      </center>
    </div>
</body>
</html>
