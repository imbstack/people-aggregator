<div class="module_flickr">	
  <ul>
  <?php if (count($pics)) {
           foreach ($pics as $pic) { ?>
            <li><a href="<?= htmlspecialchars($pic['url']) ?>" target="_blank"><img src="<?= htmlspecialchars($pic['75x75_url']) ?>" height="65" width="70" alt="flickr" /></a></li>
            <?php
          }
        } elseif (count($flickr_errors)) {
	  foreach ($flickr_errors as $err) {
	    echo "<li>".$err."</li>\n";
	  }
        } ?>
  </ul>
</div>