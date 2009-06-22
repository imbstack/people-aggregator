<?php 
$gidQuery = "";
if (!empty($gid)) {
  $gidQuery = '?ccid='.$gid;
}?>
<div class="module_definition_list">  
  <dl>
    <?php
      $links_count = count($links); 
      if (!empty($links_count)) { 
        for ($counter = 0; (($counter < $links_count) && ($counter < $limit)); $counter++) {
    ?>        
        <dt>
          <a href="<?php echo PA::$url . PA_ROUTE_CONTENT ;?>/cid=<?php echo $links[$counter]['content_id'];?>">
            <?php echo _out($links[$counter]['title']); ?>
          </a>
       
        <span><?php echo content_date($links[$counter]['changed']) ?></span>  </dt>   
    <?php
        }
      }
      else { 
        echo '<dt>'.sprintf(__('No contents in blog.  <a href="%s">Click here to make a post</a>!'), PA::$url . "/". FILE_POST_CONTENT.$gidQuery).'</dt>';
      }
    ?>
    
  </dl>  
</div>
