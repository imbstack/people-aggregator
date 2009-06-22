<?php
$contents = $links['content_info'];
$inner_html = NULL;
if ($cnt = count($contents)) {
      //foreach ($contents as $content) {   
      for ($i = 0; $i < $cnt; $i++) {
        if ($i == 0) {
          $inner_html .= uihelper_generate_center_content($contents[$i]['content_id'], 0, 1);
        }
        else {
          $inner_html .= uihelper_generate_center_content($contents[$i]['content_id']);
        }
      }
   echo $inner_html;   
}
?>