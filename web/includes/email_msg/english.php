<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
/*
 * Project:     PeopleAggregator: a social network development platform
 * File:        english.php - English language e-mail messages
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: This file handles all the emails sent by the system.
 * we can add a new email just by defining a new mail_subject, mail_message frame.
 * The latest version of PeopleAggregator can be obtained from:
 * http://update.peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
// SITE NAME
require_once dirname(__FILE__)."/../../../config.inc";

$site_name = PA::$site_name;
$page_url  = PA::$url;

$mail_footer_message =   "<TR>
    <TD colspan=\"2\"><DIV style=\" width: auto; margin: 0px 0px 5px 0px; border-top:#FF9900 1px dashed\"></DIV><CENTER>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"1\" color=\"#0099FF\">$site_name
        is an open social network service by Broadband Mechanics Inc. &copy;2008</FONT><br>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"1\" color=\"#0099FF\"><A href=\"http://broadbandmechanics.com/who.html\" style=\"color:#0099FF;\">About
          us</A> | <A href=\"http://www.peepagg.net/features.php\" style=\"color:#0099FF;\">Features</A> | <A href=\"http://www.peepagg.net/faq.php\" style=\"color:#0099FF;\">FAQ</A></FONT>

          <br>
          <br>
          <A href=\"http://creativecommons.org/\"><IMG src=\"$page_url/images/cc.srr.primary.gif\" alt=\"Creative Commons\" width=\"75\" height=\"25\" border=\"0\"></A><br>
      </FONT>
    </CENTER></TD>
  </TR>";
  
// FOR MAILING SUBJECT AND MESSAGES
// invite mail subject and message
$invite_pa_subject = "%first_name% %last_name% has invited you to join %network_name% network!";

$invite_pa_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\"></FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name% %last_name%
      has invited you to join %network_name% network!</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"\><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%invitee_image%</A>Hey
      %invited_user_name%,<br>
      <br>
    I've
          discovered yet another social network called $site_name, but
          this one is different, it's a 'digital lifestyle aggregator' (DLA.)<br>

          <br>
          <br>
          %network_name% 's Description
          <br>
          <br>
          %network_description%
          <br>
          <br>
          By joining $site_name
          you can import your own external account information from:<br>
          <br><TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">
            <TR>
              <TD>
           MySpace, Facebook, YouTube, Google-video and Flickr <FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">&mdash;</FONT> and
           combine that external info with any of your internal PeopeAggregator
           info <FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">&mdash;</FONT> and
           create Widgets with this info <FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">&mdash;</FONT> and
           place those Widgets in your blog, other social networks or dashboard
           interfaces.<br>

      </TD></TR></TABLE>
          <br>
          $site_name provides you with your own blog, personal pages, media
          gallery and allows you to create your own Groups.<br>
          <br>
          But it also allows you to create your own MySpace-in-a-Box - your own
          social network!<br>
          <br>
          You'll be able to share you media, moderate your own Groups and Networks
          and blog to your heart's content.<br>

          <br>
          Click here to register:<br>
           %register_url%<br>
          <br>
          Or, if you are already registered on $site_name, click here to
          let me know you're already in:<br>
          %accept_url%<br>

          <br>
          Here is the personalized message for you from %first_name% %last_name%
          <br>
          <br>
          %message%
          <br>
          <br>
          Thanks<br>
%first_name% %last_name%
<br>
    </FONT>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
  
$invite_pa_message .= $mail_footer_message;
$invite_pa_message .="</TABLE>";

// invite acceptance mail subject and message

$invite_accept_pa_subject = "%first_name% has accepted your invitation to join %network_name% network in %site_name%";
$invite_accept_pa_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has accepted your invitation to join %network_name% network in %site_name%</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Congratulations!<br>
      <br>
     %first_name% has accepted your invitation to join %network_name% network in %site_name%<br>

          <br>
          You have defined %first_name% as a %relation_type% kind of relationship.<br>
          <br>
          To view %first_name%'s public blog page view the following link:<br>
          <A href=\"%invited_user_url%\" style=\"color:#666666\">%invited_user_url%</A><br>
          <br>
          You can now share media with, send messages to and invite %first_name% into any of your <A href=\"%group_url%\"  style=\"color:#666666\">Groups</A> and/or <A href=\"%network_url%\"  style=\"color:#666666\">Networks.</A><br>

          <br>
          You can also control access to your profile info, content, media and
          meta-data by selecting 'Immediate Relations' in any of your 'Edit your
          Account' settings screens.
          <br>
          <br>
          Thanks<br>
          The $site_name Team
      <br>
    </FONT>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$invite_accept_pa_message .= $mail_footer_message;  
$invite_accept_pa_message.="</TABLE>";

// forgot password mail subject and message
$forgot_password_subject = "Your $site_name username/password information";
$forgot_password_message =" <TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
      %first_name%, <br>
        <br>
      As we understand it, you have requested a copy of your current username
      and password. Here they are: </FONT></P>
      <TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">

        <TR>
          <TD align=\"center\"><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"> username:
            %user_name%<br>
            </FONT></TD>
        </TR>
      </TABLE>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        To change your password, click on the link below (or copy the URL and
        paste it into your browser):<br>

        <A href=\"$page_url/change_password.php?log_nam=%user_name%&amp;uid=%user_id%&forgot_password_id=%forgot_password_id%\" style=\"color:#666666\">$page_url/change_password.php?log_nam=%user_name%&amp;uid=%user_id%&amp;forgot_password_id=%forgot_password_id%</A><br>
        <br>
        Thanks<br>
        The $site_name Team
        <br>
        <br>
        </FONT>        </P>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
  
$forgot_password_message .= $mail_footer_message;
$forgot_password_message .= "</TABLE>";

// Friend request mail subject and message
$friend_request_subject = "%first_name% %last_name% has requested to add you as a friend";

$friend_request_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name% %last_name%
      has requested to add you as a friend</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%requestor_image%</A>Hey
          %requested_user%, <br>
        <br>
      %first_name% has requested to establish a relationship with you.  They wish
      to make you one of their friends on $site_name.<br>

      <br>
      If you are already a member of $site_name, click on this link
      to accept this request:</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <A href=\"%approve_deny_url%\" style=\"color:#666666\">%approve_deny_url% </A><br>
        <br>
        Once you have accepted this invitation, you will be able to:</FONT>
      <TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">
        <TR>

          <TD><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">share
              any of your public media with %first_name% %last_name%<br>

              view any of %first_name% %last_name%'s controlled access profile info, content
              or meta-data <br>
              have a private message sent to you by %first_name% <br>
              be invited into any of %first_name% 's Groups or Networks.</FONT></TD>
        </TR>
      </TABLE>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>

      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        Thanks<br>
        %first_name% %last_name%<br>
        <br>
        <br>
        </FONT> 
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$friend_request_message .= $mail_footer_message;
$friend_request_message .= "</TABLE>";

$friend_response_subject = "%first_name% %last_name% has accepted your invitation to connect";
$friend_response_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Congratulations! <br>
        <br>
      %first_name% %last_name% has accepted your connection request!<br>
      <br>

      You
      have defined %first_name% as a %relation_type% kind of relationship.<br>
      <br>
      To view the %first_name% %last_name% 
      public blog page, click on the following link (or copy and paste the URL
      into your browser):<br>
        <A href=\"$page_url/user_blog.php?uid=%user_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%user_id%</A><br>
        <br>
        To view ALL of your friends and relations, click here  (or copy and paste
        the URL into your browser):<br>
        <A href=\"$page_url/view_all_members.php?view_type=in_relations&uid=%friend_id%\" style=\"color:#666666\">$page_url/view_all_members.php?view_type=in_relations&uid=%friend_id%</A><br>

      <br>
        Thank you<br>
        The $site_name Team <br>
        <br>
        <br>
        </FONT> 
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$friend_response_message .= $mail_footer_message;
$friend_response_message .= "</TABLE>";


$friend_denied_subject = "%first_name% %last_name% has denied your request to be your 'friend'";
$friend_denied_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Our
        condolences, </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"> <br>
        <br>
      We're sorry to have to be the bearer of bad news, but your request to befriend
      %first_name% %last_name% has been denied.
      <br>

      <br>
      Sorry again,<br>
        <br>
        The $site_name team <br>
        <br>
        <br>
        </FONT> 
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$friend_denied_message .= $mail_footer_message;
$friend_denied_message .= "</TABLE>";

$network_owner_bulletin_subject = "%subject%";
$network_owner_bulletin_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%subject%</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%owner_image%</A><br>
      %message%<br>
        <br>

    </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT> 
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$network_owner_bulletin_message .= $mail_footer_message;
$network_owner_bulletin_message .= "</TABLE>";

$group_created_subject = "%group_owner% has created a Group in your network %network_name%";
$group_created_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%group_owner%
      has created a group in your %network_name% network </FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url/user_blog.php?uid=%group_owner_id%\">%group_owner_image%</A>Dear
      %network_owner_name%,<br>
      <br>
      %group_owner%  has created the group %group_name% in your
       network, %network_name%
      <A href=\"%network_url%\" style=\"color:#666666\">%network_url%</A><br>

      <br>
      To view the %group_name% group go to <A href=\"$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%\" style=\"color:#666666\">%group_name%</A> <br>
      <br>
      To moderate the %group_name% group, go to: <br>
      <A href=\"$page_url" . PA_ROUTE_GROUP_MODERATION . "/gid=%group_id%\" style=\"color:#666666\">$page_url" . PA_ROUTE_GROUP_MODERATION . "/gid=%group_id%</A><br>
      <br>

      Regards,</FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">The
          $site_name team
      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
$group_created_message .= $mail_footer_message;
$group_created_message .= "</TABLE>";


// network join mail subject & message
$network_join_subject = "%joinee% has joined your network %network_name%";

$network_join_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%joinee%
      has joined your %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
      %network_owner_name%,<br>
      <br>
      %joinee% has joined your network, %network_name%:<br> 
      <A href=\"$page_url\" style=\"color:#666666\">$page_url</A><br>

        <br>
      You now have  %member_count% members in the %network_name% network.<br>
      <br>
      To view this new member's public blog page, go to:<br>
      <A href=\"$page_url/user_blog.php?uid=%joinee_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%joinee_id%</A><br>
      <br>
      To moderate this new member's account, go to the <A href=\"$page_url/moderate_users.php\" style=\"color:#666666\">Moderate
        Users Admin Screen.</A><br>

      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        Best regards,<br>
        <br>
      The $site_name team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
        </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$network_join_message .= $mail_footer_message;
$network_join_message .= "</TABLE>";

$media_uploaded_subject = "%first_name% has uploaded media into your network %network_name%";
$media_uploaded_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has uploaded media into your %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%user_image%</A>Dear
      %network_owner_name%,<br>
      <br>
      %first_name% has uploaded a media item into your network, %network_name%
      <A href=\"%network_url%\" style=\"color:#666666\">%network_url%</A><br>

      <br>
      <A href=\"$page_url/media_full_view.php?cid=%content_id%\" style=\"color:#666666\">%media_title%</A> <br>
      <br>
      To view or listen to %first_name%'s media item, go to:<br>
      <A href=\"$page_url/media_full_view.php?cid=%content_id%\" style=\"color:#666666\">$page_url/media_full_view.php?cid=%content_id%</A><br>
      <br>

      To moderate this %first_name%'s media, go to the <A href=\"$page_url/network_manage_content.php\" style=\"color:#666666\">Moderate
        Content Admin Screen.</A><br>
      <br>
      Regards,</FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">The
          $site_name team
      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
      </P>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$media_uploaded_message .= $mail_footer_message;
$media_uploaded_message .= "</TABLE>";

// content post to community blog mail subject and message

$content_posted_to_comm_blog_subject = "%first_name% has posted something to the %network_name% Community Blog";
$content_posted_to_comm_blog_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has posted something to the %network_name% Community Blog </FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%user_image%</A>Dear
      %network_owner_name%,<br>
      <br>
      %first_name% has posted a blog post (of some form) into
      Community blog of your network, 
      %network_name%
      <A href=\"%network_url%\" style=\"color:#666666\">%network_url%</A><br>

      <br>
      <A href=\"$page_url".PA_ROUTE_CONTENT."/cid=%content_id%\" style=\"color:#666666\">%content_title%</A> <br>
      <br>
      To view or listen to %first_name%'s full content post, go to:<br>
      <A href=\"$page_url/user_blog.php?uid=%user_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%user_id%</A><br>
      <br>

      To moderate this %first_name%'s content, go to the <A href=\"$page_url/network_manage_content.php\" style=\"color:#666666\">Moderate
        Content Admin Screen.</A><br>
      <br>
      Regards,</FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">The
          $site_name team
      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
      </P>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
  
$content_posted_to_comm_blog_message .= $mail_footer_message;
$content_posted_to_comm_blog_message .= "</TABLE>";


// content posted
$content_posted_subject = "%first_name% has posted content in your network %network_name%";
$content_posted_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has posted content in your %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%user_image%</A>Dear
      %network_owner_name%,<br>
      <br>
      %first_name% has posted some content into your network,
      %network_name%
      <A href=\"%network_url%\" style=\"color:#666666\">%network_url%</A><br>

      <br>
      <A href=\"$page_url".PA_ROUTE_CONTENT."/cid=%content_id%\" style=\"color:#666666\">%content_title%</A> <br>
      <br>
      To view or listen to %first_name%'s full content post, go to:<br>
      <A href=\"$page_url".PA_ROUTE_CONTENT."/cid=%content_id%\" style=\"color:#666666\">$page_url".PA_ROUTE_CONTENT."/cid=%content_id%</A><br>
      <br>

      To moderate this %first_name%'s content, go to the <A href=\"$page_url/network_manage_content.php\" style=\"color:#666666\">Moderate
        Content Admin Screen.</A><br>
      <br>
      Regards,</FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">The
          $site_name team
      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
      </P>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";

$content_posted_message .= $mail_footer_message;
$content_posted_message .= "</TABLE>";



// reciprocated relation established mail subject and message
$reciprocated_relation_estab_subject = "2 Members of your Network have established a reciprocated relationship.";
$reciprocated_relation_estab_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">2
      Members of your Network have established a reciprocated relationship </FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
      %network_owner_name%,<br>
      <br>
      %user_invitee% and %user_invited% have just establsihed a reciprocated
      relationship on your network, %network_name%
      <A href=\"%network_url%\" style=\"color:#666666\">%network_url%</A><br>

      <br>
      There are now %member_count% Members in your %network_name% network
      - of which %reci_relation_count% reciprocated relation's have been
      established.<br>
      <br>
      To moderate these members you can click here to go to the Network Operator
      screen to moderate members:<br>
      <A href=\"$page_url/manage_user.php\" style=\"color:#666666\">$page_url/manage_user.php</A><br>
      <br>
      Regards,</FONT></P>

      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">The
          $site_name team
      </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT> 
        </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$reciprocated_relation_estab_message .= $mail_footer_message;
$reciprocated_relation_estab_message .= "</TABLE>";

$relation_estab_subject = "%user_name% has established a relation with you";
$relation_estab_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%user_name% has established a relation with you</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" alt=\"People\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%user_image%</A>Dear
      %related_user%, <br>
        <br>
      %user_name% has established a relationship with you.<br>

      <br>
      There is nothing you can do to prevent this from happening, as this network
      has non-reciprocated relationships.<br>
      <br>
    </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">But
        you don't have to worry about anything, there is nothing they
        can do to you - except claim that they are your friend.<br>
        <br>
        By having a relationship established with Network Member they will be able
        to:</FONT>
      <TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">

        <TR>
          <TD><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">S</FONT><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\">hare
              any of their public media with you (if you want to) &mdash; Allow you
              to view any of their controlled access profile info, content or
            meta-data &mdash; Send the them a private message &mdash; Be invited into any
            of their Groups or Networks (which you can totally ignore).</FONT></TD>
        </TR>
      </TABLE>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>

        </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">To
          view this NetworkMember's public blog page, click on the following link
          (or copy and paste the URL into your browser):<br>
        <A href=\"$page_url/user_blog.php?uid=%user_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%user_id%</A><br>
        <br>
        To view ALL of your friends and relations, click here (or copy and paste
        the URL into your browser):<br>
          <A href=\"$page_url/view_all_members.php?view_type=in_relations&uid=%related_uid%\" style=\"color:#666666\">$page_url/view_all_members.php?view_type=in_relations&uid=%related_uid%</A><br>
        <br>
      Thank you<br>
        The $site_name Team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
$relation_estab_message .= $mail_footer_message;
$relation_estab_message .= "</TABLE>";
// invite group mail and message
$invite_group_subject = "%first_name% has invited you to join the %group_name% group in the %network_name% network";

$invite_group_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%group_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has invited you to join %group_name% group on the %network_name%
      network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%invitee_image%</A>Dear
        %invited_user_name%,<br>
      <br>      
      I have created my own group called %group_name% Group on the %network_name% network and  I'd like you to come and <STRONG>join us!</STRONG><br>
      <br>
      %group_name% 's Description
      <br>
      <br>
      %group_description%
      <br>
          <br>
          The %group_name% Group has it's own blog, forum, media gallery and
          we'll be able to see who else is a member of the Group.<br>
          <br>
          Once you have accepted this invitation, you will be able to: <br>
          </FONT>
      <TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">
        <TR>
          <TD><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"> Post
              blog posts into the %group_name% Group blog<br>

              Upload media into the %group_name% Group media gallery<br>
              Participate in any %group_name% Group forum threads<br>
          Invite others to come and join the %group_name% Group</FONT></TD>
        </TR>
      </TABLE>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">
        <br>
        If you want an overview of this group, click here: <br>
        <A href=\"$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%\" style=\"color:#666666\">
        $page_url" . PA_ROUTE_GROUP . "/gid=%group_id%
        </A><br>
        <br>
        If you are already a registered member of $site_name, click here
        to accept this invitation:<br>%accept_url%<br><br>
        If you are NOT a registered member of $site_name, then click
        here to register and accept this invitation:<br>%register_url%<br>
        <br>
        Here is the personalized message for you from %first_name% %last_name%
          <br>
          <br>
          %message%
          <br>
          <br>

      Thanks<br>
      %first_name%</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$invite_group_message .= $mail_footer_message;
$invite_group_message .= "</TABLE>";


$invite_accept_group_subject= "%first_name% has accepted your invitation to join the %group_name% Group";
$invite_accept_group_message ="
<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has accepted your invitation to join the %group_name% Group</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%invited_user_image%</A>Congratulations!<br>
        <br>
      %first_name% &nbsp; has accepted your invitation to join the %group_name% Group.<br>

      <br>
      To view the %group_name% Group, click on the following link (or copy
      and </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">paste
          the URL into your browser):<br>
        <A href=\"$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%\" style=\"color:#666666\">$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%<br>
        </A><br>
        There are now %group_member_count% members in the %group_name% &nbsp; Group.
        <br>
        <br>
        Thanks<br>
        The $site_name Team
        <br>
        <br>
        </FONT>      
        </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$invite_accept_group_message .= $mail_footer_message;
$invite_accept_group_message .= "</TABLE>";

//group join mail subject and message
$group_join_subject = "%group_joinee% has joined your Group %group_name% in the %network_name% network";

$group_join_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
      %group_owner_name%, <br>
        <br>
      %group_joinee% has joined your Group %group_name% in the %network_name% network.<br>
      <br>

    To view the %group_name% Group go to:<br>
    <A href=\"$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%\" style=\"color:#666666\">$page_url" . PA_ROUTE_GROUP . "/gid=%group_id%</A> <br>
    <br>
    To moderate the %group_name% Group, go to:<br>
    <A href=\"$page_url" . PA_ROUTE_GROUP_MODERATION . "/gid=%group_id%\" style=\"color:#666666\">$page_url" . PA_ROUTE_GROUP_MODERATION . "/gid=%group_id%</A></FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Very
        best regards,<br>

        <br>
      The $site_name team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT> </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
$group_join_message .= $mail_footer_message;  
$group_join_message .= "</TABLE>";

//Message to a newly created user 
$create_new_user_by_admin_subject = "Hi,\n\n%first_name% Welcome to %network_name%!";
$create_new_user_by_admin_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Hi %first_name% , <br><br> Welcome to %network_name% !<br><br><br>
Follow the link below to access your new account. You may change the password we've provided as soon as you login by going to Me and clicking the edit button on your About module for Edit Profile Settings.
<br><br>Your username:   %user_name% <br>
          password:   %password% <br><br>
Click  here %user_url% to start using %network_name% social network <br><br>
Or <br><br>Follow this %edit_url% to Edit your profile.<br>
      <br><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Very
        best regards,<br>

        <br>
      The $site_name team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT> </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
$create_new_user_by_admin_message .= $mail_footer_message;
$create_new_user_by_admin_message .= "</TABLE>";
// blink message waiting
$msg_waiting_subject = "%first_name_sender% has sent you a message in %network_name% network on %site_name%";
$msg_waiting_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
      %first_name_recipient%, <br>
        <br>
      %first_name_sender% has sent you a message in %network_name% network on %site_name%.<br>
      <br>

    To view the %first_name_sender% Public page go to:<br>
    <A href=\"$page_url/user_blog.php?uid=%sender_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%sender_id%</A> <br>
    <br>
    To view the message sent, go to:<br>
    <A href=\"$page_url" . PA_ROUTE_MYMESSAGE . "\" style=\"color:#666666\">$page_url" . PA_ROUTE_MYMESSAGE . "</A></FONT></P>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Very
        best regards,<br>

        <br>
      The $site_name team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT> </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>

  </TR>";
$msg_waiting_message .= $mail_footer_message;
$msg_waiting_message .= "</TABLE>";

$normal_relation_estab_subject = "Hi %network_owner%,A relation is added in your network %network_name%";
$normal_relation_estab_message = 
"<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\">
    </TD>
    <TD valign=\"top\">
      <P>
        <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
          %network_owner%, <br>
          A relation is added in your network %network_name%<br><br>
          <br>
        To view relationship establisher account go to: <br>
        <A href=\"$page_url/user_blog.php?uid=%user_id%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%user_id%</A> <br>
        <br>
        To view related user account go to: <br>
        <A href=\"$page_url/user_blog.php?uid=%related_uid%\" style=\"color:#666666\">$page_url/user_blog.php?uid=%related_uid%</A> <br>
        <br>
        </FONT>
      </P>
      <P>
        <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Very
        best regards,<br>
        <br>
        The $site_name team </FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT> 
      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\">
      </DIV>
      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never be shared with third parties unless specifically requested by you.
      </FONT>
    </TD>
  </TR>";
$normal_relation_estab_message .= $mail_footer_message;
$normal_relation_estab_message .= "</TABLE>";
// adding HTML subject and message, when a group member sends an invitation
$invite_group_by_member_subject = "%first_name% has invited you to join the %group_name% group in the %network_name% network";
$invite_group_by_member_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%group_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">%first_name%
      has invited you to join %group_name% group on the %network_name%
      network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><A href=\"$page_url". PA_ROUTE_USER_PUBLIC ."/%user_id%\">%invitee_image%</A>Dear
        %invited_user_name%,<br>
      <br>
      I am a member of group called %group_name% Group on the %network_name% network and  I'd like you to come and <STRONG>join us!</STRONG><br>
      <br>
      %group_name% 's Description
      <br>
      <br>
      %group_description%
      <br>
          <br>
          The %group_name% Group has it's own blog, forum, media gallery and
          we'll be able to see who else is a member of the Group.<br>
          <br>
          Once you have accepted this invitation, you will be able to: <br>
          </FONT>
      <TABLE width=\"100%\" border=\"0\" cellpadding=\"10\" bgcolor=\"#E7F6FF\">
        <TR>
          <TD><FONT color=\"#767676\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"> Post
              blog posts into the %group_name% Group blog<br>

              Upload media into the %group_name% Group media gallery<br>
              Participate in any %group_name% Group forum threads<br>
          Invite others to come and join the %group_name% Group</FONT></TD>
        </TR>
      </TABLE>
      <P><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">
        <br>
        If you want an overview of this group, click here: <br>
        <A href=\"$page_url" . PA_ROUTE_GROUP. "/gid=%group_id%\" style=\"color:#666666\">
        $page_url" . PA_ROUTE_GROUP . "/gid=%group_id%
        </A><br>
        <br>
        If you are already a registered member of $site_name, click here
        to accept this invitation:<br>%accept_url%<br><br>
        If you are NOT a registered member of $site_name, then click
        here to register and accept this invitation:<br>%register_url%<br>
        <br>
        Here is the personalized message for you from %first_name% %last_name%
          <br>
          <br>
          %message%
          <br>
          <br>

      Thanks<br>
      %first_name%</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$invite_group_by_member_message .= $mail_footer_message;
$invite_group_by_member_message .= "</TABLE>";

$report_abuse_subject = " $site_name %visitor_name% has reported an abuse about some content in your network %network_name%";
$report_abuse_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">$site_name %visitor_name% has reported an abuse about some content in you network %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
        %login_name%,<br>
      <br>
      %visitor_name% has reported an abuse about some content in your network %network_name%.<br>
      <br>
      %visitor_name% reported:<br> %message% 
      <br>
      Click Here %content_url% to view that content.
      <br>
      <br>
      Click here %delete_url% to delete that content.
      <br>
      <br>
      Thanks<br>
      The $site_name team.</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$report_abuse_message .= $mail_footer_message;
$report_abuse_message .= "</TABLE>";

// Report abuse message for group owner
$report_abuse_grp_owner_subject = " $site_name %visitor_name% has reported an abuse about some content in your group %group_name% in network %network_name%";
$report_abuse_grp_owner_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">$site_name %visitor_name% has reported an abuse about some content in your group %group_name% in network %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
        %login_name%,<br>
      <br>
      %visitor_name% has reported an abuse about some content in your group %group_name% in network %network_name%.<br>
      <br>
      %visitor_name% reported:<br> %message% 
      <br>
      Click Here %content_url% to view that content.
      <br>
      <br>
      Click here %delete_url% to delete that content.
      <br>
      <br>
      Click here %group_url% to have an overview of your group %group_name%
      <br>
      <br>
      Thanks<br>
      The $site_name team.</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$report_abuse_grp_owner_message .= $mail_footer_message;
$report_abuse_grp_owner_message .= "</TABLE>";

// .. Changed on 27 april 2007 .. for comments 

$report_abuse_on_comment_subject = " $site_name %visitor_name% has reported an abuse about some comment in your network %network_name%";
$report_abuse_on_comment_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">$site_name %visitor_name% has reported an abuse about some comment in you network %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
        %login_name%,<br>
      <br>
      %visitor_name% has reported an abuse about some comment in your network %network_name%.<br>
      <br>
      %visitor_name% reported:<br> %message% 
      <br>
      Click Here %content_url% to view that comment as well as content.
      <br>
      <br>
      Click here %delete_url% to delete that comment.
      <br>
      <br>
      Thanks<br>
      The $site_name team.</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$report_abuse_on_comment_message .= $mail_footer_message;
$report_abuse_on_comment_message .= "</TABLE>";


// Report abuse for comment message for group owner
$report_abuse_on_comment_grp_owner_subject = " $site_name %visitor_name% has reported an abuse about some comment in your group %group_name% in network %network_name%";
$report_abuse_on_comment_grp_owner_message = "<TABLE width=\"675\" border=\"0\" align=\"center\" cellpadding=\"8\">
  <TR>
    <TD colspan=\"2\">%network_icon_image%<br>
      <br>
      <STRONG><FONT face=\"Arial, Helvetica, sans-serif\" size=\"5\" color=\"#0099FF\">$site_name %visitor_name% has reported an abuse about some comment in your group %group_name% in network %network_name% network</FONT></STRONG>

      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>
    </TD>
  </TR>
  <TR>
    <TD valign=\"top\"><IMG src=\"$page_url/images/people2.gif\" width=\"150\" height=\"268\"></TD>
    <TD valign=\"top\"><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\">Dear
        %login_name%,<br>
      <br>
      %visitor_name% has reported an abuse about some comment in your group %group_name% in network %network_name%.<br>
      <br>
      %visitor_name% reported:<br> %message% 
      <br>
      Click Here %content_url% to view that comment as well as content.
      <br>
      <br>
      Click here %delete_url% to delete that comment.
      <br>
      <br>
      Click here %group_url% to have an overview of your group %group_name%
      <br>
      <br>
      Thanks<br>
      The $site_name team.</FONT><FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
        <br>
        <br>
        </FONT>      </P>
      <DIV style=\" width: auto; margin: 5px 0px 5px 0px; border-bottom:#FF9900 1px dashed\"></DIV>

      <FONT face=\"Arial, Helvetica, sans-serif\" size=\"2\" color=\"#767676\"><br>
          Everyone at $site_name respects your privacy. Your information will never
    be shared with third parties unless specifically requested by you.</FONT> </TD>
  </TR>";
$report_abuse_grp_owner_message .= $mail_footer_message;
$report_abuse_grp_owner_message .= "</TABLE>";

?>
