<div>
  <ul>
    <li>
	    <? if (!$code && !$msg) { ?>
		    <?= __("Unknown error.") ?>
      <? } else { ?>
		  Code <?=$code?>: <b><?=$msg?></b>
	    <? } ?>
    </li>
    <li>
      <?= __("Detailed information has been written into the error log.") ?>
    </li>
  </ul>
</div>