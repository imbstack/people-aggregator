<div class="module_html_text"><?
  foreach ($links as $k => $v) {
    ?><a href="<?= htmlspecialchars($v['url']) ?>" target="_blank"><?= uihelper_resize_mk_img($v['file_name'], 198, 135) ?></a>
    <?= htmlspecialchars(@$v['title']) ?><br />
    <? if ($v['url']) { ?><a href="<?= htmlspecialchars($v['url']) ?>" target="_blank"><?= __("Click here to watch the video") ?></a><? }
  }
?></div>
