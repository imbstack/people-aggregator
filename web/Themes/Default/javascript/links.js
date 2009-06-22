function edit_list() {
  var category_id = '';
  $("input[@type=radio]").each(
    function () {
      if (this.checked == true) {
        category_id =  this.value;
      }
    }
  );
  var ajax_url = 'ajax_links.php?action=edit_list&category_id='+category_id;
  $('#edit_list').html('<div class="required">loading ...</div>').load(ajax_url); 
}

var list = {
  selected_list : function () {
      var category_id = '';
      $("input[@type=radio]").each(
        function () {
          if (this.checked == true) {
            category_id =  this.value;
          }
        }
      );
      return category_id;
  },
  remove : function () {
    if (confirm('All the links under the list will be deleted. Are you sure to delete this list?')) {
      post_data = 'category_id='+list.selected_list()+'&form_action=remove_list';      
      $.ajax({
        type: "POST",
        url: "ajax_links.php",
        data: post_data,
        success: function(msg) {          
          var json_data = eval('(' + msg + ')');
          if (json_data.is_error) {
            $('#error_message').html(json_data.errors);
          } else {
            $('#message_form').attr('action', 'links_management.php');
            $('#messages').attr('value', json_data.errors);
            document.forms['message_form'].submit();
          }
        }
      });
    }
  },
  create : function (is_submit) {
    
    if (is_submit) {
      if (trim($('#category_name').val()) == '') {
        //alert('Please enter a name for the new list');
        $('#error_message').html('Please enter a name for the new list');
        return false;
      }
      $('#form_action').attr('value', 'create_list');
      var post_data = $("input[@type=text]").serialize();
      post_data += '&'+$("input[@type=hidden]").serialize();
      $.ajax({
        type: "POST",
        url: "ajax_links.php",
        data: post_data,
        success: function(msg) {
          var json_data = eval('(' + msg + ')');
          if (json_data.is_error) {
            $('#error_message').html(json_data.errors);
          } else {
            $('#message_form').attr('action', 'links_management.php');
            $('#messages').attr('value', json_data.errors);
            $('#updated_category_id').attr('value', json_data.updated_category_id);
            document.forms['message_form'].submit();
          }          
        }
      });
      
    } else {
      var obj = document.getElementById("new_link_category");
      if (obj.className == "display_false") {
        obj.className = "display_true";
      } else if (obj.className == "display_true") {
        obj.className = "display_false";
      }
    }
  },
  update : function (is_submit) {
    if (is_submit) {
      if (trim($('#updated_category_name').val()) == '') {
        //alert('Please enter a name for the new list');
        $('#error_message').html('List name can not be empty');
        return false;
      }
      $('#form_action').attr('value', 'edit_list');
      var post_data = $("input[@type=text]").serialize();
      post_data += '&'+$("input[@type=hidden]").serialize();      
      $.ajax({
        type: "POST",
        url: "ajax_links.php",
        data: post_data,
        success: function(msg) {          
          var json_data = eval('(' + msg + ')');
          if (json_data.is_error) {
            $('#error_message').html(json_data.errors);
          } else {
            $('#message_form').attr('action', 'links_management.php');
            $('#messages').attr('value', json_data.errors);
            $('#updated_category_id').attr('value', json_data.updated_category_id);
            document.forms['message_form'].submit();
          }
        }
      });
      
    } else {
      var obj = document.getElementById("new_link_category");
      if (obj.className == "display_false") {
        obj.className = "display_true";
      } else if (obj.className == "display_true") {
        obj.className = "display_false";
      }
    }
  },
  highlight : function (cat_id) {
    $("input[@type='radio']").each(
      function () {
        if (this.value == cat_id) {
          this.checked = true;
        }
      }
    );
  }
}


var list_links = {
  checked_boxes : function () {
    var list_link_ids = new Array();
    $("input[@type=checkbox]").each(
      function () {
        if (this.checked == true) {
          list_link_ids.push(this.value);
        }
      }
    );
    return list_link_ids;
  }, 
  edit : function () {
    list_link_ids = this.checked_boxes();    
    if (list_link_ids.length == 0) {
      alert('Please select atleast one list link');
    }
    //var post_data = '';
    var url = 'ajax_links.php';
    $('#formLinkManagement').attr('action', url);
    var ajax_url = 'ajax_links.php?action=edit_list_link&link_ids='+list_link_ids.join("_");
    $('#edit_list_links').html('<div class="required">loading ...</div>').load(ajax_url);
  },
  remove : function () {
    list_link_ids = this.checked_boxes();    
    if (list_link_ids.length == 0) {
      alert('Please select atleast one list link');
      return false;
    }
  },
  create : function (is_submit) {
    if (is_submit) {
      if (trim($('#title').val()) == '') {
        $('#error_message_links').html('Please enter a caption for the link.');
        return false;
      }
      if (trim($('#url').val()) == '') {
        $('#error_message_links').html('Please enter URL for the link.');
        return false;
      }
      var post_data = $('#title').serialize();
      post_data += '&'+$('#url').serialize();      
      post_data += '&form_action=create_link&category_id='+list.selected_list();
      
      $.ajax({
      type: "POST",
      url: "ajax_links.php",
      data: post_data,
      success: function(msg) {
        var json_data = eval('(' + msg + ')');        
        if (json_data.is_error) {
          $('#error_message_links').html(json_data.errors);
        } else {
          $('#message_form').attr('action', 'links_management.php');
          $('#messages').attr('value', json_data.errors);
          $('#updated_category_id').attr('value', json_data.updated_category_id);
          document.forms['message_form'].submit();
        }
      }
    });
      
    } else {
      var obj = document.getElementById("new_link_in_list");    
      if (obj.className == "display_false") {
        obj.className = "display_true";
      } else if (obj.className == "display_true") {
        obj.className = "display_false";
      }
    }
  },
  edit_action : function () {
    $('#form_action').attr('value', 'update_links');
    var error_mesg = '', counter = 1;
    $('input[@name^="title_updated"]').each(
      function () {
        if (trim(this.value) == '') {
          error_mesg += 'Link caption can not have empty value for link '+counter+'<br />';
          is_error = true;
          this.focus();
        }
        counter++;
      }
    );
    
    counter = 1;
    $('input[@name^="url_updated"]').each(
      function () {
        if (trim(this.value) == '') {
          error_mesg += 'URL can not be empty for link '+counter+'<br />';
          is_error = true;
          this.focus();
        }
        counter++;
      }
    );
    if (trim(error_mesg) != '') {
      $('#error_message_links').html(error_mesg);
      return false;
    }
    
    var post_data = $('input[@name^="title_updated"]').serialize();
    post_data += '&'+$('input[@name^="url_updated"]').serialize();
    post_data += '&'+$('input[@name^="link_id_updated"]').serialize();
    post_data += '&category_id='+list.selected_list();
    post_data += '&form_action=update_links';        
    
    $.ajax({
      type: "POST",
      url: "ajax_links.php",
      data: post_data,
      success: function(msg) {        
        var json_data = eval('(' + msg + ')');
        if (json_data.is_error) {
          $('#error_message_links').html(json_data.errors);
        } else {          
          $('#message_form').attr('action', 'links_management.php');
          $('#messages').attr('value', json_data.errors);
          $('#updated_category_id').attr('value', json_data.updated_category_id);
          document.forms['message_form'].submit();
        }
      }
    });
  },
  remove : function () {
    list_link_ids = this.checked_boxes();    
    if (list_link_ids.length == 0) {      
      $('#error_message_links').html('Please select atleast one for deletion.');
      return false;
    }
    
    var post_data = 'link_id='+this.checked_boxes();
    post_data += '&form_action=remove_links';
    post_data += '&category_id='+list.selected_list();
    $.ajax({
      type: "POST",
      url: "ajax_links.php",
      data: post_data,
      success: function(msg) {
        var json_data = eval('(' + msg + ')');
        if (json_data.is_error) {
          $('#error_message_links').html(json_data.errors);
        } else {          
          $('#message_form').attr('action', 'links_management.php');
          $('#messages').attr('value', json_data.errors);
          $('#updated_category_id').attr('value', json_data.updated_category_id);
          document.forms['message_form'].submit();
        }
      }
    });
  }
};

$(document).ready(
  function () {
    if (trim(cat_id) != '') {
      list.highlight(cat_id);
      ajax_category_links('manage_data', cat_id);//defined in base_javascript
    }
  }
);