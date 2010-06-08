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
<?php

// ImageResize wrappers for web/* code.

require_once "api/ImageResize/ImageResize.php";

function uihelper_preprocess_pic_path($pic) {
    $pic = trim($pic);
//    if (defined("NEW_STORAGE") && preg_match("|^pa://|", $pic)) {
    if (preg_match("|^pa://|", $pic)) {
	$pic = Storage::get($pic);
    } else if (preg_match("|^files/|", $pic)) {
	if (defined("NEW_STORAGE")) throw new PAException(INVALID_ID, "Old code is prepending files/ to an image path");
    } else {
	$pic = "files/$pic";
    }
    return $pic;
}

// given a User object, a Storage URL or a path to a user's image
// (relative to /web/files), and a bounding box, resize the image to
// fit inside the bounding box and return an <img> tag that will
// display the image.
function uihelper_resize_mk_user_img($user_or_picture, $max_x, $max_y, $extra_attrs="") {

// checking that whether this image is Animated or not if this image is animated than make it still image

    $pic = ($user_or_picture instanceof User) ? $user_or_picture->picture : $user_or_picture;

    return ImageResize::resize_mk_img("web", PA::$url, "files/rsz", $max_x, $max_y, uihelper_preprocess_pic_path($pic), DEFAULT_USER_PHOTO_REL, NULL, $extra_attrs, RESIZE_CROP);
}

// Resize an image from web/files or Storage and return an <img> tag
// that will display the image.
function uihelper_resize_mk_img($pic, // path to image (relative to /web/files)
				$max_x, $max_y, // bounding box for output image
				$alternate=NULL, // path to an alternate image, if the $picture file does not exist
				$extra_attrs='alt="PA"', // other attributes to include in the <img> tag
				$opts=RESIZE_CROP, // see ImageResize::resize_mk_img for the meaning of $opts
				$override_base_url=NULL // specify an alternate base url (e.g. "%BASE_URL%" if you want to save something in the DB and have it fixed up later)
				) {
    if (preg_match("|^http://|", $pic)) {
	// we've been passed a URL rather than a Storage ID or local path.
	// one day we should download URLs and resize them properly, but
	// for the moment we just fake it:
	return '<img src="'.$pic.'" width="'.$max_x.'" height="'.$max_y.'" '.$extra_attrs.' />';
    }
    return ImageResize::resize_mk_img("web", $override_base_url ? $override_base_url : PA::$url, "files/rsz", $max_x, $max_y, uihelper_preprocess_pic_path($pic), $alternate, FALSE, $extra_attrs, $opts);

}

// Resize an image from somewhere directly iweb/files or Storage and return an <img> tag
// that will display the image.
function uihelper_resize_mk_img_static($pic, // path to image (relative to /web)
				$max_x, $max_y, // bounding box for output image
				$alternate=NULL, // path to an alternate image, if the $picture file does not exist
				$extra_attrs='alt="PA"', // other attributes to include in the <img> tag
				$opts=RESIZE_CROP, // see ImageResize::resize_mk_img for the meaning of $opts
				$override_base_url=NULL // specify an alternate base url (e.g. "%BASE_URL%" if you want to save something in the DB and have it fixed up later)
				) {

    if (defined("NEW_STORAGE")) {
	    $pic = Storage::get_or_make_static($pic);
    } else if (preg_match("|^files/|", $pic)) {
	     if (defined("NEW_STORAGE")) throw new PAException(INVALID_ID, "Old code is prepending files/ to an image path");
    } else {
       $pic = "files/$pic";
    }

    return ImageResize::resize_mk_img("web", $override_base_url ? $override_base_url : PA::$url, "files/rsz", $max_x, $max_y, $pic, $alternate, FALSE, $extra_attrs, $opts);

}

// given a path to an image (relative to /web) and a bounding box,
// resize the image and return an array of info about the resized
// image (keys: url, width, height, perhaps more).  see
// ImageResize::resize_mk_img for the meaning of $opts.
function uihelper_resize_img($pic, $max_x, $max_y, $alternate=NULL, $extra_attrs='alt="PA"', $opts=RESIZE_CROP) {

    $ret = ImageResize::resize_img("web", PA::$url, "files/rsz", $max_x, $max_y, uihelper_preprocess_pic_path($pic), $alternate, FALSE, $opts);
    if (!isset($ret['url'])) $ret['url'] = PA::$url .'/'.$ret['final_path'];
    return $ret;
}

?>
