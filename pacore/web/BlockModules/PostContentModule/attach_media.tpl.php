<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<fieldset id='media'>
  <legend><a><b><?= __('Attach Media')?></b></a></legend>
  <div id="buttonbar">
  <ul>
  <li><a href="#" onclick="return show_upload('Images');"><?=__('Image')?></a></li>
  <li><a href="#" onclick="return show_upload('Videos');"><?=__('Video')?></a></li>
  </ul>
  </div>
  <div id="attach_media" style="display:none;"></div>
	</fieldset>
<br clear="all" />
<?php
$video_width = '400';
$video_height = '300';
?>
<script>
function showRequest(formData, jqForm, options) { 
	$(':input[name=submitbtn]', jqForm).remove();
	return true; 
}
function showResponse(responseText, statusText)  { 
	modal_hide();
	$('#modal_window').html('');
	$('#attach_media').html(responseText).show('slow');
}  

function video_success(video_id) {
		var flash = '<object width="<?=$video_width?>" height="<?=$video_height?>"><param name="movie"'
  		+' value="http://glued.in/Integration/play/'
  		+video_id
  		+'&preroll=true&postroll=true"></param><param name="allowfullscreen" value="true"></param><param name="allowscriptaccess" value="always"></param>'
  		+'<embed src="http://glued.in/Integration/play/'
  		+video_id
  		+'&preroll=true" allowfullscreen="true" allowscriptaccess="always"'
  		+' type="application/x-shockwave-flash"'
  		+' width="<?=$video_width?>" height="<?=$video_height?>"></embed></object>';	
  	var html = '<b>Your video is being processed.'
			+'It will show up and be playable in a few minutes.<br/>'
			+'It may show <i>Media not available</i> until then.</b><br/>'
			+'<textarea name="attach_media_html" id="attach_media_html">'
			+flash
			+'</textarea>';

	alert('Successfully uploaded video.');
	modal_hide();
	$('#attach_media').html(html).show('slow');
}
function video_failure(msg) {
	alert(msg);
	modal_hide();
	$('#attach_media').html('<b>'+msg+'</b>').show('slow');
}

function show_upload(typeStr) {
	var now = new Date().getTime();
	var options = { 
		target: '#attach_media',  
		beforeSubmit: showRequest,
		success: showResponse,
		url: '<?=PA::$url."/ajax/upload_media.php"?>?type='+typeStr+'&uid=<?=PA::$login_uid?>'
	}; 

	$.post(
		'<?=PA::$url?>/ajax/upload_media_form.php?t='+now,
		{'type': typeStr<?php 
			if ($ccid > 0) { 
			?>,
			'gid':<?=$ccid?>
			<? } ?>
			},
			function(data) {
				modal_show('Attach '+typeStr, data);
				$('#modal_window form').submit(function() { 
					$(this).ajaxSubmit(options); 
					return false; 
				}); 
			});
	return false;
}
</script>
