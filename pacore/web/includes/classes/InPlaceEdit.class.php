<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php

/**
 * @class InPlaceEdit
 *
 * The InPlaceEdit class provides helpers for creating
 * inplace editable HTML tags
 * 
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 * NOTE: HTML tags generated with these helpers depends of inplaced.js script
 *
 */


class InPlaceEdit {


 /**
  *   @brief Create a formatted INPUT HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML INPUT tag
  *
  */
  public static function input($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     $cssclass = ($css_class) ? $css_class . ' inplace_edit' : 'inplace_edit';
     return "<input type=\"text\" id=\"$id\" name=\"$id\" value=\"$value\" class=\"$cssclass\" style=\"$style\" ajaxURL=\"$ajax_url\" tinyMCE=\"$tinymce\" />";
  }

 /**
  *   @brief Create a formatted TEXTAREA HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML TEXTAREA tag
  *
  */
  public static function textarea($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('textarea', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted DIV HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML DIV tag
  *
  */
  public static function div($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('div', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted SPAN HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML SPAN tag
  *
  */
  public static function span($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('span', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted TD HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML TD tag
  *
  */
  public static function td($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('td', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted LI HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML LI tag
  *
  */
  public static function li($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('li', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted H1 HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML H1 tag
  *
  */
  public static function h1($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('h1', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted H2 HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML H2 tag
  *
  */
  public static function h2($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('h2', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted H3 HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML H3 tag
  *
  */
  public static function h3($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('h3', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted H4 HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML H4 tag
  *
  */
  public static function h4($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('h4', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

 /**
  *   @brief Create a formatted H5 HTML tag
  * 
  * 
  *   @param $id :            HTML tag id attribute value
  *   @param $ajax_url :      URL of the server PHP script that should handle update event
  *   @param $value :         initial (default) value
  *   @param $css_class :     CSS class name for the HTML tag
  *   @param $tinymce :       TinyMCE mode ('false', 'minimal', 'basic', 'normal', 'rich')
  *
  *   @return                 formatted HTML H5 tag
  *
  */
  public static function h5($id, $ajax_url, $value = null, $style = null, $tinymce = 'false', $css_class = null) {
     return self::content_tag('h5', $id, $ajax_url, $value, $tinymce, $css_class, $style);
  }

  private static function content_tag($tag_name, $id, $ajax_url, $value, $tinymce, $css_class, $style) {
     $cssclass = ($css_class) ? $css_class . ' inplace_edit' : 'inplace_edit';
     return "<$tag_name id=\"$id\" name=\"$id\" class=\"$cssclass\" style=\"$style\" ajaxURL=\"$ajax_url\" tinyMCE=\"$tinymce\">$value</$tag_name>";
  }

}


?>
