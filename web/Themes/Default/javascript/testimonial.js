
$(document).ready(
  function() {
     $('#new_testimonial').click(
       function() {
//         $(this).toggle("slow");
         attachTextarea(this);
       }
     );  

     $('.inplace_edit_testimonial').each(
      function() {
        $(this).hover(
           function() {
             $(this).addClass("inplace_active");
           }, 
           function() {
             $(this).removeClass("inplace_active");
           }
        );
      }
    );

  function storeData(ajax_data) {
    var ajaxURL = $("#submit_url").val();
    var ajax_data = '&module=ShowUserCommentModule&action=addUserComment&content='+ajax_data;
    $.ajax({
        type: "POST",
        url:  ajaxURL,
        data: ajax_data,
        dataType: 'json',
        success: function (data, status) {
          if(typeof(data.msg) == 'undefined') {
            alert("Unknown Ajax error");
            return false;
          }
          else if (data.msg != 'success') {
            alert("Ajax updater error: \r\n\r\n" + data.msg);
            return false;
          }
          else {
            var prev = $("#ShowUserCommentModule").parent();
            $("#ShowUserCommentModule").remove();
            $(prev).prepend(htmlspechrdecode(data.result));
          }
        },
        error: function (data, status, e) {
          alert("Ajax response: '" + e + "'");
          alert(data.result);
          return false;
        }
    });
  }  

  function attachTextarea(elem) {
     $(elem).hide();
     var elemID   = $(elem).attr('id');
     html_ins = '<div id="'+elemID+'_editor" style="padding: 12px">' + 
                   '<textarea id="'+elemID+'_edit" name="'+elemID+'" style="width:500px; height: 86px; border: 1px solid silver;"></textarea>' +
                   '<div style="text-align:left; padding: 8px">' + 
                      '<input id="'+elemID+'_save" type="button" value="Submit" /> or <input id="'+elemID+'_cancel" type="button" value="Cancel" />' +
                   '</div>' +
                '</div>';
     $(elem).after(html_ins);

     $('#'+elemID+'_edit').keypress(function (e) {
       if (e.which == 13 ) {
          var ajax_data  = escape($('#'+elemID+'_edit').val());
          storeData(ajax_data);
       } else if (e.which == 27) {
          cancelEdit(elem, elemID)
       }
     });
     
     $('#'+elemID+'_save').click(
        function() {
          var ajax_data  = escape($('#'+elemID+'_edit').val());
          storeData(ajax_data);
        }
     ); 

     $('#'+elemID+'_cancel').click(
        function() {
          cancelEdit(elem, elemID)
        }
     ); 
  }
    
  function cancelEdit(elem, elemID) {
    $(elem).show("slow");
    $('#'+elemID+'_editor').remove();
    $(elem).removeClass("inplace_active");

  }
    
    function htmlspechrdecode(str) {
      str = str.toString();
      str = str.replace(/&amp;/g, '&');
      str = str.replace(/&lt;/g, '<');
      str = str.replace(/&gt;/g, '>');
      str = str.replace(/&quot;/g, '"');
      str = str.replace(/'/g, '\'');
      return str;
    }
  }  
);
