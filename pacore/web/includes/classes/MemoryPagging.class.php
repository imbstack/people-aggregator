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
* @Class MemoryPagging : generate pagging from data
*                        stored in a memory array
*
*
* Created by:
* Zoran Hron <zhron@broadbandmechanics.com>
*
**/
class MemoryPagging {

  public   $pagging;
  public   $page_items;
  private  $nb_items;
  private  $page_size;
  public   $current_page;
  private  $icons;
  
  public function __construct($items, $page_size = 0, $current_page = -1, $search_key=null) {
    
      $this->pagging    = null;
      $this->icons      = null;
      $this->page_items = array();
      $this->nb_items   = (is_array($items)) ? count($items) : $items;
      $this->page_size  = $page_size;
      if($current_page != -1) {
        $this->current_page = $current_page;
        if((is_array($items))) {
          $this->page_items = $this->buildPageItems($items, $this->page_size, $this->current_page);
        }  
        $this->pagging = self::buildPagging($this->nb_items, $this->page_size, $this->current_page);
      } else if(is_array($search_key)) {
        if((is_array($items))) {
          $this->current_page = $this->getPageForItem($items, $search_key);
          $this->page_items = $this->buildPageItems($items, $this->page_size, $this->current_page);
          $this->pagging = self::buildPagging($this->nb_items, $this->page_size, $this->current_page);
        }
      }
  }

  public function getPageForItem($items, $search_key) {
    list($key, $value) = $search_key;
    $cnt = 0;
    $page = 0;
    $position = null;
    $method = "get_$key";
    foreach($items as $item) {
      if(method_exists($item, $method)) {
        if($item->{$method}() == $value) {
          $position = $cnt;
          break; 
        }
      }
      $cnt++;
    }
    if(!is_null($position)) {
      $page = intval($position / $this->page_size);
    }
    return $page;
  }
  
  public function buildPageItems($items, $page_size, $current_page) {
    $nb_items = count($items);
    $p_start  = ($page_size * ($current_page +1)) - $page_size;
    if(($nb_items - $p_start) < $page_size) {
      $page_size = $nb_items - $p_start;
    }
    return array_slice($items, $p_start, $page_size);
  }

  public function getPageItems() {
    return $this->page_items;
  }
    
  public static function buildPagging($nb_items, $page_size, $current_page) {
      
    $pagging     = null;
    $page_range  = intval($current_page / 10);
    $page_index  = $current_page % 10;
    $total_pages = intval($nb_items / $page_size);
    if(($nb_items % $page_size) == 0) --$total_pages;
    $pagging_pages_mod = $total_pages % 10;
      
    if($total_pages > 0) {
      
      if($total_pages < 10) {
        $pagging['first'] = null;
        $pagging['last']  = null;
        $pagging['prev']  = null;
        $pagging['next']  = null;
          
        for($cnt = 0; $cnt <= $total_pages; $cnt++) {
          if($cnt == $page_index) {
            $pagging['selected'] = $cnt;        // selected page
          }
          $pagging['pages'][$cnt] = $cnt;
        }
      } else {
        $pagging['first'] = 0;
        $pagging['last']  = $total_pages -1;
        $pagging_first    = $page_range * 10;
        $pagging_last     = (($pagging_first + 10) > $total_pages)
                          ? $pagging_first + $pagging_pages_mod -1
                          : $pagging_first + 9;
        
        $pagging_cnt = $pagging_last - $pagging_first;
        $pagging['prev'] = ($pagging_first > 0) ? ($pagging_first -1) : 0;
        
        for($cnt = 0; $cnt <= $pagging_cnt; $cnt++) {
          if($cnt == $page_index) {
            $pagging['selected'] = $cnt;        // selected page
          }
          $pagging['pages'][$cnt] = $pagging_first++;
        }
        $pagging['next']  = ($pagging_last < ($total_pages -1))
                          ? ($pagging_last +1)
                          : $pagging_last;
      }
    }
    return $pagging;
  }
    
  public function getPaggingData() {
    return $this->pagging;
  }

  public function getPaggingLinks($base_url, $page_key, $css_class, $css_class_selected, $icons = null) {
    $html = null;
    if(isset($this->pagging['first']) && !is_null($this->pagging['first'])) {
      // add_querystring_var() function defined in functions.php
      $url   = add_querystring_var($base_url, $page_key, $this->pagging['first']);
      if(isset($icons['first'])) {
        $text = $this->get_img_tag($icons['first'], $css_class);
      } else {
        $text = "<<";
      }
      $html .= $this->get_a_tag($url, $css_class, $text) . "\r\n";
    }
    if(isset($this->pagging['prev']) && !is_null($this->pagging['prev'])) {
      $url   = add_querystring_var($base_url, $page_key, $this->pagging['prev']);
      if(isset($icons['prev'])) {
        $text = $this->get_img_tag($icons['prev'], $css_class);
      } else {
        $text = "<";
      }
      $html .= $this->get_a_tag($url, $css_class, $text) . "\r\n";
    }
    if(isset($this->pagging['pages'])) {
      foreach($this->pagging['pages'] as $page_n) { 
        $_css_class = ($page_n == $this->pagging['selected']) ? $css_class_selected : $css_class;
        $url   = add_querystring_var($base_url, $page_key, $page_n);
        $html .= $this->get_a_tag($url, $_css_class, (string)$page_n) . "\r\n";
      }
    }  
    if(isset($this->pagging['next']) && !is_null($this->pagging['next'])) {
      $url   = add_querystring_var($base_url, $page_key, $this->pagging['next']);
      if(isset($icons['next'])) {
        $text = $this->get_img_tag($icons['next'], $css_class);
      } else {
        $text = ">";
      }
      $html .= $this->get_a_tag($url, $css_class, $text) . "\r\n";
    }
    if(isset($this->pagging['last']) && !is_null($this->pagging['last'])) {
      $url   = add_querystring_var($base_url, $page_key, $this->pagging['last']);
      if(isset($icons['last'])) {
        $text = $this->get_img_tag($icons['last'], $css_class);
      } else {
        $text = ">>";
      }
      $html .= $this->get_a_tag($url, $css_class, $text) . "\r\n";
    }
    return $html;
  }

  private function get_a_tag($url, $class, $text) {
    return "<a href=\"$url\" class=\"$class\">$text</a>";
  }
  
  private function get_img_tag($url, $class) {
    return "<img src=\"$url\" class=\"$class\" />";
  }
}

?>