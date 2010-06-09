<form name="forumtopicform" action="" method="post">
  <fieldset class="center_box">
    <legend><?= __("Write Testimonial") ?></legend>
      <div class ="field_bigger">
        <h4><span class="required"> * </span> <?= __("Testimonial") ?>:</h4>
        <textarea rows="5" cols="50" name="body"></textarea>
      </div>
      <div class="button_position">
       <input type="submit" name="createtesti" value="<?= __("Submit") ?>" />
       <input type="hidden" name="form_handler" value="CreateTestimonialModule" />
     </div>
  </fieldset>
</form>
