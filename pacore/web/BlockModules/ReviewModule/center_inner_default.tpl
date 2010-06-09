<!-- temporary style until we get this separated out properly -->
<style>
	.review {
		width: 400px;
		border: 1px black solid;
		margin-bottom: 1em;
	}
</style>

<p>Reviews from people on item type <?= $mod->subject_type ?>, id <?= $mod->subject_id ?>:</p>

<? if (empty($mod->reviews)) { ?>
  <p>No reviews yet; why not add one?</p>
<? } else {
  foreach ($mod->reviews as $review) { ?>
    <div class="review">
      <div class="review-head">Review by <?= $review->author['login_name'] ?>:</div>
      <div class="review-body"><?= nl2br($review->body) ?></div>
      <div class="review-foot">Posted at <?= $review->created ?></div>
    </div>
  <? }
} ?>

<p>Add review:</p>

<?= $mod->start_form("add_review_form", "post") ?>
<?= $mod->input_tag("hidden", "op", "add_review") ?>
<?= $mod->input_tag("hidden", "subject_type", $mod->subject_type) ?>
<?= $mod->input_tag("hidden", "subject_id", $mod->subject_id) ?>
<?= $mod->textarea_tag("body", "") ?>
<p><?= $mod->submit_tag("Save") ?></p>

</form>