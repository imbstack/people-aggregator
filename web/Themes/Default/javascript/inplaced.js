/*
 * inplaced.js - AJAX inplace edit script
 *
 * Copyright (c) 2009 Zoran Hron (BBM)
 *
 *    dependencies:  tiny_mce.js, jquery.js
 *
 *    usage:
 *      
 *      to make a HTML element in-place editable, it is necessary to add the following
 *      custom attributes to tag declaration:
 *
 *      - add class 'inplace_edit' within tag class attribute
 *      - add 'tinyMCE' attribute (possible values: 'false', 'minimal', 'basic', 'normal', 'rich')
 *      - add 'ajaxURL' attribute and place your AJAX server script URL there
 *        PHP server script should accept $_REQUEST['value'] parameter and return a json string
 *        with two params: "msg" and "result"
 *
 *
 *        Examples
 *        --------
 *
 *          input tag:
 *
 *             <div id="user_name" class="my_class inplace_edit" ajaxUrl="http://www.my_server.com/my_script.php" tinyMCE="basic">
 *               Click here to edit 
 *             </div>
 *
 *          server PHP code:
 *
 *             <?php echo json_encode(array("msg"=>"success", "result"=>"stored_value")); ?>
 *
 */
var pathToTinyExtra = '/Themes/Default/javascript/tiny_mce/examples';

if(typeof(jQuery) == 'undefined') {
   alert('inplaced.js requires JQuery javascritp librarry!');
}

$(document).ready(
  function() {
    $('.inplace_edit').each(
      function() {
/*      
        $(this).click(
           function() {
             var ed_inp = new EdInPlace(this);
             ed_inp.init();
           }
        );
*/        
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
    $('.inplace_edit').bind('click', function(e){
        attachEditor(this);
    });
  }

);

function EdInPlace(obj) {
  var self = this; 
  this.destElem = obj;
  this.init = function() {
     this.elemType   = this.destElem.tagName;
     this.tinyMode   = $(this.destElem).attr('tinyMCE');
     this.ajaxURL    = $(this.destElem).attr('ajaxUrl');
     this.minWidth   = $(this.destElem).attr('minWidth');
     this.minHeight  = $(this.destElem).attr('minHeight');
     this.elemID     = $(this.destElem).attr('id');
     this.elemWidth  = $(this.destElem).css("width");
     this.elemHeight = $(this.destElem).css("height");
     this.elemInitialValue = this.getElemValue(this.destElem);

     if(typeof(this.minWidth) != 'undefined') {
       this.elemWidth  = this.minWidth;
     }
     if(typeof(this.minHeight) != 'undefined') {
       this.elemHeight  = this.minHeight;
     }
     if(typeof(this.elemID) == 'undefined') {
        this.missingAttrMsg('id');
        return false; 
     }
     if(typeof(this.ajaxURL) == 'undefined') {
        this.missingAttrMsg('ajaxURL');
        return false; 
     }

     var html_ins;
     if(typeof(this.tinyMode) == 'undefined') {
       this.attachTextarea(); 
     } else if (this.tinyMode == 'false') {
       this.attachTextarea(); 
     } else {
       if(typeof(tinyMCE) == 'undefined') {
          alert("[inplaced.js]: Can't attach TinyMCE - TinyMCE not loaded!");
          return false;
       }
       this.attachTiny(); 
     }
  }


  this.attachTiny = function() {
     $(self.destElem).hide();
     var tiny_edit = new TinySettings();
     html_ins = '<div id="'+self.elemID+'_editor">' + 
                   '<textarea id="'+self.elemID+'_edit" name="'+self.elemID+'" style="width:'+self.elemWidth+';height:'+self.elemHeight+'">' + 
                       self.elemInitialValue + 
                   '</textarea>' +
                   '<div style="text-align:left; margin-top: 4px">' + 
                      '<input id="'+self.elemID+'_save" type="button" value="Save" /> or <input id="'+self.elemID+'_cancel" type="button" value="Cancel" />' +
                   '</div>' +
                '</div>';

     $(self.destElem).after(html_ins);
     tiny_edit.setupTiny(self.tinyMode, 'exact', self.elemID+ '_edit');

     $('#'+self.elemID+'_save').click(
        function() {
          var ajax_data  = "value=" + escape(tinyMCE.get(self.elemID+'_edit').getContent());
          tinyMCE.execCommand('mceRemoveControl', false, self.elemID+'_edit');
          self.storeData(ajax_data);
        }
     ); 

     $('#'+self.elemID+'_cancel').click(
        function() {
          tinyMCE.execCommand('mceRemoveControl', false, self.elemID+'_edit');
          self.cancelEdit();
        }
     ); 
  }


  this.attachTextarea = function() {
     $(self.destElem).hide();
     html_ins = '<div id="'+this.elemID+'_editor">' + 
                   '<textarea id="'+self.elemID+'_edit" name="'+self.elemID+'" style="width:'+self.elemWidth+';height:'+self.elemHeight+'">' + 
                       self.elemInitialValue + 
                   '</textarea>' +
                   '<div style="text-align:left;">' + 
                      '<input id="'+self.elemID+'_save" type="button" value="Save" /> or <input id="'+self.elemID+'_cancel" type="button" value="Cancel" />' +
                   '</div>' +
                '</div>';
     $(self.destElem).after(html_ins);

     $('#'+self.elemID+'_edit').keypress(function (e) {
       if (e.which == 13 ) {
          var ajax_data  = "value=" + escape($('#'+self.elemID+'_edit').val());
          self.storeData(ajax_data);
       } else if (e.which == 27) {
          self.cancelEdit();
       }
     });
     
     $('#'+self.elemID+'_save').click(
        function() {
          var ajax_data  = "value=" + escape($('#'+self.elemID+'_edit').val());
          self.storeData(ajax_data);
        }
     ); 

     $('#'+self.elemID+'_cancel').click(
        function() {
          self.cancelEdit();
        }
     ); 
  }

  this.missingAttrMsg = function(attr_str) {
    alert('[inplaced.js]\r\n\r\nInvalid ' + self.elemType +
          ' tag. Mandatory tag attribute [' + attr_str +
          '] undefined. Please, see an usage example bellow: \r\n\r\n' +
          '<input type="text" id="user_name" class="my_class inplace_edit" ajaxUrl="http://www.my_server.com/my_script.php" tinyMCE="basic" />');
    return false;
  }
  
  this.cancelEdit = function() {
      $(self.destElem).removeClass("inplace_active");
      $(self.destElem).show("slow");
      $('#'+self.elemID+'_editor').remove();
  }
  
  this.storeData = function(ajax_data) {
      var new_data = '';
      $(self.destElem).removeClass("inplace_active");
      self.setElemValue(self.destElem, 'Updating...');
      $(self.destElem).show("slow");
      $('#'+self.elemID+'_editor').remove();
//      alert("Url:"+ self.ajaxURL + ", data: " + ajax_data);
      $.ajax({
         type: "POST",
         url:  self.ajaxURL,
         data: ajax_data,
         dataType: 'json',
         success: function (data, status) {
           if(typeof(data.msg) == 'undefined') {
             alert("Unknown Ajax error");
             self.setElemValue(self.destElem, self.elemInitialValue);
             return false;
           }
           else if (data.msg != 'success') {
             alert("Ajax updater error: \r\n\r\n" + data.msg);
             self.setElemValue(self.destElem, self.elemInitialValue);
             return false;
           }
           else {
             new_data = self.stripSlashes(data.result);
             self.setElemValue(self.destElem, new_data);
           }
         },
         error: function (data, status, e)
         {
             alert("Ajax: '" + e + "'");
             self.setElemValue(self.destElem, self.elemInitialValue);
             return false;
        }
      });
  }

  this.getElemValue = function(elem) {
      switch (self.elemType) {
        case "INPUT":
        case "TEXTAREA":
          return $(elem).val().replace(/^\s+|\s+$/g,"");
        break
        default:
          return elem.innerHTML.replace(/^\s+|\s+$/g,"");      
      }
  }

  this.setElemValue = function(elem, e_value) {
      switch (self.elemType) {
        case "INPUT":
        case "TEXTAREA":
          $(elem).val(e_value);
        break
        default:
         $(elem).html(e_value.replace(/^\s+|\s+$/g,""));
      }
  }

  this.stripSlashes = function stripslashes(str) {
    str = str.replace(/\\'/g,'\'');
    str = str.replace(/\\"/g,'"');
    str = str.replace(/\\\\/g,'\\');
    str = str.replace(/\\0/g,'\0');
    return str;
  }
}


function attachEditor(target_obj) {
    var elem_id = $(target_obj).attr('id');  
    if(typeof(elem_id) == 'undefined') {
      alert("[inplaced.js] - Can't attach Editor. Mandatory tag attribute 'ID' missing!");
      return false; 
    }
    var editor_cont = document.getElementById(elem_id+"_editor");
    if(!editor_cont) {
      var ed_inp = new EdInPlace(target_obj);
      ed_inp.init();
      return true;
    }  
    return false;
}


function TinySettings() {

   this.setupTinyFull = function( tiny_mode, attach_to) { 
        tinyMCE.init({
                       // General options
                       'mode'         : tiny_mode,
                       'theme'        : 'advanced',
                       'skin'         : 'o2k7',
                       'skin_variant' : 'silver',
                       'elements'     : attach_to,
                       'extended_valid_elements' : 'div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang]',
                       
                       // Installed plugins
                       'plugins' : 'safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups',
                       
                       // Installed buttons
                       'theme_advanced_buttons1' : 'save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect',
                       'theme_advanced_buttons2' : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor',
                       'theme_advanced_buttons3' : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
                       'theme_advanced_buttons4' : 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,insertfile,insertimage',
                       
                       // TinyMCE options
                       'apply_source_formatting'            : true,
                       'inline_styles'                      : true,
                       'theme_advanced_resizing_use_cookie' : false,
                       'theme_advanced_path'                : false,
                       'theme_advanced_toolbar_location'   : 'top',
                       'theme_advanced_toolbar_align'      : 'left',
                       'theme_advanced_statusbar_location' : 'bottom',
                       'theme_advanced_resizing'           : true,
                       'theme_advanced_resize_horizontal'  : false,
                       'apply_source_formatting'           : false,
                       'relative_urls'                     : false,
                       'entity_encoding'                   : 'raw',
                       'forced_root_block'                 : false,
                       'force_p_newlines'                  : false,
                       'force_br_newlines'                 : true,
                       'debug'                             : false,

                       // Example content CSS (should be your site CSS)
                       'content_css' : pathToTinyExtra+'/css/content.css',
                       
                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' : pathToTinyExtra+'/lists/template_list.js',
                       'external_link_list_url'     : pathToTinyExtra+'/lists/link_list.js',
                       'external_image_list_url'    : pathToTinyExtra+'/lists/image_list.js',
                       'media_external_list_url'    : pathToTinyExtra+'/lists/media_list.js'
        });
   };
 
   this.setupTinyNormal = function( tiny_mode, attach_to) {
        tinyMCE.init({
                       // General options
                       'mode'         : tiny_mode,
                       'theme'        : 'advanced',
                       'skin'         : 'o2k7',
                       'skin_variant' : 'silver',
                       'elements'     : attach_to,
                       'extended_valid_elements' : 'div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang]',
                       
                       // Installed plugins
                       'plugins' : 'safari,preview,style,advimage,advlink,advhr,emotions,media,contextmenu,noneditable,visualchars',
                       
                       // Installed buttons
                       'theme_advanced_buttons1' : 'fontselect, fontsizeselect, justifyleft,justifycenter,justifyright,justifyfull,|,outdent,indent,|,link,unlink,anchor',
                       'theme_advanced_buttons2' : ' bold,italic,strikethrough,|,forecolor,backcolor,|,bullist,numlist,|,advhr,image,media,emotions,|,removeformat,preview,undo,redo,code',
                       'theme_advanced_buttons3' : '',
                       'theme_advanced_buttons4' : '',
                       
                       // TinyMCE options
                       'apply_source_formatting'            : true,
                       'inline_styles'                      : true,
                       'theme_advanced_resizing_use_cookie' : false,
                       'theme_advanced_path'                : false,
                       'theme_advanced_toolbar_location'    : 'top',
                       'theme_advanced_toolbar_align'       : 'left',
                       'theme_advanced_statusbar_location'  : 'bottom',
                       'theme_advanced_resizing'            : true,
                       'theme_advanced_resize_horizontal'   : false,
                       'apply_source_formatting'            : false,
                       'relative_urls'                      : false,
                       'entity_encoding'                    : 'raw',
                       'forced_root_block'                  : false,
                       'force_p_newlines'                   : false,
                       'force_br_newlines'                  : true,
                       'debug'                             : false,

                       // Example content CSS (should be your site CSS)
                       'content_css' : pathToTinyExtra+'/css/content.css',
                       
                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' : pathToTinyExtra+'/lists/template_list.js',
                       'external_link_list_url'     : pathToTinyExtra+'/lists/link_list.js',
                       'external_image_list_url'    : pathToTinyExtra+'/lists/image_list.js',
                       'media_external_list_url'    : pathToTinyExtra+'/lists/media_list.js'
        });
   };

   this.setupTinyBase = function( tiny_mode, attach_to) {
       tinyMCE.init({
                       // General options
                       'mode'         : tiny_mode,
                       'theme'        : 'advanced',
                       'skin'         : 'o2k7',
                       'skin_variant' : 'silver',
                       'elements'     : attach_to,
                       'extended_valid_elements' : 'div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang]',
                       
                       // Installed plugins
                       'plugins' : 'safari,advimage,advlink,media,contextmenu,noneditable,visualchars',
                       
                       // Installed buttons
                       'theme_advanced_buttons1' : 'outdent,indent,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,link,unlink,|,bold,italic,strikethrough,|,forecolor,|,image,media,|,undo',
                       'theme_advanced_buttons2' : '',
                       'theme_advanced_buttons3' : '',
                       'theme_advanced_buttons4' : '',
                       
                       // TinyMCE options
                       'apply_source_formatting'            : true,
                       'inline_styles'                      : true,
                       'theme_advanced_resizing_use_cookie' : false,
                       'theme_advanced_path'                : false,
                       'theme_advanced_toolbar_location'    : 'top',
                       'theme_advanced_toolbar_align'       : 'center',
                       'theme_advanced_statusbar_location'  : 'bottom',
                       'theme_advanced_resizing'            : true,
                       'theme_advanced_resize_horizontal'   : false,
                       'apply_source_formatting'            : false,
                       'relative_urls'                      : false,
                       'entity_encoding'                    : 'raw',
                       'forced_root_block'                  : false,
                       'force_p_newlines'                   : false,
                       'force_br_newlines'                  : true,
                       'debug'                             : false,

                       // Example content CSS (should be your site CSS)
                       'content_css' : pathToTinyExtra+'/css/content.css',
                       
                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' : pathToTinyExtra+'/lists/template_list.js',
                       'external_link_list_url'     : pathToTinyExtra+'/lists/link_list.js',
                       'external_image_list_url'    : pathToTinyExtra+'/lists/image_list.js',
                       'media_external_list_url'    : pathToTinyExtra+'/lists/media_list.js'
        });
   };

   this.setupTinyMinimal = function( tiny_mode, attach_to) {
       tinyMCE.init({
                       // General options
                       'mode'         : tiny_mode,
                       'theme'        : 'advanced',
                       'skin'         : 'o2k7',
                       'skin_variant' : 'silver',
                       'elements'     : attach_to,
                       'extended_valid_elements' : 'div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang]',
                       
                       // Installed plugins
                       'plugins' : 'safari,advlink',
                       
                       // Installed buttons
                       'theme_advanced_buttons1' : 'link,unlink,|,bold,italic,strikethrough,|,forecolor',
                       'theme_advanced_buttons2' : '',
                       'theme_advanced_buttons3' : '',
                       'theme_advanced_buttons4' : '',
                       
                       // TinyMCE options
                       'inline_styles'                      : false,
                       'theme_advanced_resizing_use_cookie' : false,
                       'theme_advanced_path'                : false,
                       'theme_advanced_toolbar_location'    : 'bottom',
                       'theme_advanced_toolbar_align'       : 'center',
                       'theme_advanced_statusbar_location'  : 'none',
                       'theme_advanced_resizing'            : false,
                       'theme_advanced_resize_horizontal'   : false,
                       'apply_source_formatting'            : false,
                       'relative_urls'                      : false,
                       'entity_encoding'                    : 'raw',
                       'forced_root_block'                  : false,
                       'force_p_newlines'                   : false,
                       'force_br_newlines'                  : true,
                       'debug'                              : false
        });
   };

   this.setupTiny = function(tiny_type, tiny_mode, attach_to) {
      switch (tiny_type) {
        case "rich":
          this.setupTinyFull(tiny_mode, attach_to);
        break
        case "basic":
          this.setupTinyBase(tiny_mode, attach_to);
        break
        case "normal":
          this.setupTinyNormal(tiny_mode, attach_to);
        break
        case "minimum":
          this.setupTinyMinimal(tiny_mode, attach_to);
        break
        default:
          this.setupTinyMinimal(tiny_mode, attach_to);
      }
   }
}
