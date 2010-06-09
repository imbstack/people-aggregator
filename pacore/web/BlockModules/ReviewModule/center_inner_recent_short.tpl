<p>Recent Reviews</p>

<? if (empty($mod->reviews)) { ?>
  <p>No reviews yet.</p>
<? } else foreach ($mod->reviews as $review) { ?>
  <div class="review">
    Review by <?= $review->author["login_name"] ?>: <?= $review->body ?>
  </div>
<? } ?>
