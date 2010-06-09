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

/*
* This class takes care of storing / accessing the state of the
* working copy.  For the moment it stores everything in MySQL,
* because that's the most convenient thing to do in PeopleAggregator,
* but perhaps in future someone will write a
* Subversion_DotSvnStateStore class that understands .svn
* directories...
*
*/

require_once "db/Dal/Dal.php";
require_once "Subversion/Common.php";

class Subversion_PAStateStore {

  function __construct($root) {
    $this->root = $root;
  }

  // test to see whether the svn_meta table has a revision -
  // i.e. whether dist_files.txt has been inserted into the DB or
  // not.
  function is_initialized() {
    list ($ver) = Dal::query_one("SELECT revision FROM svn_meta LIMIT 1");
    return $ver ? TRUE : FALSE;
  }

  function get_revision() {
    list($rev) = Dal::query_one("SELECT revision FROM svn_meta LIMIT 1");
    return $rev;
  }

  function set_revision($rev) {
    Dal::query("UPDATE svn_meta SET revision=?", array($rev));
  }

  function get_repository_root() {
    list($root) = Dal::query_one("SELECT repos_root FROM svn_meta LIMIT 1");
    return $root;
  }

  function get_repository_path() {
    list($path) = Dal::query_one("SELECT repos_path FROM svn_meta LIMIT 1");
    return $path;
  }

  /* Ensure that our DB table exists, and reinitialize it with data
  * from db/dist_files.txt, which contains a list of files
  * distributed with this release.
  */
  function initialize() {
    // global var $path_prefix has been removed - please, use PA::$path static variable

    $this->revision = 0;

    Dal::query("TRUNCATE TABLE svn_objects");
    if(!$dist_files = @file_get_contents(PA::$project_dir."/db/dist_files.txt")) {
       $dist_files = @file_get_contents(PA::$core_dir."/db/dist_files.txt");
    }
    if (!$dist_files) {
      throw new Subversion_Failure("dist_files.txt does not exist - this installation does not include necessary files to use the auto-updater.  Perhaps you installed from Subversion?");
    }
    foreach (preg_split("/\n/", $dist_files) as $line) {
      $line = rtrim($line);
      if (preg_match("/Revision: (\d+)$/", $line, $m)) {
        $this->revision = (int)$m[1];
      }
      elseif (preg_match("/Repository Root: (.*)$/", $line, $m)) {
        $this->repos_root = $m[1];
      }
      elseif (preg_match("/Repository Local Path: (.*)$/", $line, $m)) {
        $this->repos_path = $m[1];
      }
      elseif (preg_match("/(Dir|File): (?:(.*?)\s+)?(.*)$/", $line, $m)) {
        $kind = strtolower($m[1]);
        $hash = $m[2];
        $fn = $m[3];
        $full_path = "$this->root/$fn";

        /* don't validate at this point - leave that to when
        * we actually want to do an update.

        switch ($kind) {
        case 'dir':
        if (!is_dir($full_path)) throw new Subversion_Failure("Directory $fn does not exist");
        break;

        case 'file':
        if (!is_file($full_path)) throw new Subversion_Failure("File $fn does not exist");
        if (md5_file($full_path) != $hash) throw new Subversion_Failure("File $fn has been modified");
        break;
        }

        */

        Dal::query("INSERT INTO svn_objects SET path=?, kind=?, hash=?", array($fn, $kind, $hash));
      }
    }

    if (!$this->revision) throw new Subversion_Failure("Could not find 'Revision: XXX' line in dist_files.txt");
    Dal::query("TRUNCATE TABLE svn_meta");
    Dal::query("INSERT INTO svn_meta SET revision=?, repos_root=?, repos_path=?", array($this->revision, $this->repos_root, $this->repos_path));

    // set each object's revision number
    Dal::query("UPDATE svn_objects SET revision=?, is_active=1", array($this->revision));
  }

  /* find_descendants($path) finds all descendants of a folder known
  * to Subversion - all the way down the tree.  This is used when
  * the server tells us to delete a folder, so we can avoid
  * deleting anything that isn't under version control.
  */
  function find_descendants($parent_path) {
    $ret = array();

    if (!preg_match("|/$|", $parent_path)) $parent_path .= "/";
    $sth = Dal::query("SELECT path, kind FROM svn_objects WHERE is_active=1 AND path LIKE '".Dal::quote($parent_path)."%' ORDER BY path DESC");
    while (list($path, $kind) = Dal::row($sth)) {
      $ret[] = array(
      'path' => $path,
      'kind' => $kind,
      );
    }

    return $ret;
  }

  function update_object($path, $kind, $hash, $revision) {
    Dal::query("INSERT INTO svn_objects SET path=?, kind=?, hash=?, revision=?, is_active=1",
    array($path, $kind, $hash, $revision));
    Dal::query("UPDATE svn_objects SET is_active=0 WHERE is_active=1 AND path=? AND revision < ?",
    array($path, $revision));
  }

  function delete_object($path) {
    Dal::query("UPDATE svn_objects SET is_active=0 WHERE is_active=1 AND path=?", array($path));
  }

  function get_all_modified() {
    $sth = Dal::query("SELECT kind,path,hash FROM svn_objects WHERE is_active=1 ORDER BY path");
    $changes = array();
    while (list($kind, $leaf, $hash) = Dal::row($sth)) {
      $path = "$this->root/$leaf";
      $change = $this->_check_modified($kind, $path, $hash);
      if ($change) $changes[] = array($kind, $leaf, $change);
    }
    if (!count($changes)) return NULL;
    return $changes;
  }

  function set_held_revision($path, $rev) {
    if (!Dal::query_one("SELECT kind FROM svn_objects WHERE path=? AND is_active=1", Array($path))) {
      throw new Subversion_Failure("Attempt to set held_revision for path $path, which isn't in the database");
    }
    Dal::query("UPDATE svn_objects SET held_revision=? WHERE path=? AND is_active=1", Array($rev, $path));
  }

  function get_held_revision($path) {
    $r = Dal::query_one("SELECT held_revision FROM svn_objects WHERE path=? AND is_active=1", Array($path));
    if (!$r) throw new Subversion_Failure("Attempt to get held revision for path $path, which isn't in the database");
    return $r[0];
  }

  function is_modified($path) {
    $r = Dal::query_one("SELECT kind,path,hash FROM svn_objects WHERE is_active=1 AND path=?", Array($path));
    if (!$r) throw new Subversion_Failure("Attempt to check modification for a nonexistent file $path");

    list($kind, $leaf, $hash) = $r;
    $path = "$this->root/$leaf";
    return $this->_check_modified($kind, $path, $hash);
  }

  private function _check_modified($kind, $path, $hash) {
    switch ($kind) {
      case 'dir':
        if (!is_dir($path)) return "deleted";
        break;

      case 'file':
        if (!is_file($path)) return "deleted";
        if (md5_file($path) != $hash) return "changed";
        break;
    }

    return NULL;
  }

}

?>