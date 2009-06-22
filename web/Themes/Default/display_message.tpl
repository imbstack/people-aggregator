<script>
$(document).ready( function() {
	var msgDiv = document.getElementById("display_message");
	var height = msgDiv.clientHeight;	
	$(msgDiv).before('<iframe style="height:'+height+'px;" src="about:blank" id ="display_message_iframe" scrolling="no" frameborder="0"></iframe>');
});
</script>
<div id="display_message">
  <ul>
    <?php 
      if (is_array($message)) {
        foreach ($message as $msg) {
    ?>
          <li><?php echo $msg?></li>
    <?php
        }
      } else {
    ?>
        <li><?php echo $message?> </li>
    <?php
      }
    ?>
    <li> 
      <input type="button" id="confirm_btn" value="Ok" onclick="javascript:show_hide_network_categories('display_message','arrow_close_1'); return false;" />
    </li>
  </ul>
</div>
<br /><?php /* we need some HTML element here, or the iframe above will cause a rendering error */ ?>