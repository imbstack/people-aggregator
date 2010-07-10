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
 * @Class PAForm
 *
 *  Creating forms dynamically
 *
 *
 *
 *
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 */
require_once "web/includes/classes/xHtml.class.php";

 class PAForm {

   public $xform;
   private $closed;
   private $form_name;
   public function __construct($name, $method = 'POST',  $action = null, $attributes = null)  {
       $this->form_name = $name;
       $attrs = ($attributes) ? $attributes : array();
       $attrs['name'] = $name;
       $attrs['method'] = $method;
       $attrs['action'] = $action;
       $this->xform = xHtml::htmlTag( 'form', $attrs, true);
//       $this->xform .= xHtml::htmlTag( 'fieldset', null, true);
       $this->closed = false;
   }

   public function addInputTag($tag_type, $params = array()) {
       if(!isset($params['name']) && isset($params['id'])) {
         $params['name'] = $this->form_name."[{$params['id']}]";
       }
       switch($tag_type) {
           case 'button':
           case 'file':
           case 'image':
           case 'password':
           case 'text':
          case 'reset':
          case 'hidden':
          case 'submit':
               if(!isset($params['type'])) $params['type'] = $tag_type;
               $this->xform .= xHtml::xhtmlTag('input', $params, false);  // 'false' means - closed XHtml tag
               break;
           case 'radio':
               $name = $params['name'];
               $checked = $params['checked'];
               unset($params['name'],$params['checked']);
               $this->xform .= xHtml::radioButtTag($name, $params, $checked);
               break;
           case 'checkbox':
               $name = $params['name'];
               $checked = $params['value'];
               unset($params['name']);
               $this->xform .= xHtml::checkboxTag($name, $params, $checked);
               break;
           case 'textarea':
               $value = $params['value'];
               unset($params['value']);
               $this->xform .= xHtml::htmlTag('textarea', $params, true);  //  'true' means - opening tag
               if(!empty($value)) {
                  $this->xform .= $value;                // inserting text into textarea
               }
               $this->xform .= xHtml::htmlTag('textarea', null, false);  //  'false' means - closing html tag
               break;
           case 'select':
               $options = $params['options'];
               $selected = $params['selected'];
               unset($params['options']);
               unset($params['selected']);
               $this->xform .= xHtml::selectTag($options, $params, $selected);
               break;
       }
   }

   public function addContentTag($tag_name, $params = array()) {
       if(!isset($params['name']) && isset($params['id'])) {
         $params['name'] = $this->form_name."[{$params['id']}]";
       }
       $value = $params['value'];
       unset($params['value']);
       $this->xform .= xHtml::htmlTag($tag_name, $params, true);
       if(!empty($value)) {
           $this->xform .= $value;
       }
       $this->xform .= xHtml::htmlTag($tag_name, null, false);
   }

   public function openTag($tag_name, $params = array()) {
       if(!isset($params['name']) && isset($params['id'])) {
         $params['name'] = $this->form_name."[{$params['id']}]";
       }
       $this->xform .= xHtml::htmlTag($tag_name, $params, true);
   }

   public function closeTag($tag_name) {
       $this->xform .= xHtml::htmlTag($tag_name, null, false);
   }


   public function addInputField($tag_type, $label_txt, $params = array()) {
       if(!isset($params['name']) && isset($params['id'])) {
         $params['name'] = $this->form_name."[{$params['id']}]";
       }
       if(isset($params['required'])) {
          $required = $params['required'];
          unset($params['required']);
       } else {
          $required = false;
       }
       if(isset($params['css_class'])) {
          $field_css = $params['css_class'];
          unset($params['css_class']);
       } else {
          $field_css = 'field';
       }
       if(isset($params['description'])) {
          $description = $params['description'];
          unset($params['description']);
       } else {
          $description = null;
       }
       $this->xform .= xHtml::htmlTag('div', array('class' => $field_css), true);
       $this->xform .= xHtml::htmlTag('label', array('for' => $params['name']), true);
       if($required) {
          $this->xform .= xHtml::contentTag('span', '*', array('class' => 'required'));
       }
       $this->xform .= __($label_txt);
       $this->xform .= xHtml::htmlTag('label', null, false);
       $this->addInputTag($tag_type, $params);
       if($description) {
          $this->xform .= xHtml::htmlTag('div', array('class' => 'field_descr'), true);
          $this->xform .= __($description);
          $this->xform .= xHtml::htmlTag('div', null, false);
       }
       $this->xform .= xHtml::htmlTag('div', null, false);
   }

   public function addHtml($html) {
       $this->xform .= $html;
   }

   public function show() {
       $this->closeForm();
       echo $this->xform;
   }

   public function getHtml() {
       $this->closeForm();
       return $this->xform;
   }

   private function closeForm() {
       if(!$this->closed) {
//          $this->xform .= xHtml::htmlTag( 'fieldset', null, false);
          $this->xform .= xHtml::htmlTag('form', null, false);
          $this->closed = true;
       }
   }
}
?>
