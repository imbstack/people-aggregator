/**
 * @script user_register.js
 *
 * @brief: Validation and user avatar AJAX upload
 *
 * NOTE: This script require JQuery and ajaxfileupload.js plugin
 *
 * @author     Zoran Hron, 2008-11-14 <zhron@broadbandmechanics.com>
 *
 */

$(document).ready(function() {


    var validate_1 =  $("#register_user_form").validate({
                rules: {
                        first_name: {
                                required: true,
                                minLength: 2
                        },
                        login_name: {
                                required: true,
                                minLength: 3
                        },
                        password: {
                                required: true,
                                minLength: 5
                        },
                        confirm_password: {
                                required: true,
                                minLength: 5,
                                equalTo: "#password"
                        },
                        email: {
                                required: true,
                                email: true
                        },
                        userfile: {
                              accept:'gif|jpg|bmp|png'
                        },
                        avatar_url: {
                              url:true
                        },
                        chkbox_agree: "required"
                },
                messages: {
                        first_name: { 
                                required: "Please enter your firstname"
                        },
                        login_name: {
                                required: "Please enter a username",
                                minLength: "Your username must consist of at least 2 characters"
                        },
                        password: {
                                required: "Please provide a password",
                                minLength: "Your password must be at least 5 characters long"
                        },
                        confirm_password: {
                                required: "Please enter confirmation password",
                                minLength: "Your password must be at least 5 characters long",
                                equalTo: "Please enter the same password as above"
                        },
                        email: "Please enter a valid email address",
                        userfile: "Please select a valid image file. (gif, jpg, bmp, png)",
                        avatar_url: "Please enter a valid image URL.",
                        chkbox_agree: "Please accept our policy"
                },
                errorLabelContainer: $("#validation_error")
        });
/*
        var validate_2 =  $("#register_user_form").validate({

                rules: {
                        battalion: {
                                required: true,
                                minLength: 1
                        }
                },
                messages: {
                        battalion: "Please select your Battalion"
                },

        errorLabelContainer: $("#validation_error")
        });

        var validate_3 =  $("#register_user_form").validate({

                rules: {
                        member_status: {
                                required: true,
                                minLength: 1
                        },
                        userfile: {
                              accept:'gif|jpg'
                        },
                        avatar_url: {
                              url:true
                        },
                        chkbox_agree: "required"
                },
                messages: {
                        member_status: "Please select your Member Status",
                        userfile: "Please select a valid image file.",
                        avatar_url: "Please enter a valid image URL.",
                        chkbox_agree: "Please accept our policy"
                },
        errorLabelContainer: $("#validation_error2")
        });

        $('.member_status').change( function () {
             if( this.value == 'Alumni') {
                 $('#alumniextra').show('slow');
             } else {
                $('#alumniextra').hide('slow');
             }
        });

        $('#next_step1').click( function () {
           if(validate_1.form()) {
             $('#registration_step1').removeClass('current');
             $('#registration_step2').addClass('current');
             $("#current_step").val("#next_step1");
             return false;
           }
        });

        $('#next_step2').click( function () {
           if(validate_2.form()) {
             $('#registration_step2').removeClass('current');
             $('#registration_step3').addClass('current');
             $("#current_step").val("#next_step2");
             return false;
           }
        });

        $('#prev_step2').click( function () {
             $('#registration_step2').removeClass('current');
             $('#registration_step1').addClass('current');
             $("#current_step").val("#prev_step2");
             return false;
        });

        $('#prev_step3').click( function () {
             $('#registration_step3').removeClass('current');
             $('#registration_step2').addClass('current');
             $("#current_step").val("prev_step3");
             return false;
        });
*/

        $("#state_1").change( function() { 
            if(this.value == -1) {
                $("#stateOther").css({ display: "inline" });
            } else {
                $("#stateOther").val('');
                $("#stateOther").css({ display: "none" });
            }
        });
        
        $('#joinbutton').click( function () {
           if(validate_1.form()) {
             document.getElementById("register_user_form").submit();
             return true;
           }
           return false;
        });

        $('#clear_image').click( function() {
         $('#userfile').remove();
         $('#userfile_wrapper').html('<input type="file" class="text longer" id="userfile" name="userfile"/>');
         $('#your_photo').remove();
         return false;
        });
        
        $('#prev_image').click( function () {
           var image_url = $('#avatar_url').val();
           $('#loading_preview').html('<div style="width:100%; text-align: center"><img src="/Themes/Default/images/ajaxload.gif" /><br />Uploading file...</div>');
           if(image_url.length > 0) {    // image URL entered

             img_data = "image_url=" + image_url;
             $.ajax({
                type: "POST",
                url:'/ajax/preview_image.php',
                data: img_data,
                dataType: 'json',
                success: function (data, status) {
                   if(typeof(data.error) != 'undefined') {
                       if(data.error != '') {
                          $('#loading_preview').html(' ');
                          alert(data.error);
                       } else {
                          var content = '<div class="field_medium" style="height: auto">\n' +
                                        '<h4><label>Your photo:<span class="required"> &nbsp; </span></label></h4>\n' +
                                        '<input type="hidden" name="user_filename" value="' + data.image_file + '" />\n' +
                                        '<div>' + html_entity_decode(data.image) + '</div>\n' +
                                        '<div><b>To use a different image, click Clear Image and select a new image.</b></div>\n' +
                                        '</div>\n';

                          $("#image_preview").html(content);
                       }
                   }
                },
                error: function (data, status, e)
                {
                    alert(e);
                }
             })

           } else {                      // do AJAX upload

             $.ajaxFileUpload ({
                url:'/ajax/preview_image.php',
                secureuri: false,
                fileElementId: 'userfile',
                dataType: 'json',
                success: function (data, status)
                {
                   if(typeof(data.error) != 'undefined') {
                       if(data.error != '') {
                          $('#loading_preview').html(' ');
                          alert(data.error);
                       } else {
                          var content = '<div class="field_medium" style="height: auto">\n' +
                                        '<h4><label>Your photo:<span class="required"> &nbsp; </span></label></h4>\n' +
                                        '<input type="hidden" name="user_filename" value="' + data.image_file + '" />\n' +
                                        '<div>' + html_entity_decode(data.image) + '</div>\n' +
                                        '<div><b>To use a different image, click Clear Image and select a new image.</b></div>\n' +
                                        '</div>\n';

                          $("#image_preview").html(content);
                       }
                   }
                },
                error: function (data, status, e)
                {
                    alert(e);
                }
            })
          }
          return false;
        });

});

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
