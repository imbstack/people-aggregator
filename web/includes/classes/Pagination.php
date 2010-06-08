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
<?PHP
/**
 * Class for creating pagination links
 *
 * @package Pagination
 * @author Tekriti Software (http://www.tekritisoftware.com)
 * TODO : add pagination for ajax 
 */



class Pagination 
{
  /**
   * @var array for paging navigation
   * @access public
   */
  private $pageDetails = array();
  
  public $separator = ' ';
  public $str_backward = 'Prev&larr;';
  public $str_forward = '&rarr;Next';
  public $max_links = 5;
  public $page_var= 'page';
  /**
   * write description
   */
   
  public function setPaging( $paging) 
  {
    global $app;
    if (empty($paging))
    {
      return FALSE;
    }
    $paging['count'] = (@$paging['count']) ? $paging['count'] : 0;
    $pageCount = ceil((int)$paging['count']/(int)$paging['show']);
    $this->_pageDetails = array(
                        'page'=>$paging[$this->page_var],
                        'recordCount'=>$paging['count'],
                        'pageCount' =>$pageCount,
                        'firstPage' =>1,
                        'lastPage' =>$pageCount,
                        'nextPage'=> ($paging[$this->page_var] < $pageCount) ? $paging[$this->page_var]+1 : '',
                        'previousPage'=> ($paging[$this->page_var]>1) ? $paging[$this->page_var]-1 : '',
                        'url'=>( !empty($paging['pageurl']) ) ? $paging['pageurl'] : PA::$url . $app->current_route,
                        'queryString'=>( !empty($paging['querystring']) ) ? $paging['querystring'] : $app->current_query,
                        'extra'=>(!empty($paging['extra'])) ? $paging['extra']:NULL
                        );
  }
  
  /**
   * write description
   */
  public function getPreviousPage() 
  {
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
    if (!empty($this->_pageDetails['previousPage']))
    {
      $link = '<a href='. $this->normalize_query($this->_pageDetails['url'].'?'.$this->page_var.'='.$this->_pageDetails['previousPage'].$this->rebuildQueryString($this->page_var).''.$this->_pageDetails['extra']).'>'.__('Previous').'</a>'.$this->separator;
    }
    else
    {
      $link='';
    }
    
    return $link;
  }
  /**
   * write description
   */
  public function getNextPage() 
  {
    $link = '';
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
    if (!empty($this->_pageDetails['nextPage']))
    {
      
      $link = '<a href='. $this->normalize_query($this->_pageDetails['url'].'?'.$this->page_var.'='.$this->_pageDetails['nextPage'].$this->rebuildQueryString($this->page_var).''.$this->_pageDetails['extra']).'>'.__('Next').'</a>';
    }
    
    return $link;
  }
  /**
   * write description
   */
  public function getPageLinks() 
  {
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
   
    $link = ''; 
    if($this->_pageDetails['pageCount'] == 1) {
        return;
    }
    
    $curr_pages = $this->_pageDetails['page'];
    $all_pages = $this->_pageDetails['pageCount'];
    
    $css_current = 'paging-links';
    $var = $this->page_var;
    $navi_string = null;
    if ($curr_pages <= $all_pages && $curr_pages >= 0) {
      if ($curr_pages > ceil($this->max_links/2)) {
        $start = ($curr_pages - ceil($this->max_links/2) > 0) ? $curr_pages - ceil($this->max_links/2) : 1;
        $end = $curr_pages + ceil($this->max_links/2);
        if ($end >= $all_pages) {
          $end = $all_pages;
          //$end = $all_pages + 1;
          $start = ($all_pages - ($this->max_links - 1) > 0) ? $all_pages  - ($this->max_links - 1) : 1;
        }
      } else {
        $start = 1;
        $end = ($all_pages >= $this->max_links) ? $this->max_links : $all_pages;
      }
      if($all_pages >= 1) {
        $forward = $curr_pages + 1;
        $backward = $curr_pages - 1;
        $navi_string = ($curr_pages > 1) ? "<a href=\"".$this->normalize_query($this->_pageDetails['url']."?".$var."=".$backward.$this->rebuildQueryString($var)."\"".$this->_pageDetails['extra']).">".$this->str_backward.'</a> ' : ''.$this->str_backward.'';
        if (!isset($back_forward)) {
        //for($a = $start + 1; $a <= $end; $a++){
          for($a = $start; $a <= $end; $a++){
            //$theNext = $a - 1; // because a array start with 0
            $theNext = $a;
            if ($theNext != $curr_pages) {
              $navi_string .= "<a href=\"".$this->normalize_query($this->_pageDetails['url']."?".$var."=".$theNext.$this->rebuildQueryString($var)."\"".$this->_pageDetails['extra']).">";
              $navi_string .= $a."</a>";
              $navi_string .= ($theNext < ($end - 1)) ? $this->separator : $this->separator;
            } else {
              $navi_string .= ($css_current != "") ? "<span class='selected'>".$a."</span>" : $a;
              $navi_string .= ($theNext < ($end - 1)) ? $this->separator : $this->separator;
            }
          }
        }
        $navi_string .= ($curr_pages < $all_pages) ? "<a href=\"".$this->normalize_query($this->_pageDetails['url']."?".$var."=".$forward.$this->rebuildQueryString($var)."\"".$this->_pageDetails['extra']).">".$this->str_forward."</a>" : ''.$this->str_forward.'';
      }
    }
    
    return $navi_string;
    /*for ( $i=1; $i<=$this->_pageDetails['pageCount']; $i++ ) 
    {
      if ( $i == $this->_pageDetails['page'] )
      {
        $link.="<b>$i</b>".$this->separator;
      } 
      else
      {

        $link .= '<a href='.$this->_pageDetails['url'].'?page='.$i.$this->rebuildQueryString('page').'>'.$i.'</a>'.$this->separator;
        
      }
      
      $link.='&nbsp;';
    }
    return $link;*/
  }
  /**
   * write description
   */
  private function rebuildQueryString( $curr_var ) 
  {
    
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
    if (!empty($this->_pageDetails['queryString'])) 
    {

      $parts = explode("&", $this->_pageDetails['queryString']);
      $newParts = array();
      foreach ($parts as $val) 
      {
        if (stristr($val, $curr_var) == false)  
        {
          array_push($newParts, $val);
        }
      }
      if (count($newParts) != 0) 
      {
        $qs = '&'.implode('&', $newParts);
        
      }
      else 
      {
        return false;
      }
      return $qs; // this is your new created query string
    }
    else 
    {
      return false;
    }
  } 
  
  private function normalize_query($qstr) {
     $ret_str = preg_replace('/(\?\&|\&\?|\?+|\&+)/i', '&', $qstr);
     return preg_replace('/(\&)/', '?', $ret_str, 1);
  }
  
    /**
   * Move to the First page
   */
  public function getFirstPage() 
  {
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
    if ($this->_pageDetails['firstPage'] != $this->_pageDetails['page'])
    {
      $link = '<a href="'.$this->normalize_query($this->_pageDetails['url'].'?'.$this->page_var.'='.$this->_pageDetails['firstPage'].$this->rebuildQueryString($this->page_var).'"'.$this->_pageDetails['extra']).'>'.__('First').'</a>'.$this->separator;
    }
    else
    {
      $link='';
    }
    
    return $link;
  }
    /**
   * Move to the Last page
   */
  public function getLastPage() 
  {
    if ( empty($this->_pageDetails) ) 
    {
      return FALSE;
    }
    if ($this->_pageDetails['lastPage'] != $this->_pageDetails['page'])
    { 
      $link = '<a href="'.$this->normalize_query($this->_pageDetails['url'].'?'.$this->page_var.'='.$this->_pageDetails['lastPage'].$this->rebuildQueryString($this->page_var).'"'.$this->_pageDetails['extra']).'>'.__('Last').'</a>'.$this->separator;
    }
    else
    {
      $link='';
    }
    
    return $link;
  }
    
}
  
?>