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
<div id="<?=$link['user_id']?>" class="buddyimg">
  <div id="imgcontainer_<?=$link['user_id']?>" >
    <a href="<?=$link['user_url']?>">
      <img id="img_<?=$link['user_id']?>" src="<?=$link['picture']?>" alt="" style="width: 80px; height: 80px; border: 4px; border-style:solid; border-color:#E0E0E0; display: block" />
</a>
</div>

  <div id="label_<?=$link['user_id']?>">
    <p class="buddytext" style="display: block" align="left">
      <?=chop_string($link['display_name'], 12)?>
    </p>
  </div>
</div>
<!--[if IE]>
 <iframe frameborder="0" allowtransparency="true" scrolling="no" id="tooltip_<?=$link['user_id']?>" class="tooltip" src="<?=PA::$url.PA_ROUTE_PEOPLES_PAGE."?action=tooltip&module=PeopleModule&data=".base64_encode(serialize($link))?>">
<![endif]-->
 <div class="tooltip mozttip" id="tooltip_<?=$link['user_id']?>">
   <div class="tooltip_containter moz_cont">
     <div class="tooltip_header"><?=chop_string($link['display_name'], 14)?></div>
     <div class="tooltip_inner">
       <div class="tooltip_img">
         <a href="<?=$link['user_url']?>">
           <img id="bigimg_<?=$link['user_id']?>" src="<?=$link['big_picture']?>" alt=""  />
         </a>
       </div>
       <br />
       <table>
         <tr>
          <td><?=__('Location').':'?></td>
          <td><?=((trim($link['location'])) != "") ? $link['location'] : __('no data')?></td>
         </tr>
         <tr>
          <td><?=__('Age').':'?></td>
          <td><?=((trim($link['age'])) != "") ? $link['age'] : __('no data')?> </td>
         </tr>
         <tr>
          <td><?=__('Gender').':'?></td>
          <td><?=((trim($link['gender'])) != "") ? $link['gender'] : __('no data')?></td>
         </tr>
       </table>
     </div>
     <div class="tooltip_footer"><a href="<?=PA::$url.PA_ROUTE_EDIT_RELATIONS.'/uid='.$link['user_id'].'&do=add'?>"><?=__('Add as').' '.$rel_term?></a></div>
   </div>
 </div>
<!--[if IE]>
  </iframe>
<![endif]-->

