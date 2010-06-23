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

require_once dirname(__FILE__)."/../../config.inc";
require_once "api/PAException/PAException.php";
require_once "api/Logger/Logger.php";
require_once "web/includes/classes/GifSplit.php";

# Constants
define('MAX_WIDTH', 150);
define('MAX_HEIGHT', 150);

# Constants for desktop images
define('MAX_WIDTH_DESKTOP', 200);
define('MAX_HEIGHT_DESKTOP', 100);

# Constants for changing image size
define('IMAGE_MEDIUM', 4);
define('IMAGE_VERYSMALL', 0.5);
define('IMAGE_THUMBNAIL', 0.3);
define('IMAGE_DESKTOP', 4);

# Constants for resized image path
define('SMALL', "web/files/smallimages/small_");
define('MEDIUM', "web/files/mediumimages/medium_");
define('VERY_SMALL', "web/files/verysmallimages/verysmall_");
define('THUMBNAIL', "web/files/thumbnails/thumbnail_");
define('DESKTOP', "web/files/desktopimages/desktop_");
define('SQUARE_IMAGES', "web/files/square");

# Constants for resizing operations
define("RESIZE_STRETCH", 1); // ignore aspect ratio, just stretch to the given size (output image will be exactly the desired size)
define("RESIZE_CROP", 2); // scale the image to be larger than the output size, then crop out the middle (output image will be exactly the desired size)
define("RESIZE_CROP_NO_EXPAND", 3); // as with RESIZE_CROP, but just crop to fit, don't scale up.
define("RESIZE_CROP_NO_SCALE", 6); // as with RESIZE_CROP but don't scale at all; just cut the center out of the image if it's too big.
define("RESIZE_FIT", 4); // scale the image to fit insize the output size (output image may be smaller than the desired size)
define("RESIZE_FIT_NO_EXPAND", 5); // as with RESIZE_FIT, but only ever scale down.

// Image type constants - FIXME: maybe already defined in PHP5?
if (@(IMAGETYPE_GIF != 1))
{
  define("IMAGETYPE_GIF", 1);
  define("IMAGETYPE_JPEG", 2);
  define("IMAGETYPE_PNG", 3);
}

/**
* Class Images
* @author Tekriti Software
*/
class ImageResize {

  static $skip_gd = FALSE; // set to TRUE to *not* use GD
  static $skip_magick = FALSE; // set to TRUE to *not* use ImageMagick

  private static $resize_type_prefixes = array(
  RESIZE_STRETCH => "stretch",
  RESIZE_CROP => "crop",
  RESIZE_CROP_NO_EXPAND => "crop_s",
  RESIZE_CROP_NO_SCALE => "crop_x",
  RESIZE_FIT => "fit",
  RESIZE_FIT_NO_EXPAND => "fit_s",
  );

  /**
  *  resize the images
  * @param $iamge_path the path of an image to be resized
    DEPRECATED
  */
  private static function resize_image($image_path, $desktop_image=0) {
    Logger::log("Enter: ImageResize::resize_image");
    // global var $path_prefix has been removed - please, use PA::$path static variable
    $img = null;
    $path = explode('/', $image_path);
    $imagename = end($path);
    $path1 = explode('.', $image_path);
    $ext = strtolower(end($path1));

    // To Check whether GD is installed or not.
    if(function_exists("gd_info") && function_exists("imagecreatefromjpeg") && $ext != 'bmp') {
      // Get image size and scale ratio
      if ($ext == 'jpg' || $ext == 'jpeg') {
        $img = @imagecreatefromjpeg($image_path);
      }
      elseif ($ext == 'png') {
        $img = @imagecreatefrompng($image_path);
      }
      elseif ($ext == 'gif') {
        $img = @imagecreatefromgif($image_path);
      }
      if ($desktop_image==1) {
        if ($img) {
          $width = imagesx($img);
          $height = imagesy($img);
          $scale = min(MAX_WIDTH_DESKTOP/$width, MAX_HEIGHT_DESKTOP/$height);
        }
      }
      else {
        if ($img) {
          $width = imagesx($img);
          $height = imagesy($img);
          $scale = min(MAX_WIDTH/$width, MAX_HEIGHT/$height);
        }
      }
      if ($scale < 1) {

        if ($desktop_image==1) {
          // to make desktop images
          if ($img) {
            // If the image is larger than the max shrink it
            $new_width = floor($scale*$width*IMAGE_DESKTOP);
            $new_height = floor($scale*$height*IMAGE_DESKTOP);
            $tmp_img = @imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

          }
          if ($ext == 'jpg') {
            @imagejpeg($tmp_img, DESKTOP."$imagename");
          }
          else {
            $method = "image$ext";
            @$method($tmp_img, DESKTOP."$imagename");
          }
          @imagedestroy($tmp_img);
        }
        else {
          if ($img) {
            // If the image is larger than the max shrink it
            $new_width = floor($scale*$width);
            $new_height = floor($scale*$height);

            // Create a new temporary image
            $tmp_img = @imagecreatetruecolor($new_width, $new_height);

            // Copy and resize old image into new image
            @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

          }

          // Save the image
          if ($ext == 'jpg') {
            @imagejpeg($tmp_img, SMALL."$imagename");
          }
          else {

            $method = "image$ext";
            @$method($tmp_img, SMALL."$imagename");
          }
          @imagedestroy($tmp_img);
          // to make medium size images
          if ($img) {
            // If the image is larger than the max shrink it
            $new_width = floor($scale*$width*IMAGE_MEDIUM);
            $new_height = floor($scale*$height*IMAGE_MEDIUM);
            $tmp_img = @imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

          }
          if ($ext == 'jpg') {
            @imagejpeg($tmp_img, MEDIUM."$imagename");
          }
          else {
            $method = "image$ext";
            @$method($tmp_img, MEDIUM."$imagename");
          }
          @imagedestroy($tmp_img);

          // to make very small images
          if ($img) {
            // If the image is larger than the max shrink it
            $new_width = floor($scale*$width*IMAGE_VERYSMALL);
            $new_height = floor($scale*$height*IMAGE_VERYSMALL);
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

          }
          if ($ext == 'jpg') {
            @imagejpeg($tmp_img, VERY_SMALL."$imagename");
          }
          else {
            $method = "image$ext";
            @$method($tmp_img, VERY_SMALL."$imagename");
          }
          @imagedestroy($tmp_img);

          // to make thumbnail of images
          if ($img) {
            // If the image is larger than the max shrink it
            $new_width = floor($scale*$width*IMAGE_THUMBNAIL);
            $new_height = floor($scale*$height*IMAGE_THUMBNAIL);
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

          }
          if ($ext == 'jpg') {
            @imagejpeg($tmp_img, THUMBNAIL."$imagename");
          }
          else {
            $method = "image$ext";
            @$method($tmp_img, THUMBNAIL."$imagename");
          }
          @imagedestroy($tmp_img);
        }
      }
    }
    Logger::log("Exit: ImageResize::resize_image");
  }

  /**
  * to get the images
  * @return $array_of_images having path for all type of images
  *
    DEPRECATED
  * NOTE: you should probably be using uihelper_resize_mk_img() or
  * uihelper_resize_mk_user_img() to resize images.
  */
  public static function get_images($image_path, $desktop_image=0) {
    Logger::log("Enter: ImageResize::get_images");
    // global var $path_prefix has been removed - please, use PA::$path static variable
    $array_of_images[] = array();
    $path = explode('/', $image_path);
    $image_name = end($path);
    $path1 = explode('.', $image_path);
    $ext = strtolower(end($path1));

    if ($desktop_image==1) {
      $array_of_images['original_image'] = "$image_path";
      $d_path = DESKTOP."$image_name";
      if (!file_exists("$d_path")) {
        ImageResize::resize_image($image_path,  $desktop_image);
      }
      $array_of_images['desktop_image'] = $d_path;
      $t = $array_of_images['desktop_image'];
      //print "$image_path(($desktop_image)) $t"; exit;
    }
    else {
      $m_path = MEDIUM."$image_name";
      $s_path = SMALL."$image_name";
      $vs_path = VERY_SMALL."$image_name";
      $t_path = THUMBNAIL."$image_name";

      $array_of_images['original_image'] = "$image_path";
      if (!file_exists("$m_path") || !file_exists("$s_path") || !file_exists("$vs_path") || !file_exists("$t_path")) {
        ImageResize::resize_image($image_path);
      }
      $array_of_images['medium_image'] = $m_path;
      $array_of_images['small_image'] = $s_path;
      $array_of_images['verysmall_image'] = $vs_path;
      $array_of_images['thumbnail_image'] = $t_path;
    }

    return $array_of_images;
    Logger::log("Exit: ImageResize::get_images");
  }

  /* Given a root path and URL, and relative paths to a picture and an
  * alternate picture, resizes the picture if it exists, or otherwise
  * resizes the alternate picture.  Then generates an <img> tag
  * pointing to whichever it could resize.
  *
  * The image is downsized to fit within the provided rectangle
  * ($max_x, $max_y).  If it is already small enough to fit, it is
  * not modified.
  *
  * IMPORTANT NOTE: You are probably better off using
  * uihelper_resize_mk_img() to resize general images (e.g. photos in
  * an album) or uihelper_resize_mk_user_img() to resize user images
  * (as it will automatically specify the default user image as an
  * alternate).
  *
  * Usage:
  *
  * $img_tag = ImageResize::resize_mk_img("web",
  * PA::$url, "files/resized", 75, 50, "files/picture-of-me.jpg",
  * "images/default.jpg");
  *
  * (see web/uihelper.php).
  */
  public static function resize_mk_img(
  $root_path, // path to which all other paths passed to resize_mk_img are relative
  $root_url, // url of $root_path
  $output_path, // relative path to a directory where we can put resized images
  $max_x, // max width of output image
  $max_y, // max height of output image
  $picture, // relative path to image to resize
  $alternate=NULL, // relative path to an alternate image
  $overwrite=FALSE, // set to TRUE if you want to overwrite the resized image if it's already there
  $extra_attrs="", // extra attributes to include in the <img> tag
  $resize_type=RESIZE_CROP // RESIZE_CROP, RESIZE_FIT or RESIZE_STRETCH.
  ) {
    $info = ImageResize::resize_img($root_path, $root_url, $output_path, $max_x, $max_y, $picture, $alternate, $overwrite, $resize_type);
    if (!($picture instanceof StoredFile)) {
      if (!@file_exists( $info['final_path'])) {
        $info=NULL;
        // For Animated Gif files Only
        ImageResize::create_frame_from_animated_pic($root_path,$picture,$output_path,$max_x,$max_y);
        $info = ImageResize::resize_img($root_path, $root_url, $output_path, $max_x, $max_y, $picture, $alternate, $overwrite, $resize_type);
      }
      $info['url'] = "$root_url/".$info['final_path'];
    }
    if ($extra_attrs) $img = "<img $extra_attrs"; else $img = "<img";
    return $img.' border="0" src="'.htmlspecialchars($info['url']).'" '.$info['size_attr'].'/>';
  }

  // like resize_mk_img, but returns url, width, height etc so you can
  // access them individually.  used by web/api/lib/api_impl.php.
  public static function resize_img(
  $root_path, // path to which all other paths passed to resize_mk_img are relative
  $root_url, // url of $root_path
  $output_path, // relative path to a directory where we can put resized images
  $max_x, // max width of output image
  $max_y, // max height of output image
  $picture, // relative path to image to resize
  $alternate=NULL, // relative path to an alternate image
  $overwrite=FALSE, // set to TRUE if you want to overwrite the resized image if it's already there
  $resize_type=RESIZE_CROP // RESIZE_FIT, RESIZE_CROP or RESIZE_STRETCH
  ) {

    $final_path = NULL;

    if ($alternate) {
      if (preg_match("|^http://|", $alternate)) throw new PAException(BAD_PARAMETER, "Alternate image passed to resizing functions must not be a URL");
      if (!preg_match("#^(files|Themes|images)/#", $alternate)) throw new PAException(BAD_PARAMETER, "Alternate image passed to resizing functions must be relative to the web directory; $alternate is not valid");
    }

    if ($picture instanceof StoredFile) {
      $stored_file = $picture;
      $pic_path = $picture->filename;
    } else {
      if (defined("NEW_STORAGE")) {
        // check for broken or deprecated calling code
        if (preg_match("|^files/files|", $picture)) throw new PAException(INVALID_ID, "Broken image ID - starting with files/files!");
        if (preg_match("|^files/pa://|", $picture)) throw new PAException(INVALID_ID, "Broken image ID - check for code adding 'files/' to the start of a pa:// image URL");
      }

      $stored_file = NULL;
      $image_path = NULL;
      if(@getimagesize(PA::$project_dir."/$root_path/$picture")) {
        $image_path = PA::$project_dir."/$root_path";
      } else if(@getimagesize(PA::$core_dir."/$root_path/$picture")) {
        $image_path = PA::$core_dir."/$root_path";
      } else if(@getimagesize(PA::$project_dir."/$root_path/$alternate")) {
        $image_path = PA::$project_dir."/$root_path";
      } else if(@getimagesize(PA::$core_dir."/$root_path/$alternate")) {
        $image_path = PA::$core_dir."/$root_path";
      }
      
      if($picture && is_file("$image_path/$picture") && (getimagesize("$image_path/$picture") !== false)) {
        $pic_path = $picture;
      } else if (!$alternate || !is_file("$image_path/$alternate")) {
        // we could throw a FILE_NOT_FOUND exception here, but that
        // breaks things, so instead we output an image tag with the
        // requested size that refers to the original path.  this
        // way the admin will see 404 errors in the log, and maybe
        // fix what's wrong.
        $final_path = $picture;
        $width = $max_x;
        $height = $max_y;
      } else {
        $pic_path = $alternate;
      }
    }

    if (!$final_path) {
      // if it's a png or gif, convert to png - so we don't lose transparency.  otherwise jpg.
      $path_parts = pathinfo($pic_path);
      $ext = strtolower($path_parts['extension']);
      switch ($ext) {
        case 'png':
        case 'gif':
          $ext = 'png';
          $mime_type = "image/png";
          break;
        default:
          $ext = 'jpg';
          $mime_type = "image/jpeg";
          break;
      }

      $prefix = ImageResize::$resize_type_prefixes[$resize_type];
      if (!$prefix) throw new PAException(BAD_PARAMETER, "Invalid resize type: $resize_type");
      // 'dim' string for file link
      $file_link_dim = $prefix."-".$max_x."x".$max_y;

      if ($stored_file) {
        // have we resized this already?
        $link = Storage::find_thumb($stored_file->file_id, $file_link_dim);
        if ($link) {
          $thumb_id = $link['file_id'];
        } else {
          // nope - we have to resize it now
          $picture_full_path = $stored_file->getPath();
          // temp output filename
          $resized_fn_tmp = tempnam(ini_get("upload_tmp_dir"), "rsz");
          $resized_fn = $resized_fn_tmp . "." . $ext;
          rename($resized_fn_tmp, $resized_fn);

          // leaf name, to show to users later on
          $leaf = $stored_file->filename;

          Logger::log("Resizing image '$picture_full_path' from Storage into $resized_fn", LOGGER_ACTION);
          ImageResize::do_resize_to_max_side($picture_full_path, $resized_fn, $max_x, $max_y, $resize_type);
          list ($w, $h) = getimagesize($resized_fn);
          // make the new file
          $thumb_id = Storage::save($resized_fn, $file_link_dim."-".$leaf, "throwaway", $mime_type, array("width" => $w, "height" => $h));
          unlink($resized_fn);
          // link it to the original so we can find it again
          Storage::link($thumb_id, array("role" => "thumb", "dim" => $file_link_dim, "file" => $stored_file->file_id));
        }

        // and return the details
        $thumb = Storage::get($thumb_id);
        return array(
        'url' => $thumb->getURL(),
        'width' => $thumb->width,
        'height' => $thumb->height,
        'size_attr' => 'width="'.$thumb->width.'" height="'.$thumb->height.'"',
        );
      } else {
        // relative path to resized file
        $resized_pic_path = $prefix."_".$max_x."x".$max_y."/".preg_replace("/\.[A-Za-z]+$/", "", $pic_path) . ".$ext";
        // abs path to resized file
        $resized_fn = PA::$project_dir."/$root_path/$output_path/$resized_pic_path";

        // only overwrite an existing file if it's out of date or we have been told to (via $overwrite)
        if (!file_exists($resized_fn) || (filemtime($resized_fn) < filemtime("$image_path/$pic_path")) || $overwrite) {
        // make all path parts up to the image
          if (!is_dir(dirname($resized_fn))) {
            $mkdir_path = PA::$project_dir."/$root_path/$output_path";
            ImageResize::try_mkdir($mkdir_path);
            foreach (explode("/", dirname($resized_pic_path)) as $path_part) {
              $mkdir_path .= "/$path_part";
              ImageResize::try_mkdir($mkdir_path);
            }
          }

          ImageResize::do_resize_to_max_side("$image_path/$pic_path", $resized_fn, $max_x, $max_y, $resize_type);
          clearstatcache();
        }
      }
      list($width, $height) = @getimagesize($resized_fn);

      $final_path = "$output_path/".dirname($resized_pic_path)."/".rawurlencode(basename($resized_pic_path));
   }

    return array(
    'final_path' => $final_path,
    'width' => $width,
    'height' => $height,
    'size_attr' => 'width="'.$width.'" height="'.$height.'"'
    );
  }

  //FIXME: replace calls to this image with calls to uihelper_resize_mk_img
  public static function mk_img($fn_leaf, $max_side) {
    $im_info = ImageResize::resize_to_max_side($fn_leaf, $max_side);
    return '<img border="0" src="'.htmlspecialchars($im_info['url']).'" '.$im_info['attr'].' alt="PA"/>';
  }

  /* Make an image with a given max side length, returning path, url,
  * width/height, and width/height in 'width="123" height="234"'
  * format, ready for insert into an <img> tag.  If the resized image
  * already exists, this won't overwrite it unless $overwrite=TRUE,
  * so you can use this to get the url/width/height of an image prior
  * to display.
  *
  * Args:
  *
  * $fn_leaf = leaf name of file to resize, e.g. "foobar.jpg" if the
  * full path is "web/files/foobar.jpg".
  *
  * $max_side = number of pixels you want to have in the longest side
  * of the resized image.
  *
  * $overwrite = TRUE if you want to overwrite an existing scaled
  * image (i.e. if you're calling this in response to an upload), or
  * FALSE to just get its dimensions if it's there (i.e. if you're
  * calling this just to *display* something).
  *
  * $files_root = path to the 'files' directory - usually
  * "web/files", but you can use this elsewhere if you
  * really want to :-)
  */
  //FIXME: replace calls to this with calls to uihelper_resize_mk_img
  /*  public static function resize_to_max_side($fn_leaf, $max_side, $overwrite=FALSE, $files_root=NULL) {
  // global var $_base_url has been removed - please, use PA::$url static variable

  if (!$files_root) $files_root = "web/files";

  if (!$fn_leaf) throw new PAException(FILE_NOT_FOUND, "No filename specified");

  $files_root = realpath($files_root);
  $resized_root = "$files_root/resized";
  $resized_path = "$resized_root/$max_side";
  $src_fn = "$files_root/$fn_leaf";

  $resized_fn_leaf = preg_replace("/\.[A-Za-z]+$/", "", $fn_leaf) . ".jpg";
  $resized_fn = "$resized_path/$resized_fn_leaf";

  if (!file_exists($src_fn))
  throw new PAException(FILE_NOT_FOUND, "full-size image not found");

  if (!file_exists($resized_fn) || $overwrite) {
  ImageResize::try_mkdir($resized_root);
  ImageResize::try_mkdir($resized_path);
  ImageResize::do_resize_to_max_side($src_fn, $resized_fn, $max_side, $max_side);
  }

  $size = getimagesize($resized_fn);

  return array(
  'path' => $resized_fn,
  'url' => PA::$url . "/files/resized/$max_side/$resized_fn_leaf",
  'w' => $size[0],
  'h' => $size[1],
  'mime' => $size['mime'],
  'attr' => $size[3],
  );
  }*/

  // functions used by resize_max_size

  private static function gd_available() {
    return function_exists("gd_info") && function_exists("imagecreatefromjpeg");
  }

  public static function get_engine() {
    if (ImageResize::find_magick() && !ImageResize::$skip_magick) return "magick";
    if (ImageResize::gd_available() && !ImageResize::$skip_gd) return "gd";
    return NULL;
  }

  private static function do_resize_to_max_side($src_fn, $resized_fn, $max_x, $max_y, $resize_type=RESIZE_CROP) {
    $convert = ImageResize::find_magick();
    $magick_attempted = FALSE;
    if ($convert && !ImageResize::$skip_magick) {
      // try to do it with imagemagick
      $magick_attempted = TRUE;
      try {
        return ImageResize::magick_resize_image($convert, $src_fn, $resized_fn, $max_x, $max_y, $resize_type);
      } catch (PAException $magick_exc) {
        Logger::log("ImageMagick failed to resize $src_fn; trying GD");
      }
    }

    if (ImageResize::gd_available() && !ImageResize::$skip_gd) {
      // we have gd installed
      return ImageResize::gd_resize_image($src_fn, $resized_fn, $max_x, $max_y, $resize_type);
    }

    if ($magick_attempted) throw $magick_exc;

    throw new PAException(MISSING_DEPENDENCY, "need to have either gd or imagemagick installed to resize images");
  }

  private static function try_mkdir($path) {
    if (!is_dir($path)) {
      if (!@mkdir($path)) {
        throw new PAException(OPERATION_NOT_PERMITTED, "Can't create directory $path");
      }
    }
  }

  private static function loadimage($file) {
    if( !file_exists($file) ) return FALSE;

    $what = getimagesize($file);

    switch( $what[2] ){
      case IMAGETYPE_PNG: $src_id = @imagecreatefrompng($file); break;
      case IMAGETYPE_JPEG: $src_id = @imagecreatefromjpeg($file); break;
      case IMAGETYPE_GIF:
        $old_id = @imagecreatefromgif($file);
        $src_id = @imagecreatetruecolor($what[0], $what[1]);
        @imagecopy($src_id,$old_id,0,0,0,0,$what[0],$what[1]);
        break;
      default: return FALSE;
    }

    return $src_id;
  }

  public static function gd_resize_image($infn, $outfn, $max_x, $max_y, $resize_type=RESIZE_CROP) {
    Logger::log("Resizing $infn to $outfn using built in GD");

    // figure out x and y scale required to fit input image to desired size
    list($w, $h) = @getimagesize($infn);
    $x_scale = floatval($max_x)/floatval($w);
    $y_scale = floatval($max_y)/floatval($h);

    // now decide on scale required for desired resizing operation
    switch ($resize_type) {
      case RESIZE_CROP:
        $x_scale = $y_scale = max($x_scale, $y_scale);
        break;

      case RESIZE_CROP_NO_EXPAND:
        $x_scale = $y_scale = min(1.0, max($x_scale, $y_scale));
        break;

      case RESIZE_CROP_NO_SCALE:
        $x_scale = $y_scale = 1.0;
        break;

      case RESIZE_FIT:
        $x_scale = $y_scale = min($x_scale, $y_scale);
        break;

      case RESIZE_FIT_NO_EXPAND:
        $x_scale = $y_scale = min(1.0, $x_scale, $y_scale);
        break;

      case RESIZE_STRETCH:
        // keep previously calculated values
        break;

      default:
        throw new PAException(BAD_PARAMETER, "Invalid resize_type: $resize_type");
    }

    // figure out size of output image
    $tw = intval($w * $x_scale + 0.5);
    $th = intval($h * $y_scale + 0.5);

    // if cropping, work out offsets and reduce $tw and $th as required
    switch ($resize_type) {
      case RESIZE_CROP:
      case RESIZE_CROP_NO_EXPAND:
      case RESIZE_CROP_NO_SCALE:
        if ($tw > $max_x) {
          $x_offset = (int)((floatval($tw - $max_x) / 2.0) / $x_scale);
          $w = intval(floatval($max_x) / $x_scale + 0.5);
          $tw = $max_x;
        }
        if ($th > $max_y) {
          $y_offset = (int)((floatval($th - $max_y) / 2.0) / $y_scale);
          $h = intval(floatval($max_y) / $y_scale + 0.5);
          $th = $max_y;
        }
        break;

      default:
        // not cropping
        $x_offset = $y_offset = 0;
        break;
    }


    // create blank white image
    $thumb = @imagecreatetruecolor($tw, $th);
    @imagealphablending($thumb, FALSE);
    @imagefilledrectangle($thumb, 0, 0, $tw, $th, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
    @imagealphablending($thumb, TRUE);

    // load image and resample into output image
    $orig = ImageResize::loadimage($infn);
    @imagecopyresampled($thumb, $orig, 0, 0, $x_offset, $y_offset, $tw, $th, $w, $h);

    // save output and tidy up
    $path_parts = pathinfo($outfn);
    switch (strtolower($path_parts['extension'])) {
      case 'png':
        @imagealphablending($thumb, FALSE);
        @imagesavealpha($thumb, TRUE);
        imagepng($thumb, $outfn);
        break;
      default:
        imagejpeg($thumb, $outfn);
        break;
    }

    @imagedestroy($orig);
    @imagedestroy($thumb);

    return Array($tw, $th);
  }

  private static function find_magick() {
    $f = popen("which convert", "r");
    $convert = trim(fgets($f));
    pclose($f);

    return $convert;
  }

  private static function shell_escape($txt) {
    return str_replace(" ", "\\ ", escapeshellcmd($txt));
  }

  public static function magick_resize_image($convert, $src_fn, $resized_fn, $max_x, $max_y, $resize_type=RESIZE_CROP) {
    Logger::log("Resizing $src_fn to $resized_fn using ImageMagick");
    if (!$convert) $convert = ImageResize::find_magick();

    list ($w, $h) = getimagesize($src_fn);

    $x_scale = floatval($max_x)/floatval($w);
    $y_scale = floatval($max_y)/floatval($h);

    switch ($resize_type) {
      case RESIZE_STRETCH:
        $args = "-geometry ${max_x}x$max_y!";
        break;

      case RESIZE_CROP:
      case RESIZE_CROP_NO_EXPAND:
      case RESIZE_CROP_NO_SCALE:
        // first crop the input image, then resize that to fit the output
        $scale = max($x_scale, $y_scale);
        if ($resize_type == RESIZE_CROP_NO_EXPAND) $scale = min(1.0, $scale);
        if ($resize_type == RESIZE_CROP_NO_SCALE) $scale = 1.0;

        // convert output size into input coords
        $input_max_x = min($w, intval(floatval($max_x) / $scale + 0.5));
        $input_max_y = min($h, intval(floatval($max_y) / $scale + 0.5));
        if ($resize_type == RESIZE_CROP_NO_EXPAND) {
          $max_x = intval(floatval($input_max_x) * $scale + 0.5);
          $max_y = intval(floatval($input_max_y) * $scale + 0.5);
        }
        // now find offsets
        $input_x_offset = intval(($w - $input_max_x) / 2);
        $input_y_offset = intval(($h - $input_max_y) / 2);

        $args = "-crop ${input_max_x}x${input_max_y}+$input_x_offset+$input_y_offset -geometry ${max_x}x$max_y!";
        break;

      case RESIZE_FIT:
        $args = "-geometry ${max_x}x$max_y";
        break;

      case RESIZE_FIT_NO_EXPAND:
        $max_x = min($max_x, $w);
        $max_y = min($max_y, $h);
        $args = "-geometry ${max_x}x$max_y";
        break;
    }

    // call /usr/bin/convert and return an error if it fails
    $cmd = $convert.' '.ImageResize::shell_escape($src_fn)." $args ".ImageResize::shell_escape($resized_fn);
    system($cmd, $retval);
    if ($retval) throw new PAException(GENERAL_SOME_ERROR, "$convert returned error code $retval (for command: $cmd)");
  }

  private function make_square_image ($infn, $gd_OR_magick = "GD") {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    $directory = PA::$project_dir."/web/files/square/"; //TO DO: Remove hardcoding here.
    if (!@is_dir($directory)) {
      ImageResize::try_mkdir($directory);
    }

    $file_path = explode("/", $infn);
    // TO DO: Rename images to avoid conflicts.
    $imageName = $file_path[(count($file_path) - 1)];
    $destination = $directory.$imageName;
    if(@file_exists($destination) && filemtime($destination) > filemtime($infn)) { return SQUARE_IMAGES.'/'.$imageName;}
    $dimensions = @getimagesize ($infn);
    $width = $dimensions[0];
    $height = $dimensions[1];

    if ($width == $height) { // Image is a square image
      @copy($infn, $destination);
      return SQUARE_IMAGES.'/'.$imageName;
    }

    $X_start = $Y_start = $width_final = $height_final = $offset = 0;

    if ($width > $height) {  // width more than height
      $X_start = round(($width - $height)/2);
      $Y_start = 0;
      $offset = $height;
      $width_final = $height_final = $height;

    }
    else { // height more than width
      $X_start = 0;
      $Y_start = round(($height - $width)/2);
      $width_final = $height_final = $width;
      $offset = $width;
    }

    if ($gd_OR_magick == "MAGICK") {
      $cmd = 'convert -crop '.$width_final.'x'.$height_final.'+'.$X_start.'+'.$Y_start.' '.$infn.' '.SQUARE_IMAGES.'/'.$imageName;
      system($cmd, $retval);
      return SQUARE_IMAGES.'/'.$imageName;
    }

    $orig = ImageResize::loadimage($infn);
    $imageDestination = @imagecreatetruecolor($width_final, $height_final);
    @imagecopyresampled($imageDestination, $orig, 0, 0, $X_start, $Y_start, $width_final, $height_final, $offset, $offset);

    @imagejpeg($imageDestination, $destination, 100);
    @imagedestroy($imageDestination);
    @imagedestroy($orig);
    return SQUARE_IMAGES.'/'.$imageName;
  }
  /**
    Creating a Function which take an Animated Gif Image and Convert into Still image 
  */
  public function create_frame_from_animated_pic ($img_input_path,$picture,$img_output_path,$max_x,$max_y ,$new_image_name = False) {
    // global var $_base_url has been removed - please, use PA::$url static variable

    Logger::log("create_frame_from_animated_pic($img_input_path, $picture, $img_output_path, $max_x, $max_y, $new_image_name)");

    // for Retriving the Original Name of file
    $temp_name = explode('/',$picture);
    $temp_name = $temp_name['1'];

    if (!empty($temp_name)) {
      $name = explode('.',$temp_name);
      $file_name = $name['0'];
      // if it's a .gif image
      if (preg_match("/\.gif$/i", $temp_name)) {
        $img_input_path = $img_input_path.'/files/';
        $image_full_path = $img_input_path.$temp_name;
        $new_image =  $file_name;

        // creating the object of the Gifsplit class and initializing the Contructor
        $sg = new GifSplit($image_full_path, 'GIF', $new_image, $img_input_path);
        if ($sg->getReport() == 'ok') {
          return TRUE;
        }
      }
      return FALSE ;
    }

  }

}
?>