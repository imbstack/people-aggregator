<?php
/**
 *
 *  This is a sample email container template.
 *
 *  available message variables:
 *
 *  -   $subject
 *  -   $message
 *  -   all PA static variables and constants
 *
 *  NOTE:
 *
 *      Images for template should be placed within 'web/images' folder
 *      and should be referenced with absolute URL. For example:  PA::$url .'/images/myimage.jpg'
 *
 **/
?>
<TABLE width="675" border="0" align="center" cellpadding="8">
  <TR>
    <TD><IMG src="<?= PA::$url .'/images/marcDemo2.jpg' ?>" width="180" height="135"></TD>
    <TD>
        <strong><font color="#FF0000" face="Arial, Helvetica, sans-serif" size="5">
        <?= "This is only sample Email Container"?>
        </font></strong>
    </TD>
  </TR>
  <TR>
    <TD colspan="2">
    <table style="width:650px;">
     <tr>
        <td>
        <strong><font color="#0099ff" face="Arial, Helvetica, sans-serif" size="5">
        <?php echo $subject;?>
        </font></strong>
        </td>
      </tr>
    </table>
    </TD>
  </TR>
  <TR>
    <TD colspan="2" style="background-color: #ddd"><?php echo $message;?></TD>
  </TR>
  <TR>
    <TD colspan="2"><DIV style=" width: auto; margin: 0px 0px 5px 0px; border-top:#FF9900 1px dashed"></DIV><CENTER>
      <FONT face="Arial, Helvetica, sans-serif" size="1" color="#0099FF">PeopleAggregator
        is an open social network service by Broadband Mechanics Inc. &copy;2009</FONT><br>
      <FONT face="Arial, Helvetica, sans-serif" size="1" color="#0099FF"><A href="http://broadbandmechanics.com/who.html" style="color:#0099FF;">About
          us</A> | <A href="<?php echo PA::$url;?>/features.php" style="color:#0099FF;">Features</A> | <A href="<?php echo PA::$url;?>/faq.php" style="color:#0099FF;">FAQ</A></FONT>

          <br>
          <br>
          <A href="http://www.peepagg.net/"><IMG src="<?php echo PA::$url .'/images/palogo_black_bg.jpg';?>" alt="PeopleAggregator" width="135" height="75" border="0"></A><br>
      </FONT>
    </CENTER>
    </TD>
  </TR>
</TABLE>
