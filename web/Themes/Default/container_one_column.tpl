<?php echo $top_navigation_bar;?>
<div id="container" <?php echo $outer_class?>>
<?php echo $header;?>
<div id="bg_blog_big"></div>
  <div id="content">  
  <div id="col_d">
    <?php
      if ( isset($array_middle_modules) and (count( $array_middle_modules ) > 0 ) ) {
        foreach ( $array_middle_modules as $middle_module ) {
          echo $middle_module;
        }
      }
    ?>
    </div>
  </div>

  <?php 
      echo $footer;
  ?>
</div> 
</body>
</html>