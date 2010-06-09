<link rel="stylesheet" type="text/css" href="/Themes/Default/network.css" />
<style>body {background:#fff;}</style>

<script type="text/javascript" language="javascript" src="/Themes/Default/javascript/jquery.lite.js"></script>
<script type="text/javascript" language="javascript" src="/Themes/Default/javascript/forms.js"></script>

<script type="text/javascript">
var ajax_ready;
var progressbar;
var req_periods = 1000*2;
function check_blank_values(url) {
	if (!validate_title()) {
		return false;
	}
	// alert('Now uploading you video. This may take a few minutes, depending on the size of your file and your connection speeed. Please be patient.');
  ajax_ready = false;
  $('#progressbar').html($('#uploader_img').html()).show();
  progressbar = window.setInterval("show_progress('"+ url +"' )", req_periods);
}

function show_progress(complete_url) {    
  var arr_url=complete_url.split("?");
  var url=arr_url[0];
  var pars = arr_url[1];  
  $.post( url, pars, updatePrigressbar );
}

function updatePrigressbar(data) {
  $('#progressbar').html(data);
  if(data == 'complete') {
  	window.clearTimeout(progressbar);
    $('#progressbar').html('Uploading completed. Please wait, the video file is being further processed.<br/> This may take a few mins...');    
  }  
}

function validate_title() {
	// just make sure we pass a title
	var file_path = $('#upload').fieldValue()[0];
	var file_name = '';
	var m = file_path.match(/(.*)[\/\\]([^\/\\]+\.\w+)$/);
	if (m) { file_name = m[2]; }
	else if (file_path != '') { 
		file_name = file_path; 
	} else {
		alert('Please select a file.');
		return false;
	}
	var title = $('#upload_title').fieldValue()[0];
	if (title == '') {
		$('#upload_title').val(file_name);
	}
	return true;
}
</script>


<div id="image_gal1lery_upload" style="width:500px;margin:20;height:auto; float:left;">
  <fieldset>
    You can upload a video file. (Maximum size 100MB).
    <div id="image_gallery">
     <div id="block" class="block_video_form">
        <div class="upload-vids">
           <span><label for="select file">Select a file to upload</label></span>
           <input name="upload" id="upload" type="file" class="text long" value="" style="border:solid 1px #ccc; font-size:11px; width: 300px"/>
        </div>
          
       <div class="upload-vids">
          <span><label for="image title">Video Title</label></span>
          <input type="text" name="title" value="" class="text" id="upload_title" maxlength="90" style="border:solid 1px #ccc; font-size:11px; width: 300px" />
        </div>
    </div>
    </div>
    <div class="button" style="padding-bottom:10px; float:left; ">
      <input type="submit" class="button-submit" id="uploadButton" name="submit_video" value="Start Upload" />
    </div>
	<input name="output" value="v" type="hidden">
    <?php if(!empty($_GET['gid'])) { ?>
      <input type="hidden" name="group_id" value="<?php echo $_GET['gid'];?>" />
    <?php } ?>
  </fieldset>
</div>
