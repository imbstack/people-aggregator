<?php
?>
<div class="module_tagcloud">
  <ul>
    <li><?php echo PA::$url ;?></li>
    <li><?php echo PA::$network_info->name;?></li>
    <li><?php echo substr(PA::$network_info->description, 0, 100);?></li>
    <li><a href="<?php echo PA::$url . PA_ROUTE_PEOPLES_PAGE;?>"><?php echo $network_stats['registered_members_count'] .' registered users ';?></a></li>
    <li><a href="<?php echo PA::$url . PA_ROUTE_GROUPS ?>"><?php echo $network_stats['groups_count'] .' groups ';?></a></li>
    <li><a href="<?php echo PA::$url;?>/showcontent.php"><?php echo $network_stats['contents_count'] .' posts ';?></a></li>
    <li><a href="<?php echo PA::$url . PA_ROUTE_PEOPLES_PAGE;?>"><?php echo $network_stats['online_members_count'] .' online users ';?></a></li>
  </ul>
</div>