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


    var validate_1 =  $("#edit_points_form").validate({
                rules: {
                        'form_data[title]': {
                                required: true,
                                minLength: 2
                        },
                        'form_data[description]': {
                                minLength: 3
                        },
                        'form_data[place]': {
                                minLength: 2
                        },
                        'form_data[category]': {
                                required: true
                        },
                        'form_data[rating]': {
                                required: true
                        }
                },
                messages: {
                        'form_data[title]': {
                                required: "Title is a required field",
                                minLength: "Title must be at least 2 characters"
                        },
                        'form_data[description]': {
                                minLength: "Description must be at least 3 characters"
                        },
                        'form_data[place]': {
                                minLength: "Place must be at least 2 characters"
                        },
                        'form_data[category]': {
                                required: "Category is a required field"
                        },
                        'form_data[rating]': {
                                required: "Rating points is a required field",
                        }
                }
    });


    $('#switch_categ').click( function() {
       $('#edit_category').html('<input type="text" class="text short" name="form_data[category]" id="form_data_category" value="" />');
       return true;
    });

    $('#submit_form').click( function() {
       if(validate_1.form() && ($('#form_data_category').val() != '')) {
         return true;
       }
       if($('#form_data_category').val() == '') {
          alert("Category is a required field");
       }
       return false;
    });


});


function showRequest(formData, jqForm, options) {
    $(':input[@name=submit]', jqForm).remove();
    return true;
}
function showResponse(responseText, statusText)  {
    modal_hide();
    $('#modal_window').html('');
    $('#attach_media').html(responseText).show('slow');
}

function video_failure(msg) {
    alert(msg);
    modal_hide();
    $('#attach_media').html('<b>'+msg+'</b>').show('slow');
}

function show_upload(typeStr) {
    var now = new Date().getTime();
    var options = {
        target: '#attach_media',
        beforeSubmit: showRequest,
        success: showResponse,
        url: '/ajax/attach_media.php?type='+typeStr+'&uid='+ $('#uid').val()
    };

    $.post(
        '/ajax/upload_media_form.php?t='+now,
        {'type': typeStr
        },
            function(data) {
                modal_show('Attach '+typeStr, data);
                $('#modal_window form').submit(function() {
                    $(this).ajaxSubmit(options);
                    return false;
                });
            });
    return false;
}