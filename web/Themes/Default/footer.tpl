<?php
  global $network_info;
  require_once "api/FooterLink/FooterLink.php";

  $footer_links = FooterLink::get(array('is_active' => ACTIVE));
  $count_footer_links = count($footer_links);
  $link_html = NULL;
  for ($counter = 0; $counter < $count_footer_links; $counter++) {
    $extra_data = unserialize($footer_links[$counter]->extra);
    $target = NULL;
    if ($extra_data['is_external'] == 1) {
      $target = "target=\"_blank\"";
    }
    $link_html .= '<li><a href="'.$footer_links[$counter]->url.'" '.$target.'>'.$footer_links[$counter]->caption.'<a></li> | ';
  }
  $link_html = substr($link_html, 0, -2);
?>
<div style="position: static;" id="footer">
  <ul>
    <?php echo $link_html;?>
  </ul>
    <?= sprintf(__("&copy; %s Broadband Mechanics, Inc."), date('Y')) ?>
    [<?= get_svn_version() ?>]
    <!--**timing**-->
</div>
