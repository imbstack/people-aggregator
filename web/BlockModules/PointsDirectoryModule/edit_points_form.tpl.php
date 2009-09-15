<?php
 require_once "web/includes/classes/xHtml.class.php";
 $rating_points = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
 $back_url = PA::$url . PA_ROUTE_POINTS_DIRECTORY . "?uid=" . PA::$login_uid;
?>
<h1><?= $sub_title ?></h1>
<div id="PointsDirectoryModule">
 <?php if(!empty($message)) : ?>
   <div class="points_message<?=(($error) ? '_error' : '')?>"><?=$message?></div>
 <?php endif; ?>
 <form enctype="multipart/form-data" name="edit_points_form" id="edit_points_form" method="POST">
 <fieldset class="center_box">
    <div class="field">
      <h4><span class="required"> * </span><label for="form_data_entity_name"><?=__("Title")?></label></h4>
      <input type="text" name="form_data[entity_name]" id="form_data_entity_name" value="<?=(!empty($item['entity_name'])) ? $item['entity_name'] : null ?>" class="text short error" maxlength="45" />
    </div>

    <div class="field">
      <h4><label for="form_data_description"><?=__("Description")?></label></h4>
      <textarea name="form_data[description]" class="text short" id="form_data_description"><?=(!empty($item['description'])) ? $item['description'] : null ?></textarea>
    </div>

    <div class="field">
      <h4><span class="required"> * </span><label for="form_data_category"><?=__("Category")?></label></h4>
      <div  id="edit_category">
      <?php if(count($categories) > 0) : ?>
        <?= xHtml::selectTag(array_combine(array_values($categories), array_values($categories)),
                                 array('name'=> 'form_data[category]', 'id' => 'form_data_category'),
                                 (!empty($item['category'])) ? $item['category'] : null) ?>&nbsp;
        <a href="javascript:" id="switch_categ"><?=__("or click here to add a new")?></a>
      <?php else: ?>
        <input type="text" class="text short" name="form_data[category]" id="form_data_category" value="" />
      <?php endif; ?>
      </div>
    </div>

    <div class="field">
      <h4><span class="required"> * </span><label for="form_data_rating"><?=__("Rating Points")?></label></h4>
      <?= xHtml::selectTag(array_combine(array_values($rating_points), array_values($rating_points)),
                               array('name'=> 'form_data[rating]', 'id' => 'form_data_rating'),
                               (!empty($item['rating'])) ? $item['rating'] : null) ?>
    </div>

    <div class="field">
      <h4><label for="form_data_place"><?=__("Place")?></label></h4>
      <input type="text" name="form_data[place]" id="form_data_place" value="<?=(!empty($item['place'])) ? $item['place'] : null ?>" class="text short" maxlength="45" />
    </div>

    <div class="field_big">
      <h4><label for="related_media"><?=__("Related Media")?></label></h4>
      <div id="related_media" style="height:116px; display: block">
        <div id="media_icon" style="width:40%; clear: both; float: left;">
          <?php if(!empty($item['media_icon'])) : ?>
            <?= $item['media_icon'] ?>
          <?php else: ?>
            <?= uihelper_resize_mk_img(null, 86, 92, 'images/default_image.png', "", RESIZE_CROP) ?>
          <?php endif; ?>
        </div>
        <div style="float: left; position: relative;">
        <?=__("Click here to upload") ?>
        <a class="button_silver" href="#" onclick="return show_upload('Images');"><?=__('Image')?></a>
        <a class="button_silver" href="#" onclick="return show_upload('Audios');"><?=__('Audio')?></a>
        <a class="button_silver" href="#" onclick="return show_upload('Videos');"><?=__('Video')?></a>
        </div>
        <div id="attach_media" style="display:none;">
          <input type="hidden" name="media[cid]" id="media_cid" value="<?=(!empty($item['media_cid'])) ? $item['media_cid'] : null ?>" />
          <input type="hidden" name="media[type]" id="media_type" value="<?=(!empty($item['media_type'])) ? $item['media_type'] : null ?>" />
          <input type="hidden" name="media[file]" id="media_file" value="<?=(!empty($item['media_file'])) ? $item['media_file'] : null ?>" />
        </div>
      </div>
    </div>

   <input type="hidden" name="form_data[user_id]" id="form_data_user_id" value="<?= PA::$login_uid?>" />
   <input type="hidden" name="form_data[entity_id]" id="form_data_entity_id" value="<?=(!empty($item['entity_id'])) ? $item['entity_id'] : null ?>" />
   <input type="hidden" name="uid" id="uid" value="<?=PA::$login_uid?>" />
   <input type="hidden" name="fid" id="fid" value="<?=$fid?>" />
   <input type="hidden" name="form_data[network_id]" id="form_data_network_id" value="<?=(!empty($item['network_id'])) ? $item['network_id'] : PA::$network_info->network_id ?>" />
   <input type="hidden" name="form_data[family_id]" id="form_data_family_id" value="<?=(!empty($item['family_id'])) ? $item['family_id'] : $fid ?>" />
   <input type="hidden" name="form_data[created]" id="form_data_created" value="<?=(!empty($item['created'])) ? $item['created'] : PA::date(time(), 'short') ?>" />
   <input type="hidden" name="form_data[updated]" id="form_data_updated" value="<?= PA::date(time(), 'short') ?>" />
   <input type="hidden" name="faction" id="faction" value="savePoints" />
   <div class="field" style="height:32px">
    <div id="buttonbar" style="float: right;">
      <br />
      <input type="button" name="back_btn" id="back_btn" value="Back" onclick="javascript:history.back()" />
      <input type="submit" name="submit" id="submit_form" value="Save" />
    </div>
   </div>
 </fieldset>
 </form>
</div>
