<?php

/**
 * @class xHtml
 *
 * Provides a set of helpers for creating (X)HTML tags.
 * This means that you can create HTML tags in PHP programming style.
 *
 * @author     Zoran Hron <zhron@net.hr>
 * @version    0.1.1
 *
 */


class xHtml {

  private $encoding = "UTF-8";


  /**
   * This function sets class internal encoding for escaping htmlspecialchars.
   *
   *
   *   @param $encoding  :   (\p string)      character set
   *
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $tags->setEncoding("UTF-8");
   *   @endcode
   */
  public function setEncoding($encoding = "UTF-8")
  {
    $this->encoding = $encoding;
  }


  /**
   * This function returns string of a complete HTML tag with attributes
   *
   *
   *   @param $tag_name :   (\p string)      tag name
   *   @param $tag_attr :   (\p array)       array of tag attributes
   *   @param $is_open  :   (\p bool)        define tag type (opening or closing tag)
   *
   * @return                (\p string)      formatted HTML tag
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $tag_attr = array("style" => "font-size: 10pt");
   *      $tags->html_tag("h1", $tag_attr, true);
   *   @endcode
   */
  public function html_tag($tag_name, $tag_attr = array(), $is_open = true)
  {
    if (!isset($tag_name) || empty($tag_name))  return '';

    if($is_open) {

      return '<' . strtolower($tag_name)
                 . ((count($tag_attr) > 0) ? $this->attrsToString($tag_attr) : '')
                 . ">\n";
    } else {
      return "</" . strtolower($tag_name) . ">\n";
    }
  }

  public static function htmlTag($tag_name, $tag_attr = array(), $is_open = true)
  {
    $instance = new self();
    return $instance->html_tag($tag_name, $tag_attr, $is_open);
  }

  /**
   * This function returns string of a complete XHTML tag with attributes
   *
   *
   *   @param $tag_name :   (\p string)      tag name
   *   @param $tag_attr :   (\p array)       array of tag attributes
   *   @param $is_open  :   (\p bool)        define tag type (opening or closing tag)
   *
   * @return                (\p string)      formatted XHTML tag
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $tag_attr = array("style" => "font-size: 10pt");
   *      $tags->xhtml_tag("h1", $tag_attr, true);
   *   @endcode
   */
  public function xhtml_tag($tag_name, $tag_attr = array(), $is_open = true)
  {
    if (!isset($tag_name) || empty($tag_name))  return '';

    return '<' . strtolower($tag_name)
               . ((count($tag_attr) > 0) ? $this->attrsToString($tag_attr) : '')
               . (($is_open) ? ">\n" : "/>\n");
  }

  public static function xhtmlTag($tag_name, $tag_attr = array(), $is_open = true)
  {
    $instance = new self();
    return $instance->xhtml_tag($tag_name, $tag_attr, $is_open);
  }

  public function checkbox_tag($name, $tag_attr = array(), $is_checked = false)
  {
    if (!isset($name) || empty($name))  return '';

    return "<input type=\"checkbox\" name=\"$name\" value=\"$is_checked\" "
               . ((count($tag_attr) > 0) ? $this->attrsToString($tag_attr) : '')
               . (($is_checked) ? " checked" : " ")
               . "/>\n";
  }

  public static function checkboxTag($name, $tag_attr = array(), $is_checked = false)
  {
    $instance = new self();
    return $instance->checkbox_tag($name, $tag_attr, $is_checked);
  }


  public function radio_butt_tag($name, $tag_attr = array(), $is_checked = false)
  {
    if (!isset($name) || empty($name))  return '';

    return "<input type=\"radio\" name=\"$name\" value=\"$is_checked\" "
               . ((count($tag_attr) > 0) ? $this->attrsToString($tag_attr) : '')
               . (($is_checked) ? " checked" : " ")
               . "/>\n";
  }

  public static function radioButtTag($name, $tag_attr = array(), $is_checked = false)
  {
    $instance = new self();
    return $instance->radio_butt_tag($name, $tag_attr, $is_checked);
  }
  /**
   * This function returns string of a closed HTML tag with attributes and content inside
   *
   *
   *   @param $tag_name :   (\p string)      tag name
   *   @param $content  :   (\p string)      text or HTML content
   *   @param $tag_attr :   (\p array)       array of tag attributes
   *   @param $escape   :   (\p bool)        escape htmlspecialchars
   *
   * @return                (\p string)      formatted HTML tag
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $content = "This is a test";
   *      $is_escape  = true;
   *      echo $tags->content_tag("div",
   *                              $content,
   *                              array("id" => "example_id", "style" => "border:1px solid red; width:250px"),
   *                              $escape);
   *   @endcode
   */
  public function content_tag($tag_name, $content = '', $tag_attr = array(), $escape = true)
  {
    if (!isset($tag_name) || empty($tag_name))  return '';

    return   $this->html_tag($tag_name, $tag_attr, true)
           . (($escape) ? htmlspecialchars($content, ENT_COMPAT, $this->encoding) : $content)
           . $this->html_tag($tag_name, array(), false);
  }

  public static function contentTag($tag_name, $content = '', $tag_attr = array(), $escape = true)
  {
    $instance = new self();
    $instance->setEncoding("UTF-8");
    return $instance->content_tag($tag_name, $content, $tag_attr, $escape);
  }


  /**
   * This function returns string of a closed JavaScript tag encoded in XHTML style
   *
   *
   *   @param $content  :   (\p string)      JavaScript code
   *
   *   @return              (\p string)      formatted JavaScript tag
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      echo $tags->javascript_tag("alert('Hello');");
   *
   *   @endcode
   */
  public function javascript_tag($content)
  {
    return $this->content_tag('script',
                             "\n//".$this->cdata_section("\n$content\n//")."\n",
                              array('type' => 'text/javascript'), false);
  }

  public static function javascriptTag($content)
  {
    $instance = new self();
    $instance->setEncoding("UTF-8");
    return $instance->javascript_tag($content);
  }


  /**
   * This function returns HTML string of "select" HTML tag with attributes and options inside
   *
   *
   *   @param $sel_options :   (\p array)    array of option=>value
   *   @param $tag_attr    :   (\p array)    array of tag attributes
   *   @param $selected    :   (\p string)   selected option
   *
   *   @return                 (\p string)      formatted HTML tag
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $options = array("Zagreb"    => "value1",
   *                       "Dubrovnik" => "value2",
   *                       "Rijeka"    => "value3" );
   *
   *      $selected = "value3";
   *
   *      $attribs = array("id"       => "my_select",
   *                       "name"     => "my_select"
   *                      );
   *
   *      echo $tags->select_tag($options, $attribs, $selected);
   *
   *   @endcode
   */
  public function select_tag($sel_options = array(), $tag_attr = array(), $selected = '')
  {

    $out_html = '';
    if(count($sel_options) > 0) {
      $out_html .= $this->html_tag('select', $tag_attr, true) ."\n";
      foreach ($sel_options as $option => $value) {
        $attrs = ($value == $selected) ? array('value' => $value, 'selected' => 'selected')
                                       : array('value' => $value, );
        $out_html .= $this->content_tag('option', $option , $attrs);
      }
      $out_html .= $this->html_tag('select', array(), false);
    }

    return $out_html;
  }

  public static function selectTag($sel_options = array(), $tag_attr = array(), $selected = '')
  {
    $instance = new self();
    $instance->setEncoding("UTF-8");
    return $instance->select_tag($sel_options, $tag_attr, $selected);
  }


  /**
   * This function returns HTML string of a complete unordered HTML list
   *
   *
   *   @param $li_contents :   (\p array)    array of contents
   *   @param $ul_attr     :   (\p array)    array of ul tag attributes
   *   @param $li_attr     :   (\p array)    array of li tag attributes
   *
   *   @return                 (\p string)      formatted ul HTML tag with li elements
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      $li_contents = array("Zagreb", "Dubrovnik", "Rijeka" );
   *
   *      $ul_attr = array("class" => "my_list" );
   *      $li_attr = array("style" => "display: block" );
   *
   *      echo $tags->ulist_tag($li_contents, $ul_attr, $li_attr);
   *
   *   @endcode
   */
  public function ulist_tag($li_contents = array(), $ul_attr = array(), $li_attr = array())
  {

    $out_html = '';
    if(count($li_contents) > 0) {
      $out_html .= $this->html_tag('ul', $ul_attr, true)."\n";
      foreach ($li_contents as $content) {
        $out_html .= $this->content_tag('li', $content , $li_attr, false);
      }
      $out_html .= $this->html_tag('ul', array(), false);
    }
    return $out_html;
  }

  public static function ulistTag($li_contents = array(), $ul_attr = array(), $li_attr = array())
  {
    $instance = new self();
    $instance->setEncoding("UTF-8");
    return $instance->ulist_tag($li_contents, $ul_attr, $li_attr);
  }

  /**
   * This function returns string of a CDATA section
   *
   *
   *   @param $content  :   (\p string)      input text
   *
   *   @return              (\p string)      formatted CDATA section
   *
   * \b Example:
   *   @code
   *      $tags = new xHtml();
   *      echo $tags->cdata_section("<sender>John Smith</sender>");
   *
   *   @endcode
   */
  public function cdata_section($content)
  {
    return "<![CDATA[$content]]>";
  }

  public static function cdataSection($content)
  {
    $instance = new self();
    $instance->setEncoding("UTF-8");
    return $instance->cdata_section($content);
  }

  private function attrsToString($tag_attr = array())
  {
    $out_html = '';
    if(count($tag_attr) > 0) {
      foreach ($tag_attr as $name => $value) {
        $out_html .= ' '. strtolower($name) .'="'.htmlspecialchars($value, ENT_COMPAT, $this->encoding).'"';
      }
    }
    return $out_html;
  }

}
