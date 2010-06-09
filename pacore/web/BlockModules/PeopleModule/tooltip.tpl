<head>
  <style type="text/css">
    @import url(<?=PA::$theme_url . "/javascript/facewall/tooltip/tooltip.css" ?>);
  </style>
</head>
<html>
 <body style="margin:0; background-color:transparent">
 <div class="tooltip iettip" >
  <div class="tooltip_containter ie_cont">
     <div class="tooltip_header"><?= chop_string($link['display_name'], 14) ?></div>
     <div class="tooltip_inner">
       <div class="tooltip_img">
         <a href="javascript: window.parent.location = '<?= $link['user_url'] ?>'">
           <img id="bigimg_<?= $link['user_id'] ?>" src="<?= $link['big_picture'] ?>" alt=""  />
         </a>
       </div>
       <br />
       <table>
         <tr>
          <td><?= __('Location') . ':' ?></td>
          <td><?= ((trim($link['location'])) != "") ? $link['location'] : __('no data') ?></td>
         </tr>
         <tr>
          <td><?= __('Age') . ':' ?></td>
          <td><?= ((trim($link['age'])) != "") ? $link['age'] : __('no data') ?> </td>
         </tr>
         <tr>
          <td><?= __('Gender') . ':' ?></td>
          <td><?= ((trim($link['gender'])) != "") ? $link['gender'] : __('no data') ?></td>
         </tr>
       </table>
     </div>
     <div class="tooltip_footer"><a href="javascript: window.parent.location = '<?= PA::$url .PA_ROUTE_EDIT_RELATIONS.'/uid='.$link['user_id'].'&do=add'?>'; window.close();"><?= __('Add as') . ' ' . $rel_term ?></a></div>
    </div> 
   </div>
 </body>
</html> 