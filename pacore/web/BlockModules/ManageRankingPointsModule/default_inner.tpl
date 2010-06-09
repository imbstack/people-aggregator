<?php
?>

<form method="post" action="<?= PA_ROUTE_RANKING_POINTS ?>?action=UpdateActivities" enctype="multipart/form-data">
<fieldset class="center_box">

<?php if(is_array($activities)) : ?>

<table border="0" id="activities_table">
  <thead>
    <tr>
      <th scope=col><?=__('Id')?></th>
      <th scope=col><?=__('Title')?><span class="required"> *</span></th>
      <th scope=col><?=__('Type')?><span class="required"> *</span></th>
      <th scope=col><?=__('Description')?></th>
      <th scope=col><?=__('Points')?><span class="required"> * </span></th>
      <th scope=col></th>
<!--
      <th scope=col><?=__('Delete')?></th>
-->
    </tr>
  </thead>
  <tbody>
  <?php foreach ($activities as $activity) :?>
   <?php if($activity['id'] <= 19) : ?>
    <tr>
      <td><?=$activity['id']?>.<input type="hidden" name="form_data[<?=$activity['id']?>][id]" id="form_data_<?=$activity['id']?>_id" value="<?=$activity['id']?>" /></td>
      <td><input type="text" class="text input_long" name="form_data[<?=$activity['id']?>][title]" id="form_data_<?=$activity['id']?>_title" value="<?=$activity['title']?>" /></td>
      <td><input type="text" class="text input_medium_readonly" name="form_data[<?=$activity['id']?>][type]" id="form_data_<?=$activity['id']?>_type" value="<?=$activity['type']?>" readonly /></td>
      <td><textarea cols=20 rows=2 class="activity_description" name="form_data[<?=$activity['id']?>][description]" id="form_data_<?=$activity['id']?>_description" ><?=$activity['description']?></textarea></td>
      <td><input type="text" class="text input_short" name="form_data[<?=$activity['id']?>][points]" id="form_data_<?=$activity['id']?>_points" value="<?=$activity['points']?>" /></td>
    </tr>
   <?php else : ?>
    <tr>
      <td><?=$activity['id']?>.<input type="hidden" name="form_data[<?=$activity['id']?>][id]" id="form_data_<?=$activity['id']?>_id" value="<?=$activity['id']?>" /></td>
      <td><input type="text" class="text input_long" name="form_data[<?=$activity['id']?>][title]" id="form_data_<?=$activity['id']?>_title" value="<?=$activity['title']?>" /></td>
      <td><input type="text" class="text input_medium" name="form_data[<?=$activity['id']?>][type]" id="form_data_<?=$activity['id']?>_type" value="<?=$activity['type']?>" /></td>
      <td><textarea cols=20 rows=2 class="activity_description" name="form_data[<?=$activity['id']?>][description]" id="form_data_<?=$activity['id']?>_description" ><?=$activity['description']?></textarea></td>
      <td><input type="text" class="text input_short" name="form_data[<?=$activity['id']?>][points]" id="form_data_<?=$activity['id']?>_points" value="<?=$activity['points']?>" /></td>
      <td><input type="checkbox" class="text" name="form_data[<?=$activity['id']?>][selected]" id="form_data_<?=$activity['id']?>_selected" /></td>
    </tr>
   <?php endif; ?>
  <?php endforeach; ?>
<!--
    <tr>
      <td colspan="5"><b><?=__('Edit activity types or add a new type below')?></b></td>
    <tr>
    <tr>
      <td><?=__('New')?></td>
      <td><input type="text" class="text input_long" name="form_data[new][title]" id="form_data_new_title" value="" /></td>
      <td><input type="text" class="text input_medium" name="form_data[new][type]" id="form_data_new_type" value="" /></td>
      <td><textarea cols=20 rows=2 class="activity_description" name="form_data[new][description]" id="form_data_new_description" ></textarea></td>
      <td><input type="text" class="text input_short" name="form_data[new][points]" id="form_data_new_points" value="" /></td>
    </tr>
-->
  </tbody>
</table>

<div class="button_position">
  <input type="submit" name="submit_save" value="Save" />
<!--
  <input type="submit" name="submit_delete" value="Delete" />
-->
</div>

<?php else : ?>

<div class="field_text">
  No avtivities defined.
</div>

<?php endif; ?>

</fieldset>
</form>
