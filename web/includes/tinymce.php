<?php

function install_tinymce($mode = 'full') {
  global $tinymce_installed;
  if (!isset($tinymce_installed)) {
    // never include more than once -- this means you can call
    // install_tinymce() many times if you want in a page and don't
    // need to worry about extra inclusion.
    $tinymce_installed = TRUE;
?>
<script language="javascript" type="text/javascript" src="<?=PA::$theme_url?>/javascript/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
    theme: "advanced",
    mode: "textareas",
    extended_valid_elements: "object[align|width|height],param[name|value],embed[quality|src|type|wmode|width|height],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]",
    plugins: "flash",
    theme_advanced_toolbar_location: "top",
    relative_urls: false,
    remove_script_host: true,
    document_base_url: "<?=PA::$url?>",  
<? if ($mode == 'full') { ?>
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,sub,sup,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect",
    theme_advanced_buttons2 : "outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,cleanup,help,code,hr,removeformat,visualaid,separator,charmap,flash",
    theme_advanced_buttons3 : ""
<? } else { ?>
    theme_advanced_buttons1 : "formatselect,bold,italic,underline,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,undo,redo,link,unlink,image,code",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : ""
<? } ?>
});
</script>
<?php
  }
}

?>