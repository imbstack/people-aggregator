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

 class MessagesHandler {
     public static $msg_arr = array (
                     1050 => 'You cannot make a relationship with yourself.',
                     1051 => 'Your request has been sent for approval',

                     3000 => 'Zipcode should have a integral value.',
                     3001 => 'Sorry You can not join the group: <br />Reason:<br/>Invitation might be invalid<br/> Or you are using an old invitation',
                     5001 => ' User is not registered yet.',
                     5002 => ' Email successfully sent. ',
                     5003 => 'Congratulations! You have successfully registered.',
                     
                     // Email Related message start with 5050 upto 5999
                     5050 => 'Your request has been sent successfully',
                     5051 => 'Please fill mandatory fields',
                     5052 => 'Sorry - your supplied email address is from a domain not in the allowed list.',
                     
                     7001 => 'You have successfully joined the network.',
                     7002 => 'Please join the network to perform any activity.',
                     7003 => 'Your account has been temporarily disabled by the network administrator, so you can not perform the requested activity.',
                     7004 => 'Congratulations! <br /> Your password has been changed successfully.',
                     7005 => 'Please specify a user to delete.',
                     7006 => 'Network created successfully.',
                     7007 => 'Network bulletin has been sent.',
                     7008 => 'You have successfully left the network.',
                     7009 => 'Sorry! You are not authorized to view the content of this page.',
                     7010 => 'Module settings have been saved successfully.',
                     7011 => 'Email Notification has been saved.',
                     7012 => 'Relationship settings have been saved.',
                     7013 => 'Check your email to activate your account.',
                     7014 => 'Your account has been activated. Enjoy!',
                     7015 => 'Sorry! You cannot reuse a token.',
                     7016 => 'Invitation has been successfully accepted.',
                     7017 => 'Sorry! The invitation you are trying to accept is no longer valid.',
                     7018 => 'Sorry! The invitation you are trying to accept is not intended for you.',
                     7019 => 'Sorry! The token is not valid, the signature is incorrect.',
                     7026 => 'You can invite either internal or external users. Please provide either login names or emails',
                     7701 => 'Theme sucessfully changed - <br /> you can see it on the homepage.',
                     7702 => 'Sorry multiple selection is not allowed in this page.',
                     7703 => 'You are not authorized to change the theme.',
                     7704 => 'Network Settings have been changed successfully <br /> you can see it on the homepage.',
                     7020 => 'User(s) deleted successfully',
                     7021 => 'You are already a member of this network.',
                     7022 => 'User(s) approved.',
                     7023 => 'User(s) denied.',
                     7024 => 'Content deleted successfully.',
                     7025 => 'Comment has been deleted successfully.',
                     7027 => 'Content has been posted successfully.',
                     7028 => 'You are not authorized to post content, you are not a member of the group.',
		     7029 => 'Network has been deleted successfully.',
                     7032 => 'You can delete your account from the meta network only.',
                     7030 => 'To delete your account please tick the checkbox.',
                     7031 => 'Your account has been deleted sucessfully. <br/>You can create a new account with your email address...',
                     7033 => 'You are not authorized to delete this content.',

                     // Message Related to Group creation RANGE 90221 to 90240
                     90223 => 'Sorry you are not authorized to edit this group.',
                     90222 => 'Either group name is empty or it contains illegal characters.
                            Please  enter group name again.',
                     90221 => 'Group has been created successfully.',
                     90224 => 'Please select a category for the group',
                     90225 => 'Please select group type.',
                     90231 => 'Group has been updated successfully.',

                     // msg for ad-center module
                     //Range of Ad-center's related message is start with 19001-19020
                     19007 => 'Ad has been updated successfully',
                     19008 => 'Ad has been added successfully',
                     19009 => 'Please enter a valid URL for the link',
                     19010 => 'Ad has been successfully disabled',
                     19011 => 'Ad has been successfully enabled',
                     19012 => 'Please enter either URL or javascript code',
                     19013 => 'Ad has been deleted successfully',

                     //Messages for textpad module.
                     19014 => 'Textpad has been updated successfully',
                     19015 => 'Textpad has been added successfully',
                     19016 => 'Textpad has been successfully disabled',
                     19017 => 'Textpad has been successfully enabled',
                     19018 => 'Textpad has been deleted successfully',
                     19020 => 'Length of title should not be more than 30 characters.',

                     // messsage related to people invite
                     6001 => '<br />Please specify either email addresses or PeopleAggregator login name.',
                     6002 => '<br />You cannot invite yourself.',
                     6003 => 'Please specify email address - it can\'t be blank.',
                     6004 => 'Invitation has been sent successfully',
                     6005 => 'Please join atleast 1 group before sending invitation',
                     // message related to internal messaging

                     //Messages for the My Message section.
                     8001 => '<br /> Message sent successfully.',
                     'message_sent' => 'Your message has been successfully sent.',
                     /* TODO: Automatically alter this message when MAX_MESSAGE_LENGTH is changed from
                        api_constants.php
                     */
                     8002=> 'Message cannot be greater than 15000 characters (approx. 3000 words).',
                     8003=> 'Please enter at least one addressee in the <b>To</b> field.',

                     //Messages for the Media Gallery Section.
                     2001=>'Image uploaded',
                     2002=>'Audio uploaded',
                     2003=>'Video uploaded',
                     2004=>'Image deleted',
                     2005=>'Audio deleted',
                     2006=>'Video deleted',
                     2007=> '%media% updated successfully',
                     // Message for Customize User UI
                     2008=>'User profile updated successfully',

                     // Messages for reporting abuse
                     9002 => 'A mail regarding this content has been sent to the Network Moderator & Group Moderator',
                     9003 => 'A mail regarding this content has been sent to the Network Moderator',
                     9004 => 'Your report could not be sent, as you did not enter a message',
                     9005 => 'Sorry you are not a member of this invite-only group.<br /> To join, you need a group invitation.',
                     9006 => 'Group Settings have been changed successfully',
                     9007 => 'Role has been created successfully.' ,
                     9009 => 'Role has been updated successfully.',
                     // Message related to the Testimonials
                     // Rage 9010 to 9020
                     9010 => 'You are not authorized to perform this operation',
                     9011 => 'Testimonial has been approved successfully',
                     9012 => 'Testimonial has been denied',
                     9013 => 'Testimonial has been sent successfully',
                     9014 => 'Testimonial has been deleted successfully',
                     9015 => 'Task has  been  successfully assigned. ',
                     // Message Related to Comment posting ..9021 to 9031
                     9021 => 'Sorry, your comment cannot be posted as it looks like spam.  Try removing any links to possibly suspect sites, and re-submitting.',
                     9022 => 'Your comment has been posted successfully',
                     9023 => 'Sorry, your comment cannot be posted as it was classified as spam by Akismet, or contained links to blacklisted sites.  Please check the links in your post, and that your name and e-mail address are correct.',
                     9024 => 'Comment can\'t be blank',
                     9025 =>'Your blog settings have been saved.',
                     // messages related to content moderation are 1001
                     1001 => 'Sorry this content is not available for view. ',
                     1002 => 'Content(s) has been approved.',
                     1003 => 'Content(s) has been denied.',
                     1004 => 'Content has been sent for approval.',
                     1005 => 'Media has been sent for approval.',
                     1006 => 'Please select either approve or deny from select box.',
                     1007 => 'Please select atleast one content to approve or deny.',
                     // messages related to footer links configuration.
                     11007 => 'Footer link has been updated successfully.',
                     11008 => 'Footer link has been added successfully.',
                     11010 => 'Footer link has been successfully disabled.',
                     11011 => 'Footer link has been successfully enabled.',
                     11013 => 'Footer link has been deleted successfully.',
                      // messages related to static pages.
                     12007 => 'Page has been updated successfully.',
                     12008 => 'Page has been added successfully.',
                     12009 => 'Sorry!! the page you are trying to access is not available.',
                     12013 => 'Page has been deleted successfully.',
                      //messages related to configurable email
                     13001 => 'Email has been updated succesfully',
                     13002 => 'Email has been restored with default data succesfully',
                     13003 => 'You are not a member of this group, Please join this group to upload media',
                     // message related to configuration of category.
                     14001 => 'Category has been created succesfully',
                     14002 => 'Category has been updated succesfully',
                     14003 => 'Category has been deleted succesfully',

                     // Messages related to files operation, messages start with 5040 upto 5049
                     5040 => 'Unable to open file',
                     5041 => 'Unable to save the data',
                     5042 => 'Permitted domain name list has been successfully updated.',
		                 5043 => 'Profanity word list has been successfully updated.',

                     //Message for super group related features
                     15000 =>'Ecommerce widget has been saved successfully',
                     15001 =>'Ecommerce widget has been deleted successfully',
                     15002 =>'Ecommerce widget has been updated successfully',
                     15003 =>'User has been added successfully added to the list of showcased users',
                     15004 =>'User already exists in the list of showcased users',
                     15005 =>'User has been removed successfully from the list of showcased users',
                     15006 =>'User you are trying to remove is not in the list of showcased users',
                     15007 =>'Playlist has been saved successfully',
                     15008 =>'Playlist has been deleted successfully',
                     15009 =>'Playlist has been updated successfully',
                     15010 =>'List has been saved successfully',
                     15011 =>'List has been deleted successfully',
                     15012 =>'List has been updated successfully',
                     //message for celebrity
                     16001 =>'No such celebrity exist',
                     16002 =>'Celebrity group created successfully',
                     16003 =>'Ad edited successfully',
                     16004 =>'Headline edited successfully',
                     16005 =>'Celebrity image uploaded successfully',
                     16006 =>'Song edited successfully',
                     16007 =>'Album edited successfully',
                     16008 =>'Celebrity edited successfully',
                     16009 =>'Celebrity added successfully',
                     16010 =>'You are not authorized to edit this image',
                     16011 =>'Celebrity image edited successfully',
                     16012 =>'Celebrity comment deleted successfully',
                     16013 =>'No such id exist',
                     16015=>'You are not authorized to delete this item',
                     16016 =>'Celebrity video uploaded successfully',
                     16017 =>'Celebrity video edited successfully',
                     16018 =>'Comment edited successfully',
                     16019 =>'Picture removed successfully.',
                     16020 =>'Please select theme'
                  );

     /* This function is made for handling Dynamic msg */
     /* We can defind static message here and find the string between %-% and replace with dyanmic Messages */
     public static function get_message($msg_id,$dynamic_error_msg=null) {
       $msg = MessagesHandler::$msg_arr[$msg_id];
       if (!empty($dynamic_error_msg)) {
         $msg = preg_replace("/^%[a-z]*%/",$dynamic_error_msg,$msg);
       }
       return $msg;
     }
 }
?>