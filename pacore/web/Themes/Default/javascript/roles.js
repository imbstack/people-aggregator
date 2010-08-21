var roles = {
  update_role_extra : function (uid) {
    var role_extra = $("input[@id^='role_extra_']").serialize();
    get_data = "uid="+uid+"&"+role_extra;
    $.ajax({
      type: "POST",
      url: "/ajax/update_role_extra.php",
      data: get_data,
      success: function(msg) {
        if(msg.length > 0) {
          if(msg.indexOf('Error') != -1) {
             var tmp = msg.split(',');
             var err = tmp[0]; // error message
             alert(err);
          } else {
             alert("Role data sucessfully updated");
          }
        }
        document.getElementById("assign_role").style.display ="none";
      }
    });
  },

  assingrole : function (uid, role_id, dest, gid) {
    if(role_id.length <= 0) {
      alert("Nothing selected.");
      return false;
    }
    var role_extra = $("input[id^='role_extra_']").serialize();
    get_data = "rid="+role_id+"&uid="+uid+"&"+role_extra;
    if (gid != '-1') get_data += '&gid='+gid;
    if(dest == 'associated_roles') {
      get_data += '&roles_action=add';
    } else {
      get_data += '&roles_action=delete';
    }
    $.ajax({
      type: "POST",
      url: "/ajax/assign_role.php",
      data: get_data,
      success: function(msg) {
        if(msg.length > 0) {
          if(msg.indexOf('Error') != -1) {
             var tmp = msg.split(',');
             var err = tmp[0]; // error message
             alert(err);
             msg = tmp[1];     // restore Role names
          } else {

             if(dest == 'associated_roles') {
               if (gid == '-1') {
                 alert("The new Role has been successfully assigned to selected user.\r\nPlease, click on newly assigned role for editing Role extra data.");
               } else {
                 alert("The new Role has been successfully assigned to selected user.\r\nFor more informations, click on newly assigned Role.");
               }
             } else {
               alert("Role has been successfully removed.");
               $("div[@id^='extra_info_']").css({ display: "none" });
             }

          }
        }
        var tid = "#curr_role"+uid;
        $(tid).html(msg);
      }
    });
  },

  double_list_move : function (user_id, src, dest, gid) {
    var srcobj = $("#"+src);
    var dstobj = $("#"+dest);
    var role_id = '';
    for (var i = 0; i < srcobj[0].options.length; i++)
    {
      if (srcobj[0].options[i].selected)
      {
        role_id = srcobj[0].options[i].value;
        dstobj[0].options[dstobj[0].length] = new Option(srcobj[0].options[i].text, srcobj[0].options[i].value);
        srcobj[0].options[i] = null;
        --i;
      }
    }
    thisObj = this;
    setTimeout(function() { thisObj.assingrole(user_id, role_id, dest, gid); }, 600);
    setTimeout(function() { thisObj.refresh_role_extra(user_id, gid); }, 1600);
  },

  showhide_roleblock : function (id, user_id, gid) {
    block_id = document.getElementById(id);
    if (trim(block_id.style.display)=='' || block_id.style.display=='none') {
      block_id.style.display = 'block';
      get_data = "uid="+user_id;
      if (gid != '-1') get_data += '&gid='+gid;
      $.ajax({
        type: "GET",
        url: "/ajax/user_roles.php",
        data: get_data,
        success: function(msg) {
          if(msg.length > 0) {
              modal_show("Assign Role", msg, 380, 780);

          } else {
            alert("No AJAX response from user_roles.php");
          }
        }
      });
    } else {
      block_id.style.display = 'none';
    }
  },


  show_role_extra: function (user_id, elm_id) {

    var selecttag = document.getElementById(elm_id);
    var role_id = selecttag[selecttag.selectedIndex].value;
      $("div[id^='extra_info_']").css({ display: "none" });
      $('#extra_info_'+role_id).css({ display: "block" });
  },

  refresh_role_extra: function (user_id, gid) {
      $('#roles_extra_info').html('<div style="padding-left: 28px"><img src="/Themes/Default/images/ajaxload.gif" /><br />Refreshing...</div>');
      get_data = "uid="+user_id;
      if (gid != '-1') get_data += '&gid='+gid;
      $.ajax({
        type: "GET",
        url: "/ajax/refresh_role_extra.php",
        data: get_data,
        success: function(msg) {
          if(msg.length > 0) {
            $('#roles_extra_info').html(msg);
          } else {
            alert("No AJAX response from refresh_role_extra");
          }
        }
      });
  }

}

function toggle_chhkbox(elm_id) {
  if($("#"+ elm_id).val() == '0') {
    $("#"+ elm_id).val('1');
  } else {
    $("#"+ elm_id).val('0');
  }
}
