<div id="login_parent" >
<wbr /><style type="text/css"><!--
div#login_parent {
 background-color: white;
 width: 194px;
 padding: 2px 0px 2px 0px;
 border: solid 2px #eeeeee;
 float:right;
}
div#login_parent h4 {
 font-size: 1.2em;
 background-color: #e0e0e0;
}
div#login_parent h4 {
 margin: 2px 0px 2px 0px;
}
.comment {
  float:left;
  width:100%;
  margin-left:4px;
}
.comment_form b{
  font-size:13px;
}
--></style>
      <h4>Login to <?php echo PA::$site_name;?></h4>
      <div class="comment">
      <?php if(empty($login_uid)) {?>
      <span id="result">
       <form action="<?php echo PA::$url;?>/Widgets/Login/post_login.php" method="post" name="login_form" onsubmit="document.getElementById('return').value=window.location;">
         username:<input type="text" name="username" />
         password:<input type="password" name="password" />
         <input type="hidden" name="remember" value="1" />
         <input type="hidden" name="return" id="return" value="">
         <input type="submit" name="submit" value="Login" />    
       </form>   
       </span>
       <?php } else {?>
        <p>Successfully Login.</p>
        <a href="javascript:l=window.location;window.location='<?php echo PA::$url;?>/logout.php?return='+l">Click here</a> for logoff.
       <?php }?>
       </div>
      </div>