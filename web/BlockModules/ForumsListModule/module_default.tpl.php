  <div class="module_icon_list" id="list_members">
    <ul class="members">
      <?php
        for ($counter = 0; $counter < count($boards_info); $counter++) {
          $class = (( $counter%2 ) == 0) ? 'class="color"': NULL;
      ?>  
      
      <li <?php echo $class?>>
        &nbsp;<a href="<?= $boards_info[$counter]['url'] ?>"><?= $boards_info[$counter]['title'] ?></a>
        <br />&nbsp;<?= __('type: ') . $boards_info[$counter]['type'] . " " . __('board') ?>
      </li>
      <?php 
        }
      ?>          
    </ul>         
  </div>
