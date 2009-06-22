var roles_edit = {
  editrole : function (id) {
    $("#role").html('<div style="padding-left: 28px"><img src="/Themes/Default/images/ajaxload.gif" /><br />Loading...</div>');
    get_data = "id="+id;
    $.ajax({
      type: "POST",
      url: "/configure/roles/action=getRole",
      data: get_data,
      success: function(msg) {
        $("#role").addClass('display_true');
        $("#role").html(msg);
      }
    });
  },

  showrole : function () {
    $.ajax({
      type: "POST",
      url: "/configure/roles/action=showRole",
      success: function(msg) {
        $("#role").addClass('display_true');
        $("#role").html(msg);
      }
    });
  },

  closeedit : function () {
    $("#role").removeClass('display_true')
    $("#role").addClass('display_false');
  },

  showdescription : function (msg) {
    $("#role_description").html(msg);
  },
  
  double_list_move : function (src, dest) {
    var srcobj = $("#"+src);
    var dstobj = $("#"+dest);
    for (var i = 0; i < srcobj[0].options.length; i++)
    {
      if (srcobj[0].options[i].selected)
      {
        dstobj[0].options[dstobj[0].length] = new Option(srcobj[0].options[i].text, srcobj[0].options[i].value);
        srcobj[0].options[i] = null;
        --i;
      }
    }
  },
  
  saverole : function (submit_action) {
    var _role_id   = $("#role_id").val();
    var _role_name = $("#role_name").val();
    var _role_desc = $("#desc").val();
    var _type = '';
    var _tasks = '';
    var tasks_obj = $("#associated_tasks");
    if($("#role_type_user").is(':checked')) {
      _type = 'user';
    } else if($("#role_type_network").is(':checked')) {
      _type = 'network';
    } else {
      _type = 'group';
    }
    var no_tasks  = tasks_obj[0].options.length;
    for (var i = 0; i < no_tasks; i++)
    {
        _tasks += tasks_obj[0].options[i].value;
        if(i < (no_tasks-1)) _tasks += ',';
    }

    if(_role_name.length <= 0) {
      alert('Please enter Role name.');
      return;
    }
    if(_role_desc.length <= 0) {
      alert('Please enter Role description.');
      return;
    }
    $.ajax({
      type: "POST",
      url: "/configure/roles/action=" + submit_action,
      data:  {role_id: _role_id, role_name: _role_name, role_desc: _role_desc, role_type: _type, tasks: _tasks},
      success: function(msg) {
        document.location.href = "/configure/roles/msg=" + msg;
      }
    });
  },

  delrole : function (id) {
    $.ajax({
      type: "POST",
      url: "/configure/roles/action=delRole",
      data:  {role_id: id},
      success: function(msg) {
        document.location.href = "/configure/roles/msg=" + msg;
      }
    });
  }

}
