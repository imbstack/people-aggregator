<?php

require_once "api/ModuleData/ModuleData.php";

class ShowCaseModule extends Module {

    public $module_type = 'network';
    public $module_placement = 'middle'; //This is going to be changed to top
    public $outer_template = 'outer_public_group_center_module.tpl';

    public $Paging;
    public $page_links, $ipage_prev, $page_next, $page_count;
 

    function __construct() {
        parent::__construct();
        $this->title = _("ShowCase");
        $this->html_block_id = "ShowCaseModule"; 
    }

    function render() {
      $data = unserialize(ModuleData::get('showcase'));
      $u = $data['featured_user_name'];
      $upage = PA::$url.'/user/'.$u;
      $up = $data['auto_user_picture_url'];
      $g = TypedGroupEntity::load_for_group($data['featured_group_id']);
      $gname = $g->attributes["name"]["value"];
      $gpage = PA::$url.'/group/gid='.$data['featured_group_id'];
      $gp = $g->attributes["logo"]["value"];
      //PUT VIDEO STUFF HERE
      $b = TypedGroupEntity::load_for_group($data['featured_business_id']);
      $bname = $b->attributes["name"]["value"];
      $bpage = PA::$url.'/group/gid='.$data['featured_business_id'];
      $bp = $b->attributes["logo"]["value"];


      

      $table = '<table width="100%%" border="0" cellpadding="0" cellspacing="0" style="background-color:#fff; padding-top:3px; padding-bottom:10px; ">
					  <tr>
						<th colspan="4" align="center" style="background-color:#999; color:#dfe2e3; padding:4px 0 4px 0;" scope="col">Featured Bar</th>
					  </tr>
					  <tr style="color:#000;">
						<td align="center"><b>User</b></td>
						<td align="center"><b>Group</b></td>
						<td align="center"><b>Video</b></td>
						<td align="center"><b>Business</b></td>
					  </tr>
					  <tr>
						<td align="center">%s</td>
						<td align="center">%s</td>
						<td align="center">%s</td>
						<td align="center">%s</td>
					  </tr>
					</table>';
      $user    =  "<a href='".$upage."'>".uihelper_resize_mk_img($up, 100, 100, 'images/default.png', 'alt=PeopleAggregator')."</a><h4>". $data['featured_user_name'] ."</h4>";
		$group   = "<a href='".$gpage."'>".uihelper_resize_mk_img($gp, 100, 100, 'images/default.png', 'alt=PeopleAggregator')."</a><h4>". $gname ."</h4>";
		$video = "<a href='http://www.people.tdooner.com/'>".uihelper_resize_mk_img('images/default.png', 100, 100, 'images/default.png', 'alt=PeopleAggregator')."</a>";
    $business= "<a href='".$bpage."'>".uihelper_resize_mk_img($bp, 100, 100, 'images/default.png', 'alt=PeopleAggregator')."</a><h4>".$bname."</h4>";
    return  sprintf($table, $user, $group, $video, $business);
	}
}

?>
