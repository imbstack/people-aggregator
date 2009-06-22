<?php

/**
* @Class TinyMCE : setup and generate TinyMCE JS code
*
*
*
* Created by:
* Zoran Hron <zhron@broadbandmechanics.com>
*
**/

class TinyMCE {

  const full    = 'full';
  const medium  = 'medium';
  const base    = 'base';
  const minimal = 'minimal';

  public $type;
  public $types = array (
      'full' => array(
                       // General options
                       'mode'         => "\"textareas\"",
                       'theme'        => "\"advanced\"",
                       'skin'         => "\"o2k7\"",
                       'skin_variant' => "\"silver\"",
                       'elements'     => "\"elm1,elm2\"",
                       'extended_valid_elements' => "\"b,u,font[color],a[href|title|alt],ul[class|style],ol[class|style],li[class|style],div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang|style]\"",

                       // Installed plugins
                       'plugins' => "\"safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,imagemanager,filemanager\"",

                       // Installed buttons
                       'theme_advanced_buttons1' => "\"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\"",
                       'theme_advanced_buttons2' => "\"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\"",
                       'theme_advanced_buttons3' => "\"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\"",
                       'theme_advanced_buttons4' => "\"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,insertfile,insertimage\"",

                       // TinyMCE options
                       'theme_advanced_toolbar_location'   => "\"top\"",
                       'theme_advanced_toolbar_align'      => "\"left\"",
                       'theme_advanced_statusbar_location' => "\"bottom\"",
                       'theme_advanced_resizing'           => 'true',
                       'theme_advanced_resize_horizontal'  => 'false',
                       'apply_source_formatting'           => 'false',
                       'relative_urls'                     => 'false',
                       'entity_encoding'                   =>  "\"raw\"",
                       'forced_root_block'                 => 'false',
                       'force_p_newlines'                  => 'false',
                       'force_br_newlines'                 =>'true',
                       'debug'                             => 'false',

                       // Example content CSS (should be your site CSS)
                       'content_css' => "CURRENT_THEME_PATH + \"/javascript/tiny_mce/content.css\"",

                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' => "\"lists/template_list.js\"",
                       'external_link_list_url'     => "\"lists/link_list.js\"",
                       'external_image_list_url'    => "\"lists/image_list.js\"",
                       'media_external_list_url'    => "\"lists/media_list.js\""),

      'medium' => array(
                       // General options
                       'mode'         => "\"textareas\"",
                       'theme'        => "\"advanced\"",
                       'skin'         => "\"o2k7\"",
                       'skin_variant' => "\"silver\"",
                       'elements'     => "\"elm1,elm2\"",
                       'extended_valid_elements' => "\"b,u,font[color],a[href|title|alt],ul[class|style],ol[class|style],li[class|style],div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang|style]\"",

                       // Installed plugins
                       'plugins' => "\"safari,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,insertdatetime,preview,media,print,contextmenu,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,imagemanager,filemanager\"",

                       // Installed buttons
                       'theme_advanced_buttons1' => "\"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,fontselect,fontsizeselect\"",
                       'theme_advanced_buttons2' => "\"bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\"",
                       'theme_advanced_buttons3' => "\"tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,advhr\"",
                       'theme_advanced_buttons4' => "\"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,insertfile,insertimage,|,print\"",

                       // TinyMCE options
                       'theme_advanced_toolbar_location'   => "\"top\"",
                       'theme_advanced_toolbar_align'      => "\"left\"",
                       'theme_advanced_statusbar_location' => "\"bottom\"",
                       'theme_advanced_resizing'           => 'true',
                       'theme_advanced_resize_horizontal'  => 'false',
                       'apply_source_formatting'           => 'false',
                       'relative_urls'                     => 'false',
                       'entity_encoding'                   =>  "\"raw\"",
                       'forced_root_block'                 => 'false',
                       'force_p_newlines'                  => 'false',
                       'force_br_newlines'                 =>'true',
                       'debug'                             => 'false',

                       // Example content CSS (should be your site CSS)
                       'content_css' => "CURRENT_THEME_PATH + \"/javascript/tiny_mce/content.css\"",

                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' => "\"lists/template_list.js\"",
                       'external_link_list_url'     => "\"lists/link_list.js\"",
                       'external_image_list_url'    => "\"lists/image_list.js\"",
                       'media_external_list_url'    => "\"lists/media_list.js\""),

      'base' => array(
                       // General options
                       'mode'         => "\"textareas\"",
                       'theme'        => "\"advanced\"",
                       'skin'         => "\"o2k7\"",
                       'skin_variant' => "\"silver\"",
                       'elements'     => "\"elm1,elm2\"",
                       'extended_valid_elements' => "\"b,u,font[color],a[href|title|alt],ul[class|style],ol[class|style],li[class|style],div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang|style]\"",

                       // Installed plugins
                       'plugins' => "\"safari,preview,style,advimage,advlink,emotions,media,contextmenu,noneditable,visualchars\"",

                       // Installed buttons
                       'theme_advanced_buttons1' => "\"styleselect, fontsizeselect, justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,anchor\"",
                       'theme_advanced_buttons2' => "\"bold,italic,strikethrough,|,forecolor,backcolor,|,image,media,|,emotions,cite,|,removeformat,preview,undo,code\"",
                       'theme_advanced_buttons3' => "\"\"",
                       'theme_advanced_buttons4' => "\"\"",

                       // TinyMCE options
                       'theme_advanced_toolbar_location'   => "\"top\"",
                       'theme_advanced_toolbar_align'      => "\"left\"",
                       'theme_advanced_statusbar_location' => "\"bottom\"",
                       'theme_advanced_resizing'           => 'true',
                       'theme_advanced_resize_horizontal'  => 'false',
                       'apply_source_formatting'           => 'false',
                       'relative_urls'                     => 'false',
                       'entity_encoding'                   =>  "\"raw\"",
                       'forced_root_block'                 => 'false',
                       'force_p_newlines'                  => 'false',
                       'force_br_newlines'                 =>'true',
                       'debug'                             => 'false',

                       // Example content CSS (should be your site CSS)
                       'content_css' => "CURRENT_THEME_PATH + \"/javascript/tiny_mce/content.css\"",

                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' => "\"lists/template_list.js\"",
                       'external_link_list_url'     => "\"lists/link_list.js\"",
                       'external_image_list_url'    => "\"lists/image_list.js\"",
                       'media_external_list_url'    => "\"lists/media_list.js\""),
      'minimal' => array(
                       // General options
                       'mode'         => "\"textareas\"",
                       'theme'        => "\"advanced\"",
                       'skin'         => "\"o2k7\"",
                       'skin_variant' => "\"silver\"",
                       'elements'     => "\"elm1,elm2\"",
                       'extended_valid_elements' => "\"b,u,font[color],a[href|title|alt],ul[class|style],ol[class|style],li[class|style],div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang|style]\"",

                       // Installed plugins
                       'plugins' => "\"safari,preview,style,advimage,advlink,emotions,media,contextmenu,noneditable,visualchars\"",

                       // Installed buttons
                       'theme_advanced_buttons1' => "\"styleselect, justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,bold,italic,strikethrough,|,forecolor,|,image,media,|,emotions,cite,|,preview,undo\"",
                       'theme_advanced_buttons2' => "\"\"",
                       'theme_advanced_buttons3' => "\"\"",
                       'theme_advanced_buttons4' => "\"\"",

                       // TinyMCE options
                       'theme_advanced_toolbar_location'   => "\"top\"",
                       'theme_advanced_toolbar_align'      => "\"left\"",
                       'theme_advanced_statusbar_location' => "\"bottom\"",
                       'theme_advanced_resizing'           => 'true',
                       'theme_advanced_resize_horizontal'  => 'false',
                       'apply_source_formatting'           => 'false',
                       'relative_urls'                     => 'false',
                       'entity_encoding'                   =>  "\"raw\"",
                       'forced_root_block'                 => 'false',
                       'force_p_newlines'                  => 'false',
                       'force_br_newlines'                 =>'true',
                       'debug'                             => 'false',

                       // Example content CSS (should be your site CSS)
                       'content_css' => "CURRENT_THEME_PATH + \"/javascript/tiny_mce/content.css\"",

                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' => "\"lists/template_list.js\"",
                       'external_link_list_url'     => "\"lists/link_list.js\"",
                       'external_image_list_url'    => "\"lists/image_list.js\"",
                       'media_external_list_url'    => "\"lists/media_list.js\""),
      'blog' => array(
                       // General options
                       'mode'         => "\"textareas\"",
                       'theme'        => "\"advanced\"",
                       'skin'         => "\"o2k7\"",
                       'skin_variant' => "\"silver\"",
                       'elements'     => "\"elm1,elm2\"",
                       'extended_valid_elements' => "\"b,u,font[color],a[href|title|alt],ul[class|style],ol[class|style],li[class|style],div[class],object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|style|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],p[lang|style]\"",

                       // Installed plugins
                       'plugins' => "\"safari,preview,style,advimage,advlink,emotions,media,contextmenu,noneditable,visualchars\"",

                       // Installed buttons
                       'theme_advanced_buttons1' => "\"bold,italic,underline,strikethrough,sub,sup,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect, outdent,indent,separator,undo,redo,separator,link,unlink,anchor,cleanup,help,code,hr,removeformat,separator,image,media,separator,charmap\"",
                       'theme_advanced_buttons2' => "\"\"",
                       'theme_advanced_buttons3' => "\"\"",
                       'theme_advanced_buttons4' => "\"\"",

                       // TinyMCE options
                       'theme_advanced_toolbar_location'   => "\"top\"",
                       'theme_advanced_toolbar_align'      => "\"left\"",
                       'theme_advanced_statusbar_location' => "\"bottom\"",
                       'theme_advanced_resizing'           => 'true',
                       'theme_advanced_resize_horizontal'  => 'false',
                       'apply_source_formatting'           => 'false',
                       'relative_urls'                     => 'false',
                       'remove_script_host'                => 'true',
                       'entity_encoding'                   =>  "\"raw\"",
                       'forced_root_block'                 => 'false',
                       'force_p_newlines'                  => 'true',
                       'force_br_newlines'                 => 'false',
                       'debug'                             => 'false',

                       // Example content CSS (should be your site CSS)
                       'content_css' => "CURRENT_THEME_PATH + \"/javascript/tiny_mce/content.css\"",

                       // Drop lists for link/image/media/template dialogs
                       'template_external_list_url' => "\"lists/template_list.js\"",
                       'external_link_list_url'     => "\"lists/link_list.js\"",
                       'external_image_list_url'    => "\"lists/image_list.js\"",
                       'media_external_list_url'    => "\"lists/media_list.js\""),
      );


  public function __construct($type = TinyMCE::full, $mode = 'textareas', $theme = 'advanced',
                              $skin = 'o2k7', $skin_variant = 'silver', $elements = null) {
    $this->type = $type;
    $this->setTinyParam('mode', $mode);
    $this->setTinyParam('theme', $theme);
    $this->setTinyParam('skin', $skin);
    $this->setTinyParam('skin_variant', $skin_variant);
    if($mode == 'textareas') {
      $this->unsetTinyParam('elements');
    } else if($elements) {
      $this->setTinyParam('elements', $elements);
    }
  }

  public function setTinyParam($param_name, $param_value) {
    if(is_bool($param_value)) {
      $param_value = ($param_value) ? 'true' : 'false';
    }
    $this->types[$this->type][$param_name] = "\"$param_value\"";
  }

  public function getTinyParam($param_name) {
    return (isset($this->types[$this->type][$param_name]))
                ? trim($this->types[$this->type][$param_name], " \"'")
                : null;
  }

  public function unsetTinyParam($param_name) {
    if(isset($this->types[$this->type][$param_name])) {
      unset($this->types[$this->type][$param_name]);
    }
  }

  public function installTinyMCE() {
    $out_data  = array();
    $tiny_data = $this->types[$this->type];
    foreach($tiny_data as $key => $value) {
      $out_data[] = "$key : $value";
    }
    $out_data = implode(",\r\n", $out_data);
    $tiny_init = "tinyMCE.init({\r\n$out_data\r\n  });";

    $html = "
     <script language=\"javascript\" type=\"text/javascript\" src=\"".PA::$theme_url."/javascript/tiny_mce/tiny_mce.js\"></script>
     <script language=\"javascript\" type=\"text/javascript\">
    ";
    $html .= $tiny_init;
    $html .= "\n</script>\n";
    return $html;
  }
}
?>
