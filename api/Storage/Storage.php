<?php

/** Storage: file storage abstraction.  Backend-specific stuff goes in ext/StorageBackend/*.php
 * Author: Phillip Pearson
 * Copyright (C) 2007 Broadband Mechanics
 */

/*

Public storage API:

To save a file into storage:

$file_id = Storage::save($current_filename, $filename, $file_class="critical", $mime_type="application/octet-stream", $meta=array())

This will copy the file currently located at $current_filename into
storage, saving the filename $filename and file MIME type $mime_type
(e.g. "image" for unknown images, "image/jpeg" for jpegs,
"application/octet-stream" for totally unknown).

$file_class specifies how important the file is; if the
StorageBackend supports different levels of reliability.  "critical"
means the file is irreplaceable and will be replicated widely; this
should be used for uploaded files.  "throwaway" means the file is
not so important; this can be used for generated files like
thumbnails.

The file will initially have a reference count of 0; you will need
to call Storage::link() to link the file to a gallery, blog post or
some other usage.

Return code: file identifier (32-char hex string).

To link a file to somewhere and increment the reference count:

$link_id = Storage::link($file_id, $params)

This creates a link between file $file_id and the location specified
in $params, and assign a link ID.

$params is an array containing some of the following keys:

- "role" (required): a 'usage role'
- "network": a network_id
- "user": a user_id
- "group": a group_id
- "ad": an ad_id (for advertisement media)
- "content": a content_id (for gallery media or media embedded in content)
- "file": a file_id (for derivative files, e.g. thumbnails)
- "dim": a string of the form "123x456", where 123 and 456 represent a width and height, respectively (for thumbnails)

The value of "role" defines what other parameters are valid.
Possible values of "role":

- "thumb": a thumbnail (requires: file, dim)
- "media": a media object, either standalone/gallery, or embedded in a content object (requires: network, content)
- "avatar": a user, network or group avatar (requires: user|network|network+group)
- "header": a user, network or group header image (requires: user|network|network+group)
- "ad": an advertisement image (requires: network, ad)
- "emblem": a network-level 'partner site' logo image (requires: network)
- "showcased_net": a global 'showcased network' logo image (requires: nothing)

The following combinations of network/group/user are applicable
(depending on the role):

- for a file pertaining to a user (e.g. user avatar): 'user' => $user_id
- for a file pertaining to a user on a network (e.g. gallery media): 'network' => $network_id, 'user' => $user_id
- for a file pertaining to a network (e.g. network header): 'network' => $network_id
- for a file pertaining to a group (e.g. group avatar): 'network' => $network_id, 'group' => $group_id

Return code: link identifier (integer; local to each file, so the
first reference to each file will have a link_id of 1).

To find a thumbnail:

Storage::find_thumb($parent_file_id, $dimensions) -> array(link info from database)

To destroy a link to a file, decrement its reference count, and
optionally delete the file if this is the last link:

Storage::unlink($file_id, $link_id, $delete_immediately=TRUE) -> TRUE

In general you should not pass a value for $delete_immediately
unless you definitely want to override the default, which will
change to 'FALSE' in future once we have a scheduled process running
to reap files that have been deleted for a sufficiently long time.

To retrieve a file from storage:

$path = Storage::getPath($file_id)

Given a file_id, this retrieves the file from storage if necessary,
and returns a local path to the file.

To verify that a file ID is either a Storage ID, a URL or a valid path
to a file inside web/files (checking for directory traversal attacks,
etc):

$file_id = Storage::validateFileId($file_id)

This will return the (possibly sanitized) file ID if valid, or NULL
if not.

To generate a URL for a file in storage:

$url = Storage::getURL($file_id)

Given a file_id, this moves the file into a web accessible space if
necessary, and returns a (not necessarily local) URL to the file.

To clean up files which have been created but never used, or for which
all links have been deleted:

Storage::cleanupFiles($min_age=1800, $undelete_grace_period=1800)

This will remove from backend storage that either:

- have never had any links, and were created at least $min_age
seconds ago, or

- no longer have any links, the last of which was deleted at least
$undelete_grace_period seconds ago.

TODO: Currently files which failed to completely upload are left in
place.

*/

require_once "api/Storage/StoredFile.php";

class Storage {

  // Storage backend cache
  private static $backends = array();

  // PUBLIC API: save
  public static function save($current_filename, $filename, $file_class="critical", $mime_type="application/octet-stream", $meta=NULL) {
    // validate $file_class
    if (!in_array($file_class, array("critical", "throwaway"))) throw new PAException(INVALID_ID, "Invalid file class $file_class");
    //INNODB TODO: start transaction
    $sql = "INSERT INTO files SET filename=?, file_class=?, mime_type=?, created=NOW()";
    $args = array($filename, $file_class, $mime_type);
    if ($meta) {
      foreach ($meta as $k => $v) {
        $sql .= ", $k=?";
        $args[] = $v;
      }
    }
    Dal::query($sql, $args);
    $file_id = Dal::insert_id();
    try {
      $ctx = Storage::connect();
      if (preg_match("|^http://|", $current_filename)) {
        // it's a URL - download it instead
        $local_id = $ctx->download($current_filename, $file_id, $filename, $file_class, $mime_type);
      } else {
        $local_id = $ctx->save($current_filename, $file_id, $filename, $file_class, $mime_type);
      }
      Dal::query("UPDATE files SET storage_backend=?, local_id=?, incomplete=0 WHERE file_id=?", array($ctx->backend_type, $local_id, $file_id));
      //INNODB TODO: commit
      Logger::log("Saved file $current_filename into storage as $filename, ID $file_id", LOGGER_ACTION);
      return "pa://".$file_id;
    } catch (PAException $e) {
      Logger::log("Cleaning up incomplete file $file_id from storage due to error from backend while saving", LOGGER_ERROR);
      Dal::query("DELETE FROM files WHERE file_id=?", array($file_id));
      throw $e;
    }
  }

  // PUBLIC API: link
  public static function link($file_id, $params) {
    if (!defined("NEW_STORAGE")) return; // no-op if Storage is disabled

    //INNODB TODO: start transaction
    $file_id = self::parseId($file_id);

    // Verify file
    list($incomplete, $link_count) = Dal::query_one("SELECT incomplete, link_count FROM files WHERE file_id=?", array($file_id));
    if ($incomplete) throw new PAException(FILE_NOT_FOUND, "File $file_id was not completely saved; cannot create a link to it");

    // Validate $params and build SQL to add link
    $role = $params['role'];
    if (empty($role)) throw new PAException(BAD_PARAMETER, "Storage role must be provided to link()");
    $sql = "INSERT INTO file_links SET file_id=?, role=?";
    $sqlargs = array($file_id, $role);

    $required_params = NULL;
    unset($params['role']);
    switch ($role) {
      case 'thumb': $required_params = array("file", "dim"); break; // thumbnail for any image
      case 'media': $required_params = array("network", "content"); break; // media -> content in one network

      // tied to one event:  (TODO: does this need 'network' too?)
      case 'event_logo': $required_params = array("event_id", "user"); break;

      // tied to one user:
      case 'cv': $required_params = array("user"); break;

      // user/group/network avatar/header
      case 'avatar':
      case 'header':
        if (!empty($params['user'])) {
          $required_params = array("user");
        } else if (!empty($params['group'])) {
          $required_params = array("network", "group");
        } else {
          $required_params = array("network");
        }
        break;

        // advertising
      case 'ad': $required_params = array("network", "ad"); break;

      // tied to one network:
      case 'emblem':
      case 'tour_img':
        $required_params = array("network");
        break;

      case 'showcased_net': $required_params = array(); break;
      default:
        throw new PAException(BAD_PARAMETER, "Invalid storage role '$role'");
    }

    if (in_array("network", $required_params) && empty($params['network'])) $params['network'] = PA::$network_info->network_id;

    // Map user param names to SQL column names
    $param_name_mapping = array(
    "network" => "network_id",
    "user" => "user_id",
    "group" => "group_id",
    "ad" => "ad_id",
    "content" => "content_id",
    "file" => "parent_file_id",
    "dim" => "dim",
    );

    // Process required params, removing them from $params as we go
    foreach ($required_params as $param_name) {
      if (empty($params[$param_name])) {
        throw new PAException(BAD_PARAMETER,
        "Required parameter '$param_name' (for role $role) not provided to link() for file $file_id");
      }
      $value = $params[$param_name];
      switch ($param_name) {
        case "network":
        case "user":
        case "group":
        case "ad":
        case "content":
          if (!is_numeric($value)) throw new PAException(BAD_PARAMETER, "'$param_name' parameter must be numeric");
          break;
        case "dim":
          if (!preg_match("/^.*?\-\d+x\d+$/", $value)) throw new PAException(BAD_PARAMETER, "'dim' parameter must be of the format <prefix>-<width>x<height>");
          break;
        case "file":
          $parent_file_id = $value;
          if ($parent_file_id == $file_id) throw new PAException(BAD_PARAMETER,
          "Cannot link a file in as a derivative of itself (file_id == parent_file_id == $file_id)");
          $parent_file = Dal::query_one("SELECT incomplete FROM files WHERE file_id=?", array($parent_file_id));
          if (empty($parent_file)) throw new PAException(FILE_NOT_FOUND,
          "Parent of derivative file in link must exist (file $file_id, parent file $parent_file_id)");
          break;
      }
      $sql .= ", ".$param_name_mapping[$param_name]."=?";
      $sqlargs[] = $params[$param_name];
      unset($params[$param_name]);
    }
    // If $params is not empty by now, we must have some extra params, that aren't allowed
    if (count($params) > 0) throw new PAException(BAD_PARAMETER,
    "Invalid parameters (".implode(", ", array_keys($params)).") provided to link() for role $role, file $file_id");

    // Now store the link and increment the file link count
    Dal::query($sql, $sqlargs);
    $link_id = Dal::insert_id();
    Dal::query("UPDATE files SET last_linked=NOW(), link_count=link_count+1 WHERE file_id=?", array($file_id));

    //INNODB TODO: commit

    return $link_id;
  }

  // PUBLIC API (only for use by ImageResize): find_thumb
  public static function find_thumb($file_id, $dimensions) {
    return Dal::query_one_assoc("SELECT * FROM file_links WHERE role='thumb' AND parent_file_id=? AND dim=?", array($file_id, $dimensions));
  }

  // PUBLIC API: unlink
  public static function unlink($file_id, $link_id, $delete_immediately=TRUE) {
    //INNODB TODO: start transaction

    // Verify link
    $r = Dal::query_one("SELECT file_id FROM file_links WHERE file_id=? AND link_id=?", array($file_id, $link_id));
    if (empty($r)) throw new PAException(FILE_NOT_FOUND, "Link $link_id does not exist");

    // Find file and verify link count
    list($incomplete, $link_count) = Dal::query_one("SELECT incomplete, link_count FROM files WHERE file_id=?", array($file_id));
    if ($incomplete) throw new PAException(STORAGE_ERROR, "Consistency error... we have a link $link_id to an incomplete file $file_id");
    if ($link_count < 1) throw new PAException(STORAGE_ERROR, "Consistency error... we have a link $link_id to a file $file_id, which claims to have no links");

    // Delete the link
    Dal::query("DELETE FROM file_links WHERE file_id=? AND link_id=?", array($file_id, $link_id));
    // Decrement link count on file, and update last_unlinked timestamp
    Dal::query("UPDATE files SET last_unlinked=NOW(), link_count=link_count-1 WHERE file_id=?", array($file_id));

    //INNODB TODO: COMMIT here, then turn autocommit back on

    // If we've been ordered, see if we now need to clean up the file now
    if ($delete_immediately) {
      Storage::cleanupFile($file_id);
    }

    return TRUE;
  }

  // PUBLIC API: get
  public static function get($file_id) {
    return new StoredFile($file_id);
  }

  public static function parseId($file_id, $allow_numeric=FALSE) {
    if ($allow_numeric && preg_match("/^\d+$/", $file_id)) return (int)$file_id;
    if (preg_match("|^pa://(\d+)|", $file_id, $m)) return (int)$m[1];
    throw new PAException(INVALID_ID, "Failed to parse file ID: $file_id");
  }

  // public API: validateFileId
  public static function validateFileId($file_id) {
    // empty - fail
    if (!trim($file_id)) return NULL;
    // if it's a pa:// id - ok
    try {
      self::parseId($file_id);
      return $file_id;
    } catch (PAException $e) {
      if ($e->getCode() != INVALID_ID) throw $e;
    }
    // if it's a url - ok
    if (preg_match("|^http://|", $file_id)) return $file_id;
    // if it's a file - check for dir traversal
    $files = realpath(PA::$upload_path);
    $path = realpath("$files/$file_id");
    if ($files && $path && strpos($path, $files) === 0) return $file_id;
    // otherwise fail
    return NULL;
  }

  // PUBLIC API: getFilename
  public static function getFilename($file_id) {
    if (preg_match("|^http://|", $file_id)) {
      // it's a url
      return preg_replace("|^.*/|", "", $file_id);
    }
    try {
      $file_id = self::parseId($file_id);
    } catch (PAException $e) {
      // it's a local file
      return preg_replace("|^.*/|", "", $file_id);
    }
    // it's a file in storage
    $f = self::get($file_id);
    return $f->filename;
  }

  // PUBLIC API: getPath
  public static function getPath($file_id) {
    if (preg_match("|^http://|", $file_id)) throw new PAException(INVALID_ID, "Trying to get the pathname of a URL");
    // try to be clever - if someone gives us "foo.jpg", return a URL to that file in the 'files' directory, otherwise look in Storage
    try {
      $file_id = self::parseId($file_id);
    } catch (PAException $e) {
      return "web/files/".$file_id;
    }
    $f = self::get($file_id);
    return $f->getPath();
  }

  // PUBLIC API: getURL
  public static function getURL($file_id, $download_url=FALSE) {
    if (preg_match("|^http://|", $file_id)) return $file_id; // it's already a URL
    // try to be clever - if someone gives us "foo.jpg", return a URL to that file in the 'files' directory, otherwise look in Storage
    try {
      $file_id = self::parseId($file_id);
    } catch (PAException $e) {
      return PA::$url."/files/".$file_id;
    }
    $f = self::get($file_id);
    if ($download_url) return $f->getDownloadURL();
    return $f->getURL();
  }

  // PUBLIC API: getDownloadURL
  public static function getDownloadURL($file_id) {
    return self::getURL($file_id, TRUE);
  }

  // PUBLIC API: get_or_make_static($path)
  public static function get_or_make_static($path, $mime_type="application/octet-stream") {
    if (!$path) throw new PAException(INVALID_ID, "Storage::get_or_make_static() requires a filename");
    if (!file_exists(PA::$project_dir . "/web/$path")) throw new PAException(FILE_NOT_FOUND, "File $path does not exist");

    // Call this for files distributed with PA that need to be in
    // Storage (FixedStorage backend) so they can be resized.
    $r = Dal::query_one_assoc("SELECT file_id FROM files WHERE storage_backend='fixed' AND local_id=?", array($path));
    if (!empty($r)) return new StoredFile($r);

    // Not in storage -- add it
    if (strlen($path) > 255) throw new PAException(INVALID_ID, "Path of file to store in fixed storage is too long - max 255 chars ($path)");
    $path_bits = explode("/", $path);
    $leaf = $path_bits[count($path_bits)-1];
    Dal::query("INSERT INTO files SET filename=?, file_class='fixed', mime_type=?, created=NOW(), incomplete=0, storage_backend='fixed', local_id=?", array(
    $leaf, $mime_type, $path));
    return new StoredFile(Dal::insert_id());

  }

  // PUBLIC API: cleanupFiles
  public static function cleanupFiles($min_age=1800, $undelete_grace_period=1800) {
    // Look for files that have never been linked, or have been completely unlinked.
    $sth = Dal::query("SELECT file_id FROM files WHERE link_count=0 AND last_unlinked IS NULL AND created < DATE_SUB(NOW(), INTERVAL ? SECOND)
            UNION SELECT file_id FROM files WHERE link_count=0 AND last_unlinked < DATE_SUB(NOW(), INTERVAL ? SECOND)",
    array($min_age, $undelete_grace_period));
    // Iterate through results and blow 'em away
    while (list($file_id) = Dal::row($sth)) {
      echo "cleaning up file $file_id\n";
      Storage::cleanupFile($file_id);
    }
  }

  /* PRIVATE API -- some things below may be declared public, but
  * please don't use them unless you really know what you're
  * doing! */

  // cleanupFile(): Immediately remove a file from backend storage
  // and return TRUE if there are no remaining links, otherwise
  // return FALSE.
  public static function cleanupFile($file_id) {
    // If there are now no links, delete the file
    $r = Dal::query_one("SELECT link_count, storage_backend, local_id FROM files WHERE file_id=?", array($file_id));
    if (empty($r)) throw new PAException(FILE_NOT_FOUND, "Cannot clean up file $file_id as it does not exist in the 'files' table");
    list($link_count, $backend_type, $local_id) = $r;
    if (!$link_count) {
      $ctx = Storage::connect($backend_type);
      $ctx->delete($local_id);
      Dal::query("DELETE FROM files WHERE file_id=?", array($file_id));
      return TRUE;
    }
    return FALSE;
  }

  // connect(): Get a backend of a particular type - normally you'll
  // want to just call Storage::connect() to get the configured
  // backend.
  public static function connect($backend_type=NULL) {
     
    if (empty($backend_type)) $backend_type = PA::$config->storage_backend;
    if (!empty(Storage::$backends[$backend_type])) {
      // Cached
      return Storage::$backends[$backend_type];
    }

    // Nothing in the cache - instantiate a backend.
    if (!preg_match("/^[A-Za-z]*$/", $backend_type)) {
      throw new PAException(INVALID_ID, "Invalid characters in storage backend: $backend_type");
    }
    $class_name = ucfirst($backend_type).'Storage';
    $backend_file = 'ext/StorageBackend/'.$class_name.'.php';
    require_once $backend_file;

    // Instantiate, cache, return
    $ctx = Storage::$backends[$backend_type] = new $class_name;
    $ctx->backend_type = $backend_type;
    return $ctx;
  }

  // migrateLegacyFiles(): Figure out who owns files in web/files,
  // that were put there before the storage abstraction system
  // existed.
  public function migrateLegacyFiles($dry_run=TRUE) {
    $this->dry_run = $dry_run;

    require_once "db/Dal/DbUpdate.php";

    echo "Migrating legacy files to new storage system\n";

    $this->all_files = array();

    if (!($h = opendir('web/files'))) throw new PAException(GENERAL_SOME_ERROR, "Unable to open web/files directory");
    while (false !== ($f = readdir($h))) {
      if ($f[0] == '.') continue;
      $this->all_files[$f] = TRUE;
    }
    closedir($h);

    $this->unmatched = count($this->all_files);
    $this->unmatchable = 0;
    $this->matched = 0;
    $this->dupes = 0;
    echo "$this->unmatched files found\n";

    echo "Matching with user images ...\n";
    $sql = Dal::validate_sql("SELECT user_id,picture FROM {users}", $network);
    $sth = Dal::query($sql);
    while ($r = Dal::row($sth)) {
      list($uid, $pic) = $r;
      // user avatar
      $this->_matchLegacyFile($pic, array("role" => "avatar", "user" => $uid));
      //TODO: user header image
    }
    $this->_dumpMatchResults();

    $networks = DbUpdate::get_valid_networks();

    echo "Processing ".count($networks)." networks\n";

    foreach ($networks as $network) {
      echo " Network: $network\n";
      // network level stuff
      list($network_id, $act, $logo, $extra) = Dal::query_one(
      "SELECT network_id, is_active, inner_logo_image, extra FROM networks WHERE address=?", array($network));
      assert($act); // sanity check
      $extra = unserialize($extra);

      // network avatar
      $this->_matchLegacyFile($logo, array("role" => "avatar", "network" => $network_id));

      // network header image
      $header_image = @$extra["basic"]["header_image"]["name"];
      if (!empty($header_image)) {
        $this->_matchLegacyFile($header_image, array("role" => "header", "network" => $network_id));
      }

      // emblems
      foreach (unserialize(Dal::query_first(Dal::validate_sql("SELECT data FROM {moduledata} WHERE modulename='LogoModule'"))) as $emblem) {
        $this->_matchLegacyFile($emblem["file_name"], array("role" => "emblem", "network" => $network_id));
      }

      // group pictures
      $sth = Dal::query(Dal::validate_sql("SELECT collection_id, picture FROM {contentcollections} WHERE type=1 AND is_active=1", $network));
      while ($r = Dal::row($sth)) {
        list ($cid, $pic) = $r;
        $this->_matchLegacyFile($pic, array("role" => "avatar", "network" => $network_id, "group" => $cid));
        $header = Dal::query_first(Dal::validate_sql("SELECT header_image FROM groups WHERE group_id=?", $network), array($cid));
        $this->_matchLegacyFile($header, array("role" => "header", "network" => $network_id, "group" => $cid));
      }
      /* disabled until we update peopleaggregator.net
      $sth = Dal::query(Dal::validate_sql("SELECT group_id, header_image FROM {groups}", $network));
      while ($r = Dal::row($sth)) {
      list ($gid, $pic) = $r;
      $this->_matchLegacyFile($network, "group", $gid, $pic);
      }
      */

      //TODO: advertisements

      // images, audio, video
      foreach (array("image", "audio", "video") as $table) {
        $sth = Dal::query(Dal::validate_sql('SELECT mc.content_id, mc.'.$table.'_file, c.author_id, c.collection_id, c.is_active FROM {'.$table.'s} mc LEFT JOIN {contents} c ON mc.content_id=c.content_id HAVING c.is_active=1', $network));
        while ($r = Dal::row($sth)) {
          list ($cid, $fn, $uid, $ccid, $act) = $r;
          $this->_matchLegacyFile($fn, array("role" => "media", "network" => $network_id, "content" => $cid));
        }
      }
    }
    $this->_dumpMatchResults();

    foreach ($this->all_files as $fn => $v) {
      if ($v === TRUE) {
        echo " * unmatchable: $fn\n";
      }
    }

    echo "Overall results from web/files: "; $this->_dumpMatchResults();

  }

  private function _dumpmatchResults() {
    echo "$this->unmatched not matched; $this->matched matched. $this->unmatchable missing; $this->dupes multi-matched\n";
  }

  private function _matchLegacyFile($filename, $params) {
    if (empty($filename)) return;
    if (preg_match("|^http://|", $filename)) {
      print "file $filename is a link - use HttpStorage (TODO)\n";
      return;
    }

    $f =& $this->all_files[$filename];

    if (empty($f)) {
      echo "  * $filename not found\n";
      ++ $this->unmatchable; -- $this->unmatched;
      return;
    } else if ($f === TRUE) {
      echo "matched: $filename, ".str_replace("\n", " ", print_r($params, TRUE))."\n";
      $f = array("global", "user", $uid);
      ++ $this->matched; -- $this->unmatched;
    } else {
      //      echo "  + $filename matched more than one object (net $network_id, ctx $context, id $context_id)!\n";
      ++ $this->dupes;
    }

    // If we got this far, we should save a copy of the file in the provided context
    //  echo "Saving a copy of $filename in context net=$network_id ctx=$context id=$context_id\n";
    if (!$this->dry_run) {
      echo "Actually saving file\n";
    }
  }

  // called by LocalStorage.php (and other backends) to validate file type against given mime type
  public static function validateFileType($path, $filename, $mime_type) {
    @list($mime_major, $mime_minor) = explode("/", $mime_type);

    $filename_bits = explode(".", $filename);
    if (count($filename_bits) == 1) {
      $file_ext = NULL;
    } else $file_ext = array_pop($filename_bits);

    // global check for filenames we NEVER want to accept
    if (in_array(strtolower($file_ext), array("php", "html", "js", "swf", "exe", "scr")))
    throw new PAException(INVALID_FILE, sprintf(__("Files with extension '%s' may not be uploaded"), $file_ext));

    // image type check
    if ($mime_major == "image") {
      $sc = @getimagesize($path);
      if (!$sc) throw new PAException(INVALID_FILE, "Invalid image file");
      // so it's an image file.  check extension.
      if (!preg_match("|/([\w]+)$|", $sc['mime'], $m))
      throw new PAException(INVALID_FILE, __("Unable to determine image type"));
      $mime_ext = $m[1];
      // check full MIME type if we have one
      if ($mime_minor && $mime_type != $sc['mime'])
      throw new PAException(INVALID_FILE, sprintf(__("File MIME type is %s, expected %s"), $sc['mime'], $mime_type));
      // correct or add file extension
      $file_ext = $mime_ext;
    }

    // all done - rebuild filename
    $filename = implode(".", $filename_bits);
    if ($file_ext) $filename .= ".".$file_ext;
    return $filename;
  }

}

?>