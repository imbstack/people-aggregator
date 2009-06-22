<div id="image_gallery_upload" class="">
<?php
$TekMedia = new TekMedia();
$form_key =  $TekMedia->generate_form_key(@$gid);
$redirection_path = PA::$url."/ajax/save_tekmedia.php";

// $use_tek_form = true;

if (@$use_tek_form) {
	$iframe_src = "http://www.glued.in/Integration/index.php?form_key=$form_key";
} else {
	// $form_url = PA::$url."/ajax/upload_video_form.php";
	// need this to live on a public access server!
	$form_url = "http://pre8staging.dev.vm.broadbandmechanics.com/ajax/upload_video_form.php";
	if (!empty($_REQUEST['gid'])) {
		$form_url .= "?gid=".$_REQUEST['gid'];
	}

	$iframe_src = PA::$tekmedia_iframe_form_path."?form_key=$form_key&get_form=$form_url";
}

$iframe_src .= "&redirection_path=$redirection_path&redirection_target=self";

?>
<div id="framiac" />
	<iframe id="tekmiframe" src="<?=$iframe_src?>"  width="600" height="400" scrolling="no" frameborder="0"></iframe>

</div>
