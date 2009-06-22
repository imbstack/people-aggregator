<?php
/**
 * @script preview_image.php
 *
 * @brief: AJAX image upload and preview
 *
 *  NOTE: this AJAX scritp assume that file upload input tag id='userfile'
 *
 * @author     Zoran Hron, 2008-11-14 <zhron@broadbandmechanics.com>
 *
 */

 require_once dirname(__FILE__).'/../../config.inc';
 require_once "api/Storage/Storage.php";
 require_once 'web/includes/classes/file_uploader.php';

    $error = null;
    $image_file = null;
    $img_resized = null;
    $img_url = (!empty($_POST['image_url'])) ? trim($_POST['image_url']) : null;

    if(!$img_url) {
      if(!empty($_FILES['userfile']['error']))  {
        switch($_FILES['userfile']['error']) {
            case '1':  $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
            case '2':  $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
            case '3':  $error = 'The uploaded file was only partially uploaded'; break;
            case '4':  $error = 'No file was uploaded.'; break;
            case '6':  $error = 'Missing a temporary folder'; break;
            case '7':  $error = 'Failed to write file to disk'; break;
            case '8':  $error = 'File upload stopped by extension'; break;
            case '999':
            default:
                $error = 'No error code avaiable';
        }
      } elseif (empty($_FILES['userfile']['tmp_name']) || $_FILES['userfile']['tmp_name'] == 'none')    {
          $error = 'No file was uploaded..';
      } else    {
        try {
          $myUploadobj = new FileUploader;
          $image_file = $myUploadobj->upload_file(PA::$upload_path,'userfile',true,true,'image');
          if (!$image_file) {
          	$error = $myUploadobj->error;
          }
        } catch (Exception $e) {
          $error = $e->getMessage();
        }
        $img_resized = uihelper_resize_mk_img($image_file, 200, 200, NULL, 'style="margin-left: 10px"');
      }
      echo "{";
      echo                "error: '" . $error . "',\n";
      echo                "image: '" . htmlspecialchars($img_resized) . "',\n";
      echo                "image_file: '" . $image_file . "'\n";
      echo "}";
    } else {
      if(preg_match("|http://(.*?)/(.*)|", $img_url, $m)) {
        try {
          list(, $uf_server, $uf_path) = $m;
          $image_file = Storage::save($img_url, basename($uf_path), "critical", "image");
        } catch (Exception $e) {
          $error = $e->getMessage();
        }
        $img_resized = uihelper_resize_mk_img($image_file, 200, 200, NULL, 'style="margin-left: 10px"');
      } else {
        $error = 'Invalid Image URL.';
      }
      echo "{";
      echo                "error: '" . $error . "',\n";
      echo                "image: '" . htmlspecialchars($img_resized) . "',\n";
      echo                "image_file: '" . $image_file . "'\n";
      echo "}";
    }

?>
