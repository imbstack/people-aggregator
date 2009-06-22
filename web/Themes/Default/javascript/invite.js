// invite.js
//
// Zoran Hron

var fbconnect_onlogin; /* called by facebook connect on login: must be global */

$(document).ready(
  function() {

    jQuery.fn.slideFadeToggle = function(speed, easing, callback) {
       return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
    };

    $("img[@id^='get_contacts_']").click(                    // trigger import contacts
      function() {
        var service = this.getAttribute('id').replace(/get_contacts_/,'');
        var caption = service;
        var ajaxurl = "/ajax/" + service + "Contacts.php";    // naming example: "ajax/plaxoContacts.php"
        modal_show(
          "Contacting " + service,
          "<img style='margin-top:18px; margin-left:68px' src='"+CURRENT_THEME_PATH+"/images/ajaxload.gif' />",
          140, 180);
        $.post(
          ajaxurl,
          {'pUID' : $('#'+service+'_username').val(),
           'pPSW' : $('#'+service+'_password').val(),
           'authtype' : $('#authtype').val(),
           'action' : $('#action').val()},
          function(data) {
            modal_show("<img  src='"+CURRENT_THEME_PATH+"/images/"+service
                       +"_logo.gif' title='"+service+"' alt='"+service+"' />",
                       data, 360, 580);
          });
        return false;
      }
    );

    $("div[@id^='button_invite_']").hover(function() {
       $(this).removeClass("invite_out");
       $(this).addClass("invite_over");
    },function(){
       $(this).addClass("invite_out");
    });

    function show_xfbml(xfbml) {
      $("#facebook_logo").empty().append(xfbml);
      FB.XFBML.Host.parseDomTree();
    };

    /* global onlogin handler: declared at the top of invite.js */
    fbconnect_onlogin = function(user_id) {
      show_xfbml(INVITE_XFBML);
    };

    var xfbml_initialized = false;
    $("div[@id^='button_invite_']").click(
      function() {
        var service = $(this).attr('id').replace(/button_invite_/,'');
        $("div[@class^='signin_']").each(function() {
          elemname = $(this).attr('class').replace(/signin_/,'');
          if(elemname == service) {
            this.style.display = (this.style.display == "block") ? "none" : "block";
          } else {
            this.style.display = "none";
          }
        });
	if (service == 'facebook') {
          // Initialize XFBML - as long as the page has finished loading, so we have the Facebook library
          if (!xfbml_initialized) {
            xfbml_initialized = true;
            $(function() {
              FB_RequireFeatures(["XFBML"], function(){
                FB.Facebook.init(FACEBOOK_API_KEY, XD_RECEIVER_URL, {
                  ifUserConnected: fbconnect_onlogin,
                  ifUserNotConnected: function() {
                    show_xfbml('<div>Invite your Facebook Friends using Facebook Friend Connect.</div><div><fb:login-button length="long" autologoutlink="true" onlogin=""></fb:login-button></div>');
                  }
                });
                FB.XFBML.Host.autoParseDomTree = false;
              });
            });
          }
        }
      }
    );

    $("#select_all").click(
      function() {
        $("input[@id^='inv_selected_']").each(function() {
          this.checked = "checked";
        });
      }
    );

    $("#invite_show").click(function() {
      $("#invite_msg_container").slideFadeToggle('slow', function() {
        var $this = $(this);
        if ($this.is(':visible')) {
//        $this.text('Successfully opened.');
        } else {
//        $this.text('Sucessfully closed.');
        }
      });
    });

    $("img[@id^='linkedin_contacts']").click( function() {
        modal_show(
          "Loading LinkedIn CSV file... ",
          "<img style='margin-top:18px; margin-left:68px' src='"+CURRENT_THEME_PATH+"/images/ajaxload.gif' />",
          140, 180);
             $.ajaxFileUpload ({
                url:'/myAccount/contacts?type=contacts&stype=import&action=importLinkedInCSV',
                secureuri: false,
                fileElementId: 'linkedin_csv',
                dataType: 'json',
                success: function (data, status)
                {
                   var modal_header = "<img  src='"+CURRENT_THEME_PATH+"/images/LinkedIn_logo.gif' title='LinkedIn' alt='LinkedIn' />";
                   if(typeof(data.error) != 'undefined') {
                       if(data.error != '') {
                          modal_show(modal_header, data.error, 360, 580);
                       } else {
                          var html_content = html_entity_decode(Base64.decode(data.content));
                          modal_show(modal_header, html_content, 360, 580);
                       }
                   }
                },
                error: function (data, status, e)
                {
                    alert(e);
                }
            })
          return false;
        });

    $("img[@id^='outlook_contacts']").click( function() {
        modal_show(
          "Loading Outlook CSV file... ",
          "<img style='margin-top:18px; margin-left:68px' src='"+CURRENT_THEME_PATH+"/images/ajaxload.gif' />",
          140, 180);
             $.ajaxFileUpload ({
                url:'/myAccount/contacts?type=contacts&stype=import&action=importOutlookCSV',
                secureuri: false,
                fileElementId: 'outlook_csv',
                dataType: 'json',
                success: function (data, status)
                {
                   var modal_header = "<img  src='"+CURRENT_THEME_PATH+"/images/Outlook_logo.gif' title='Outlook' alt='Outlook' />";
                   if(typeof(data.error) != 'undefined') {
                       if(data.error != '') {
                          modal_show(modal_header, data.error, 360, 580);
                       } else {
                          var html_content = html_entity_decode(Base64.decode(data.content));
                          modal_show(modal_header, html_content, 360, 580);
                       }
                   }
                },
                error: function (data, status, e)
                {
                    alert(e);
                }
            })
          return false;
        });
  }
);

function html_entity_decode(str)
{
    try {
        var  tarea=document.createElement('textarea');
        tarea.innerHTML = str;
        return tarea.value;
        tarea.parentNode.removeChild(tarea);
    } catch(e) {
        document.getElementById("htmlconverter").innerHTML = '<textarea id="innerConverter">' + str + '</textarea>';
        var content = document.getElementById("innerConverter").value;
        document.getElementById("htmlconverter").innerHTML = "";
        return content;
    }
}
