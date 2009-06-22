    <div class="body">
        <div class="left-body home-page-background">

         <div class="header-parent-group">
         <?php echo $header;?>
         </div>

         <div class="left-parent">
         <?php
         /*if ( !empty($array_left_modules) && sizeof($array_left_modules) >0 )
         {
           foreach ( $array_left_modules as $left_module )
           {
              echo $left_module;
           }
         }*/
         ?><br>
         </div>
            <div class="middle-parent">
            <div class="middle">
            <?php
            if ( isset($array_middle_modules) and (count($array_middle_modules) > 0) )
            {
              foreach ( $array_middle_modules as $middle_module )
              {
                echo $middle_module;
              }
            }
            ?>
            </div>
            </div>
            
            <div class="right-parent">
            <?php
//               if ( !empty($array_right_modules) && sizeof($array_right_modules) >0 )
//               {
//                 foreach ( $array_right_modules as $right_module )
//                 {
//                   echo $right_module;
//                 }
//               }
            ?>
            </div>
            

            <?php echo $footer;?>

        </div>

        <div class="right-body">
            <img src="<?= $current_theme_path?>/images/right-piller.gif" alt="PA" /></div>
    </div>