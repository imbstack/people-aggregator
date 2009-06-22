<?php global $current_theme_path;?>
<div class="module_browse_groups">
  <?php
    if (!empty($profile_feeds)) {
  ?>
  <ul>
  <?php
      foreach ($profile_feeds as $feed) {
  ?>

    <li class="color"><a href="<?php echo $feed['blog_url']?>" target="_blank"><?php echo $feed['blog_title']?></a>
      <?php
        if (!empty($feed['links'])) {
      ?>
      <ul>
      <?php
          foreach ($feed['links'] as $link) {
      ?>
        <?php if(is_string($link)) : // that means feed Error message! ?>
           <li><?php echo $link?></li>
        <?php else : ?>
          <li><a href="<?php echo $link->original_url?>"  target="_blank"><?php echo $link->title?></a></li>
        <?php endif; ?>
      <?php
          }// end inner foreach
      ?>
      </ul>
      <?php
        }//end inner if
      ?>
    </li>
    <?php
        }//end outer foreach
    ?>
    </ul>
    <?php
      } //end outer if
    ?>
</div>