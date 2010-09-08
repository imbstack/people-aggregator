<?php 
require_once "api/Permissions/PermissionsHandler.class.php";

	$permission_to_post = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => 'post_to_community'));
	
  $form_action = isset($form_action) ? $form_action : PA::$url ."/post_content.php";
  if ( $ccid > 0 ) {
  	$ccid_string = "&ccid=".$ccid;
  	$form_action .= "?ccid=$ccid";
		if (($group_access == ACCESS_PRIVATE) || ($group_reg == REG_INVITE)) {
			// content published in a private Group!
			// turn off full routing for private groups
			PA::$config->simple['omit_routing'] = true;
			// no routing to homepage either!
			$permission_to_post = false;
		}
  } else {
    $ccid_string = "";
  }
  
?>

<form name="formCreateContent" method="post" enctype="multipart/form-data" action="<?php echo $form_action;?>" onsubmit="return sanitize_input(this);">
<div id="content_post">
  <div class="steps">
    <ul>
    <?php if ($is_edit) { ?>
      <li><h3><?= __("edit your post") ?></h3></li>
    <? } else { ?>
      <li><h3><?= __("create your post") ?></h3></li>
    <? } ?>
    </ul>
    <ul id="create_blog_form">
        <li>
          <?php echo $center_content; ?>
        </li>
      </ul>

<?php if(!$is_edit) { 
	// turn off full routing for private groups
	
	if (!empty(PA::$config->simple['omit_routing'])) { 
		// give user a very simmple routing option here
		?>
    <ul id="routing">
        <li>
              <?php 
                
                if ($permission_to_post) { ?>
            <fieldset>
              <legend><?= __("Routing") ?></legend>
              <input type="checkbox" name="route_to_pa_home" value="1" 
              <?php 
              		if (!empty($_POST['route_to_pa_home'])) {
              			echo 'checked="checked"';
              		} ?> />
              	<?php 
              	  // To display network owner specified title on home page.
              	  $extra = unserialize($network_info->extra);
              	  $block_heading_net= @$extra['network_group_title'];
              	  $block_heading_net = ($block_heading_net) ? $block_heading_net : __("Community Blog");
              	  echo sprintf(__("Post to %s (On this networks home page)"), $block_heading_net) ?>
            </fieldset>
              <?php } ?>
          </li>
        </ul>
<?php } else {
				// show more fully featured routing
				if(!$is_edit) { ?>
      <ul>
      <li><p><?= __("You can route this post to one or many destinations with the selectors below.") ?>.</a></p></li>
      <li><p>*<?= __("Please note that routing does not post to private groups") ?></p></li>
    </ul>
    <ul id="routing">
        <li>
            <fieldset>
              <legend><?= __("Routing") ?></legend>
              <?php 
                $permission_to_post = PermissionsHandler::can_user(PA::$login_uid, array('permissions' => 'post_to_community'));
                if ($permission_to_post) { ?>
                 <input type="checkbox" name="route_to_pa_home" value="1" <?php if (@$_POST['route_to_pa_home'] == 1 ) { echo 'checked="checked"';}?> />
                 <?= __("Post to community Blog (On this networks home page)")?>
              <?php } ?>
              <?php
                $existing_route_targets_group = @$_POST['route_targets_group'];
                if (!$existing_route_targets_group) $existing_route_targets_group = array();
              if(count($user_groups) && !empty($_REQUEST['ccid'])) { ?>
              <div id="route_targets">
                <h4><label for="route_targets_groups"><?= __("Send to selected group blogs") ?> *</label></h4>
                    <select id="route_targets_groups" name="route_targets_group[]" multiple="multiple" onchange="deselect_others('route_targets_groups');">
<!--                    
                    <option value="-2">----Select none of my group blogs</option>
                    <option value="-1">----Select all of my group blogs</option>
-->                    
                    <option value=""> Select group blogs </option>
                    <?php 
                        $var_groups="";
                        for ($counter = 0; $counter < count($user_groups); $counter++) {
                          $grp_id = $user_groups[$counter]['gid'];
                          if( $grp_id == $ccid || in_array($grp_id, $existing_route_targets_group)) {
                              $selected = "selected";
                          } else {
                              $selected = "";
                             }
                             $var_groups = $var_groups.$user_groups[$counter]['gid'].',';
                       ?> 
                          <?php $permission_to_post = PermissionsHandler::can_group_user(PA::$login_uid, $user_groups[$counter]['gid'], array('permissions' => 'post_to_group'));
                          if ($permission_to_post) {?>
                            <option value="<?php echo $user_groups[$counter]['gid'];?>" <?php echo $selected?>><?php echo  chop_string(stripslashes($user_groups[$counter]['name']), NAME_LENGTH);?>
                            </option>
                          <?php } ?>  
                            <?php }?>     
                            <?php $var_groups = substr($var_groups, 0, -1);?>               
                        </select>
                        <input type="hidden" value="<?php echo $var_groups;?>" name="Allgroups"/>
             <?php /* } else {
                 echo "You have not joined any group right now.<a href='".PA::$url . PA_ROUTE_GROUPS ."'>Click Here</a> to join a group";
             } */?>
<!--                
                <p>For multiple selections hold down the &lt;Ctrl&gt;key PC or the</p>
                <p>&lt;Command&gt; key(Mac) while clicking the desired selections.</p>
                <p>* Post will not appear in moderated groups unless owner of group moderates them.</p>
-->                
              </div>
            <?php } ?>
            <?php
            if (empty(PA::$config->simple['omit_advacedprofile'])) {
            ?>
                <h4><label for="route_targets_groups"><?= __("Send to selected external blogs") ?> *</label></h4>
                <?php if($show_external_blogs) {
                  $existing_route_targets_external = @$_POST['route_targets_external'];
		  if (!$existing_route_targets_external) $existing_route_targets_external = array();
                ?>
                <select id="route_targets_external" name="route_targets_external[]" multiple="multiple" onchange="deselect_others('route_targets_external');">
                  <option value="-2">----Select none of my external blogs</option>
                  <option value="-1">----Select all of my external blogs</option>
                  <?php  $var_external_blogs = "";
                           for ($counter = 0; $counter < count($targets); $counter++) { 
                              $var_external_blogs = $var_external_blogs.$targets[$counter]['ID'].',';
                 ?>   <option value="<?php echo $targets[$counter]['ID'];?>"<?php if (in_array($targets[$counter]['ID'], $existing_route_targets_external)) echo " selected"; ?>><?php echo stripslashes($targets[$counter]['title']);?></option>
                <?php } ?>
               </select>
             <?php } else { echo $outputthis_error_mesg; } ?>
             <?php $var_external_blogs = substr(@$var_external_blogs, 0, -1);?>
             <input type="hidden" value="<?php echo $var_external_blogs;?>" name="Allexternal_blog"/>
       <?php } // end if not omit_advacedprofile?>
       
       <?php if($album_type != -1) {
	  $existing_route_targets_album = @$_POST['route_targets_album'];
	  if (!$existing_route_targets_album) $existing_route_targets_album = array();
       ?>
         <h4><label for="route_targets_groups"><?= __("Select albums in your media gallery to send to") ?> *</label></h4>
          <select id="route_targets_album" name="route_targets_album[]" multiple="multiple" onchange="deselect_others('route_targets_album');">    
           <option value="-2">----<?= __("Select none of my galleries") ?></option>
           <option value="-1">----<?= __("Select all of my galleries") ?></option>
              <?php if(count($user_albums)) {
                $var_album = "";
                for($counter = 0; $counter < count($user_albums); $counter++) {
              ?>
              <option value="<?php echo $user_albums[$counter]['collection_id']; ?>"<?php
if (in_array($user_albums[$counter]['collection_id'], $existing_route_targets_album)) echo " selected";
 ?>><?php echo chop_string($user_albums[$counter]['description'], 20);?></option>
              <?php $var_album = $var_album . $user_albums[$counter]['collection_id'] .',';?>
              <?php }
                } else {
                echo __("No Albums");
              }
              $var_album = substr($var_album, 0, -1);
            ?>
            </select>
            <input type="hidden" name="all_album" value="<?php echo $var_album;?>"/>  
            <?= __("Or, create a new album in your media gallery") ?> <input type="text" name="new_album" />    
         <?php } ?>
           </fieldset>
          
        </li>
      </ul>
         <?php } 
			}
} ?>      
      <ul>
      <li><h3>publish post</h3></li>
    </ul>
    <ul id="publish_post">
        <li>
           <input type="button" name="cancel" value="Cancel" onclick="JavaScript:   document.location='<?= PA::$url . PA_ROUTE_USER_PRIVATE  ?>';"/>
           <input type="hidden" name="save_publish_post" value="1" id="save_publish_post" />
           <input type="hidden" name="publish" value="<?php echo (!$is_edit) ? 'Publish Post' : 'Update Post'; ?>">
           <input type="submit" name="publish_post" value="<?php echo (!$is_edit) ? 'Publish Post' : 'Update Post'; ?>" />
        </li>
      </ul>
      </div>
    </div>
</form>    
<br clear="all" />
<br clear="all" />
<script>
$(document).ready(
  function() {
    // add onChange to form elements to have dirtyBit set
    $('input, select, textarea').change(
      function() {
        // alert("don't forget to save!");
        window.dirtyBit = true;
      }
    );

		// mark links inn the PostContentModule
		$("#PostContentModule a").addClass('internal');
    // add "You are leaving this section" alerts
    $("a").not('.internal').click(
      function() {
        if (window.dirtyBit) {
          // only do the are you sure if there have been changes to the form
          // onChange for form elements set the dirtyBit to ture
          var url = $(this).attr('href');
          var question = "<?=__("The post you are composing will be lost if you continue, are you sure you want to leave  without saving?")?>";
          var check = confirm(question);
          if (check == false) {
            return false;
          } 
          document.location.href = url;
        }
      }
    );
	}
);
</script>
