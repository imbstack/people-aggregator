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

(Minimal) Pure PHP Subversion client.
  Phillip Pearson, 2006-06-12
  Copyright (C) 2006 Broadband Mechanics

 This file implements a subset of the Subversion client's
 functionality, just enough to let us query a Subversion server over
 HTTP for differences between a given revision and the head, and apply
 the diffs.
 
 Written to let us use a Subversion server to back our auto-update
 functionality.
 
 To do:

 - call $state to update version numbers and checksums (and deletion
   flag?) on files/directories that we DO add/update/delete.

 - figure out how to detect if we can delete a directory.

 - gzip support

*/

require_once "Net/Socket.php";
require_once "HTTP/Request.php";
require_once "HTTP/Request/Listener.php";
require_once "Subversion/Common.php";

class Subversion_ExitBlock extends Exception {
}

class Subversion_Svndiff {

    // parser for svndiff format (txdelta)
    // detail: http://svn.collab.net/viewvc/*checkout*/svn/trunk/notes/svndiff
    
    function __construct($tx, $debug=false) {
        $this->tx = $tx;
        $this->debug = $debug;
    }
    
    function apply($source=NULL) {
        if ($this->debug) echo "parsing txdelta: ".strlen($this->tx)." bytes\n";
        $this->source = $source;
        $this->target = "";

        $this->len = strlen($this->tx);
        $this->pos = 0;

        if ($this->getc() != 'S'
            || $this->getc() != 'V'
            || $this->getc() != 'N'
            || $this->getc() != 0) {
            throw new Subversion_Failure("svndiff document doesn't start with SVN\\0");
        }

        while ($this->pos < $this->len) {
            $this->parse_window();
        }

        if ($this->pos != $this->len) throw new Subversion_Failure("position mismatch after reading all windows: pos=$this->pos, but we should be at the end: len=$this->len");

        return $this->target;
    }

    function getc() {
        if ($this->pos >= $this->len) throw new Subversion_Failure("run out of data in txdelta");
        return $this->tx[$this->pos++];
    }

    function get_data($len) {
        if ($len > $this->len - $this->pos) throw new Subversion_Failure("run out of data in txdelta (attempting to read $len bytes, but we only have ".($this->len - $this->pos)." left)");
        $data = substr($this->tx, $this->pos, $len);
        $this->pos += $len;
        return $data;
    }

    function get_int() {
        $r = 0;
        while (1) {
            $c = ord($this->getc());
            $r = ($r << 7) | ($c & 0x7f);
            if (!($c & 0x80)) break;
        }
        return $r;
    }

    function parse_window() {
        if ($this->debug) echo "parsing window at $this->pos: ";
        $src_view_offset = $this->get_int();
        $src_view_len = $this->get_int();
        $targ_view_len = $this->get_int();
        $ins_len = $this->get_int();
        $data_len = $this->get_int();

        if ($this->debug) echo "src view offset $src_view_offset, src view len $src_view_len, target view len $targ_view_len, instructions: $ins_len, data: $data_len\n";

        $win_target = "";

        // process instructions
        $ins_start = $this->pos;
        $ins_end = $ins_start + $ins_len;
        $data_pos = $ins_end;
        $data_end = $data_pos + $data_len;
        while ($this->pos < $ins_end) {
            if ($this->debug) echo "[@".($this->pos - $ins_start)."/$ins_len] ";
            $c = ord($this->getc());
            $ins = $c >> 6;
            $copy_len = $c & 0x3f;
            if (!$copy_len) $copy_len = $this->get_int();

            if ($this->debug) $target_start = strlen($win_target);
            switch ($ins) {
            case 0:
                $copy_ofs = $this->get_int();
                if ($this->debug) echo "copy from source: offset $copy_ofs, len $copy_len\n";
                if ($copy_ofs > strlen($this->source)) throw new Subversion_Failure("can't copy from past the end of the source; source len=".strlen($this->source)." but trying to copy from offset $copy_ofs");
                if ($copy_ofs + $copy_len > strlen($this->source)) throw new Subversion_Failure("copy region extends past the end of the source");
                $win_target .= substr($this->source, $src_view_offset + $copy_ofs, $copy_len);
                break;
            case 1:
                $copy_ofs = $this->get_int();
                if ($this->debug) echo "copy from target (offset $copy_ofs, len $copy_len)\n";
                if ($copy_ofs > strlen($win_target)) throw new Subversion_Failure("can't copy from past the end of the target");
                for ($i = 0; $i < $copy_len; ++$i)
                    $win_target .= $win_target[$copy_ofs++];
                break;
            case 2:
                if ($this->debug) echo "copy from data (len $copy_len)\n";
                if ($copy_len > $data_end - $data_pos) throw new Subversion_Failure("can't copy $copy_len bytes from data as only ".($data_end-$data_pos)." are available");
                $win_target .= substr($this->tx, $data_pos, $copy_len);
                $data_pos += $copy_len;
                break;
            default:
                throw new Subversion_Failure("invalid instruction code: $ins");
            }
            if ($this->debug) echo "wrote to target: [".substr($win_target, $target_start)."]\n";
        }
        if ($data_pos != $data_end) throw new Subversion_Failure("not all data used: pos=$data_pos but end=$data_end");

        // skip over data
        if ($this->pos != $ins_end) throw new Subversion_Failure("after reading instructions, we should be at pos $ins_end, but instead we're at $this->pos");
        $this->pos += $data_len;

        if (strlen($win_target) != $targ_view_len) throw new Subversion_Failure("we were meant to add $targ_view_len bytes to the target, but actually ".strlen($win_target)." bytes were added");

        // add this chunk onto the target
        $this->target .= $win_target;

    }

}

// listener that writes output to a file
class Subversion_FileListener extends HTTP_Request_Listener {
    function __construct($f) {
        parent::__construct();
        $this->f = $f;
    }

    function update(&$subject, $event, $data = null) {
        if ($event == 'tick') {
            fwrite($this->f, $data);
        }
    }
}

class Subversion_StandaloneClient {

    /*
     * @param $diff_fn A filename: where you want to save the XML retrieved
     * from the server.
     *
     */
    function __construct($state, $diff_fn) {
        $this->state = $state;
        $this->displayer = NULL;
        $this->diff_fn = $diff_fn;
    }
    
    function clear_recent_errors() {
        $this->recent_errors = FALSE;
    }

    function has_recent_errors() {
        return $this->recent_errors;
    }

    function add_error($level, $err) {
        $this->recent_errors = TRUE;
	if ($this->dry_run) {
            if (!@$this->errors[$level]) $this->errors[$level] = array();
            $this->errors[$level][] = $err;
	    //$this->errors[] = array("level" => $level, "msg" => $err); 
            $this->out("ERROR: ($level) $err");
	} else {
	    throw new Subversion_Failure($err);
	}
    }

    function add_perms_error($fn, $type='change') {
        switch ($type) {
        case 'change':
            if (file_exists($fn)) {
                $this->add_error("perms", "Unable to write to $fn.  <br/>Try: <code>chmod go+w $fn</code>");
            } else {
                $this->add_error("perms", "Unable to create $fn.  <br/>Try: <code>chmod 777 ".dirname($fn)."</code>");
            }
            break;

        case 'delete':
            $this->add_error("perms", "Unable to delete $fn.  <br/>Try: <code>chmod go+w $fn</code>");
            break;

        default:
            throw new Subversion_Failure("invalid \$type passed to add_perms_error: $type");
        }
    }

    function out($txt) {
        if ($this->displayer) $this->displayer->display($txt);
        else echo $txt;
    }
    
    function check_err($r) {
        if (PEAR::isError($r)) {
            throw new Subversion_Failure("error: $r");
        }
    }

    // figure out if we can create a given directory
    function can_mkdir($full_path) {
        // if there's already something there, we definitely can't create it.
        if (file_exists($full_path)) return FALSE;

        // try making the directory...
        $ret = @mkdir($full_path);
        // and remove it
        @rmdir($full_path);

        return $ret;
    }

    // figure out if we can create or write to a given file
    function can_write_to($full_path) {

        // if the parent directory is scheduled to be created, assume
        // that all is well:
        if (in_array(dirname($full_path), $this->created_dirs)) {
            return TRUE;
        }

        // not in a scheduled dir - try creating/updating it and see
        // what happens:
        if (file_exists($full_path)) {
            // file already there - try opening for writing (nondestructively)
            $f = @fopen($full_path, "r+");
            if ($f) fclose($f);
            $ret = $f ? TRUE : FALSE;
        } else {
            // not there - try creating it
            $ret = @touch($full_path);
            // and get rid of it so we don't leave a zero-byte file behind
            @unlink($full_path);
        }

        return $ret;
    }

    function queue_rename($from_path, $to_path) {
        $this->rename_queue[] = array($from_path, $to_path);
    }

    function queue_delete($path) {
        $this->delete_queue[] = $path;
    }

    // hold a file back at an older revision
    function add_state_hold($path) {
        // sanity check - get_held_revision() will throw an exception if the file doesn't exist.
        $this->state->get_held_revision($path);
        $this->state_instructions[] = array("hold", $path);
    }

    function add_state_update($path, $kind, $checksum) {
        $this->state_instructions[] = array("update", $path, $kind, $checksum);
    }

    function add_state_delete($path) {
        $this->state_instructions[] = array("delete", $path);
    }

    function add_revert_rename($from_name, $to_name) {
        $this->revert_instructions[] = array("rename", $from_name, $to_name);
    }
    
    function add_revert_rmdir($name) {
        $this->revert_instructions[] = array("rmdir", $name);
    }

    function add_revert_delete($name) {
        $this->revert_instructions[] = array("delete", $name);
    }

    function rename_to_dotlocal($full_path) {
        $this->out("Moving $full_path out of the way");
        $new_path = $full_path.".local";
        $this->out("RENAME $full_path &rarr; $new_path");
        if (!rename($full_path, $new_path)) throw new Subversion_Failure("Unable to rename $full_path to $new_path");
        clearstatcache();
        $this->add_revert_rename($new_path, $full_path);
    }

    function process_delete_file_request($full_local_path) {
        $full_path = $this->state->root."/$full_local_path";

        $this->out("D $full_path\n");
        if (!$this->can_write_to($full_path)) {
            if ($this->is_soft_install) {
                $this->out("Unable to delete $full_path; skipping");
                $this->add_state_hold($full_local_path);
            } else {
                $this->add_perms_error($full_path, "delete");
            }
        } elseif ($this->state->is_modified($full_local_path)) {
            if ($this->is_soft_install) {
                $this->out("Not deleting $full_path as it has local modifications");
                $this->add_state_hold($full_local_path);
            } elseif ($this->is_force_install) {
                $this->rename_to_dotlocal($full_path);
            } else {
                $this->add_error("localmod", "The file $full_path has local modifications, but is scheduled to be deleted.");
            }
        } elseif (!$this->dry_run) {
            $this->queue_delete($full_path);
            $this->add_state_delete($full_local_path);
        }
    }
    
    // callback from HTTP_Response
    function update($req, $event, $data) {
        if ($event == "tick") {
            $this->received_bytes += strlen($data);
            $this->out("\rReceived $this->received_bytes bytes ...");
            fwrite($this->fp, $data);
        }
    }

    function enter_dir($dn) {
        $this->path_stack[] = $dn;
        $this->mk_current_path();
        if ($this->dry_run) {
            if (!is_dir($this->current_path) && !in_array($this->current_path, $this->created_dirs) && !in_array($this->current_path, $this->skipped_dirs)) {
                throw new Subversion_Failure("$this->current_path does not exist and is not scheduled to be created");
            }
        } elseif (in_array($this->current_path, $this->skipped_dirs)) {
            $this->out("Skipping all instructions under directory $this->current_path");
        } else {
            if (!is_dir($this->current_path)) throw new Subversion_Failure("Cannot enter directory: $this->current_path does not exist");
        }
    }

    function mk_current_path() {
        $this->local_path = ltrim(implode("/", $this->path_stack), "/");
        $this->current_path = rtrim($this->state->root."/$this->local_path", "/");
    }

    function leave_dir() {
        array_pop($this->path_stack);
        $this->mk_current_path();
    }

    function xml_open_tag($xp, $tag, $attrs) {
        switch ($tag) {
        case 'S:TARGET-REVISION':
            $this->target_revision = $attrs['REV'];
            break;
        case 'S:DELETE-ENTRY':
            $fn = $attrs['NAME'];
            $full_path = "$this->current_path/$fn";
            $full_local_path = ltrim("$this->local_path/$fn", "/");

            if (!file_exists($full_path)) {
                $this->out("Already deleted: $full_path");
            } elseif (is_dir($full_path)) {
                $this->out("prerequites for RMDIR $full_path:\n");
		$desc_list = $this->state->find_descendants($fn); // this always returns them in reverse order, i.e. safe deletion order
		foreach ($desc_list as $desc) {
		    $desc_path = $desc['path'];
                    $full_desc_path = "$this->current_path/$desc_path";
                    $full_local_desc_path = ltrim("$this->local_path/$desc_path", "/");

		    switch ($desc['kind']) {
		    case 'dir':
			$this->out("RMDIR $desc_path");
                        $this->queue_delete($full_desc_path);
                        $this->add_state_delete($full_local_desc_path);
                        break;

		    case 'file':
                        $this->process_delete_file_request($desc_path);
			break;
		    }
		}

                // and get rid of the parent
                $this->queue_delete($full_path);
            } elseif (is_file($full_path)) {
                $this->process_delete_file_request($full_local_path);
            } else {
                throw new Subversion_Failure("$full_path exists but is not a file or directory - can't delete it");
            }
            break;

        case 'D:HREF':
            $this->href = "";
            $this->reading_href = true;
            break;

        case 'S:TXDELTA':
            if (!$this->current_file)
                throw new Subversion_Failure("txdelta while not adding/opening file");
            $this->txdelta = "";
            $this->reading_txdelta = true;
            break;

        case 'V:MD5-CHECKSUM':
            $this->checksum = "";
            $this->reading_checksum = true;
            break;

        case 'S:OPEN-DIRECTORY':
            $dn = isset($attrs['NAME']) ? $attrs['NAME'] : '';
            $this->enter_dir($dn);
            break;

        case 'S:ADD-DIRECTORY':
            $dn = $attrs['NAME'];
            $full_path = "$this->current_path/$dn";
	    $full_local_path = ltrim("$this->local_path/$dn", "/");

            $skip_this_dir = FALSE;
            $dir_already_there = FALSE;

            if (in_array($this->current_path, $this->skipped_dirs)) {
                $skip_this_dir = TRUE;
            } elseif (is_dir($full_path)) {
                // Perhaps it's OK to just use the existing dir.  If
                // the update says mkdir, and the directory's already
                // there, svn would complain, but it's probably safe
                // to use it.
                $dir_already_there = TRUE;
	    } elseif (file_exists($full_path)) {
                if ($this->is_force_install) {
                    // rename the file away
                    $this->rename_to_dotlocal($full_path);
                } elseif ($this->is_soft_install) {
                    // skip the whole directory
                    $skip_this_dir = TRUE;
                } else {
                    // normal install mode - raise an error
                    $this->add_error("localmod", "There is a file at $full_path, but the update contains the instruction to create a directory there.");
                    // see if we can make a directory in $this->current_path anyway, in case the user selects 'force-apply'
                    while (1) {
                        $random_name = "$this->current_path/".md5(rand()).".tmp";
                        if (!file_exists($random_name)) {
                            if (!$this->can_mkdir($random_name)) {
                                $this->add_error("perms", "Unable to create directories under $this->current_path.  <br/>Try: <code>chmod 777 $this->current_path</code>");
                            }
                            break;
                        }
                    }
                    // see if we can rename the file
                    if (!$this->can_write_to($full_path)) {
                        $this->add_perms_error($full_path);
                    }
                    $skip_this_dir = TRUE;
                }
	    }

            if ($skip_this_dir) {
                $this->out("SKIP $full_path\n");
                $this->skipped_dirs[] = $full_path;
                $this->add_state_hold($full_local_path);
            } elseif ($dir_already_there) {
                $this->out("CHDIR $full_path\n");
            } else {
                $this->out("MKDIR $full_path\n");
                if (!$this->dry_run || !in_array($this->current_path, $this->created_dirs)) {
                    if (!$this->can_mkdir($full_path)) {
                        $this->add_error("perms", "Unable to make directory $full_path.  <br/>Try: <code>chmod 777 $this->current_path</code>");
                    }
                }
		$this->created_dirs[] = $full_path;
		if (!$this->dry_run) {
		    if (!@mkdir($full_path)) throw new Subversion_Failure("Failed to make dir $full_path.");
                    chmod($full_path, 0777); // in case the web server's umask stopped us creating it like that before
                    clearstatcache();
                    $this->add_revert_rmdir($full_path);
                    $this->add_state_update($full_local_path, "dir", NULL);
		}
	    }

            // add this to the dir stack
            $this->enter_dir($dn);
            break;

        case 'S:ADD-FILE':
            $this->adding_file = true;
            // fall through
        case 'S:OPEN-FILE':
            $this->current_file = $attrs['NAME'];
            $this->checksum = null;
            break;
        }
    }

    function xml_close_tag($xp, $tag) {
        switch ($tag) {
        case 'D:HREF':
            $this->reading_href = false;
            break;

        case 'S:TXDELTA':
            $this->reading_txdelta = false;
            break;

        case 'V:MD5-CHECKSUM':
            $this->reading_checksum = false;
            break;

        case 'S:OPEN-DIRECTORY':
        case 'S:ADD-DIRECTORY':
            $this->leave_dir();
            break;

        case 'S:ADD-FILE':
        case 'S:OPEN-FILE':
            $this->clear_recent_errors();

            $full_path = "$this->current_path/$this->current_file";
	    $full_local_path = ltrim("$this->local_path/$this->current_file", "/");

            if (!$this->checksum) throw new Subversion_Failure("Updates is missing a checksum for file $full_path");

            // A or U
            $op_display_as = "";

            // TRUE if we are saving as $fn.updated, which needs
            // renaming later on, i.e. if this is an update or it's an
            // addition with an existing file in the way.
            $is_update = TRUE;

            // TRUE if we have to download the file from the server
            // (i.e. we're in force mode and a file to be updated has
            // been modified or deleted).
            $is_download = FALSE;

            // TRUE if the file has been modified or deleted by the user.
            $is_modified = TRUE;

	    try {
                // if the parent dir is skipped, skip the file too
		if (in_array($this->current_path, $this->skipped_dirs)) {
                    $this->out("SKIP $full_path");
                    $this->add_state_hold($full_local_path);
		    throw new Subversion_ExitBlock();
		}

                if (file_exists($full_path) && md5_file($full_path) == $this->checksum) {
                    $this->out("$full_path is already at revision $this->target_revision.");
                    $this->add_state_update($full_local_path, "file", $this->checksum);
                    throw new Subversion_ExitBlock();
                }

		if ($this->adding_file) {
                    // adding a file (A)
                    $op_display_as = "A";
		    if (!file_exists($full_path)) {
                        // there isn't already a file there - good!
                        $is_update = FALSE;
                        $is_modified = FALSE;
                    } else {
                        // there is already a file in the way.  
                        if ($this->is_soft_install) {
                            $this->out("Skipping new file $full_path as there is already a file in the way.");
                            $this->add_state_hold($full_local_path);
                            throw new Subversion_ExitBlock();
                        }

                        if ($this->is_force_install) {
                            // force-install: rename it out of the way
                            $this->rename_to_dotlocal($full_path);
                        } else {
                            $this->add_error("localmod", "There is already a file at $full_path, but the update contains a file to install there.");
                        }
		    }

		    $content = NULL;
		} else {
                    // patching a file (U)
                    $op_display_as = "U";
                    $save_download_info = FALSE; // TRUE if a force-apply will need to download this file
                    if (!is_file($full_path)) {
                        // the file has been deleted
                        if ($this->is_soft_install) {
                            $this->out("Skipping modification of $full_path as the file has been deleted.");
                            $this->add_state_hold($full_local_path);
                            throw new Subversion_ExitBlock();
                        }

                        if ($this->is_force_install) {
                            $is_download = TRUE;
                        } else {
                            $this->add_error("localmod", "The update contains instructions to modify $full_path, but it doesn't exist.");
                            $save_download_info = TRUE;
                        }
		    } elseif ($this->state->is_modified($full_local_path)) {
                        // file to patch has local modifications
                        if ($this->is_soft_install) {
                            $this->out("Skipping $full_path due to local modifications.");
                            $this->add_state_hold($full_local_path);
                            throw new Subversion_ExitBlock();
                        }
                        
                        if ($this->is_force_install) {
                            $this->out("A newer version of $full_path is available but it has been modified; replacing it with the latest version from the repository");
                            $this->rename_to_dotlocal($full_path);
                            $is_download = TRUE;
                        } else {
                            $this->add_error("localmod", "The file $full_path has local modifications, which may conflict with modifications in the update.");
                            $save_download_info = TRUE;
                        }
		    } else {
                        // the file is there and hasn't been modified - good!
                        $content = file_get_contents($full_path);
                        $is_modified = FALSE;
                    }

                    if ($save_download_info) {
                        $this->to_download[] = array(
                            'full_path' => $full_path,
                            'full_local_path' => $full_local_path,
                            'href' => $this->href,
                            'checksum' => $this->checksum,
                            );
                    }
		}

                // now test for accessibility
                if (!$this->can_write_to($full_path)) {
                    if ($this->is_soft_install) {
                        $this->out("Skipping $full_path as unable to create/update it.");
                        $this->add_state_hold($full_local_path);
                        throw new Subversion_ExitBlock();
                    }
                    $this->add_perms_error($full_path);
                }

                if (!$is_update) {
                    $file_path = $full_path;
                } else {
                    $file_path = $full_path.".updated";
                    if (!$this->can_write_to($file_path)) {
                        if ($this->is_soft_install) {
                            $this->out("Skipping $full_path as unable to create/update it");
                            $this->add_state_hold($full_local_path);
                            throw new Subversion_ExitBlock();
                        }
                        $this->add_perms_error($file_path);
                    }
                }

                $this->out("$op_display_as $full_path");
                    
                if ($is_download) {
                    // we had to download this one - so read it from where we got it from
                    $download_path = $full_path . ".r". $this->target_revision;
                    if (!file_exists($download_path)) throw new Subversion_Failure("$download_path has not been downloaded");
                    $new_content = file_get_contents($download_path);
                    $this->queue_delete($download_path);
                    if ($new_content == FALSE) throw new Subversion_Failure("Unable to read $download_path");
                } elseif (!$this->has_recent_errors()) {
                    // if we got this far, we must have a patch for the file.
                    if (!$this->txdelta) throw new Subversion_Failure("Update is missing a text delta for file $full_path");
                    // find file contents by evaluating txdelta
                    $d = new Subversion_Svndiff(base64_decode($this->txdelta));
                    $new_content = $d->apply($content);
                } else {
                    $this->out("Errors occurred; not continuing with $full_path");
                    throw new Subversion_ExitBlock();
                }
                    
                $comp_checksum = md5($new_content);
                if ($this->checksum != $comp_checksum) {
                    throw new Subversion_Failure("checksums do not match for $full_path; svn says we should get $this->checksum but I calculated $comp_checksum.");
                }

                if (!$this->dry_run) {
                    file_put_contents($file_path, $new_content);
                    clearstatcache();
                    $this->add_revert_delete($file_path);
                    if ($is_update) {
                        $this->queue_rename($file_path, $full_path);
                    }
                    $this->add_state_update($full_local_path, "file", $this->checksum);
                }
	    } catch (Subversion_ExitBlock $e) {
		// do nothing - Subversion_ExitBlock is just a replacement for 'break'
		// that we use so we don't skip the deinitialization code below.
	    }
	    $this->current_file = $this->txdelta = $this->checksum = null;
	    $this->adding_file = false;
            break;
        }
    }

    function xml_data($xp, $data) {
        if ($this->reading_txdelta) {
            $this->txdelta .= $data;
        } else if ($this->reading_checksum) {
            $this->checksum .= $data;
        } else if ($this->reading_href) {
            $this->href .= $data;
        }
    }

    /* Retrieve a tree diff from a Subversion server using the REPORT
     * command, from a given revision to the current head.
     *
     * @param $repo_url URL of the repository root
     * (e.g. "http://svn.automattic.com/wordpress/").
     *
     * @param $repo_sub_path Path from the repository root to the
     * folder you want to check out (e.g."trunk").
     *
     * @param $current_rev The revision to diff FROM.
     *
     * e.g., to save an XML tree diff from revision 3400 to the current head
     * of Wordpress as wp.3400.treediff.xml, call
     * checkout("http://svn.automattic.com/wordpress/", "trunk", 3400, "wp.3400.treediff.xml");
     *
     */
    function checkout() { //$repo_url, $repo_sub_path, $current_rev) {
        $repo_url = $this->state->get_repository_root();
        $repo_sub_path = $this->state->get_repository_path();
        $current_rev = $this->state->get_revision();
        
        $src_path = $repo_url . $repo_sub_path;
        
        // figure out where to send our http REPORT request
        $bits = parse_url($repo_url . "/!svn/vcc/default");
        $repo_host = $bits['host'];
        $repo_port = @$bits['port']; if (!$repo_port) $repo_port = 80;
        $repo_path = $bits['path'];

        $this->out("Connecting to host $repo_host:$repo_port,<br>sending HTTP REPORT $repo_path for src-path $src_path.\n");
        
        $xml = '<S:update-report send-all="true" xmlns:S="svn:"><S:src-path>'.$src_path.'</S:src-path><S:entry rev="'.$current_rev.'"></S:entry></S:update-report>';
        $xml_len = strlen($xml);
        
        $query = "REPORT $repo_path HTTP/1.0
Host: $repo_host
Content-Type: text/xml
Content-Length: $xml_len
Depth: 0

$xml";

        $this->out($query);

        $sock = new Net_Socket();
        $this->check_err($sock->connect($repo_host, $repo_port, null, 60, null)); // 60 sec timeout
        
        $this->check_err($sock->write($query));

        // read response
        $this->out("Downloading patch ...\n");
        $this->fp = fopen($this->diff_fn, "wt");
        if (!$this->fp) throw new Subversion_Failure("can't open $this->diff_fn file");
        $listeners = array($this);
        $this->received_bytes = 0;
        $resp = new HTTP_Response($sock, $listeners);
        $this->check_err($resp->process(false));
        fclose($this->fp);
        $this->out("\nFinished downloading update ($this->received_bytes bytes).\n");
    }

    /*

    Call this to apply a saved SVN XML diff (REPORT response) to a
    source tree.

    Pass an array of filenames as $skip if you have files you want to
    keep from being updated.

    Leave $apply==FALSE to do a 'dry run', i.e. make sure that we are
    actually able to apply the diff to the tree.  If something goes
    wrong, it will throw a 'Subversion_Failure' exception object.

    Once you have ascertained that it actually *will* apply cleanly,
    run this again with $apply=TRUE to actually make the change.

    */
    function apply_patch($apply=FALSE) { //$skip=array(), $apply=FALSE) {
        if (!in_array($apply, array(FALSE, TRUE, "force", "soft"))) throw new Subversion_Failure("illegal value for \$apply: $apply");
        $this->apply_type = $apply;
        $this->dry_run = !$apply;
        $this->is_soft_install = ($apply == "soft");
        $this->is_force_install = ($apply == "force");

        $this->current_rev = $this->state->get_revision();

        // dirs that have been created
        $this->created_dirs = array();
        // files that have been created
//        $this->created_files = array();
        // rename instructions to run at end of patch
        $this->rename_queue = array();
        // delete instructions to run at end of patch
        $this->delete_queue = array();
        // directories that have been skipped
        $this->skipped_dirs = array();
	// list of errors (just strings) to pass back to the client
	$this->errors = array();
        // list of instructions to execute (in reverse order) to revert all changes
        $this->revert_instructions = array();
        // list of instructions to execute on the DB once everything succeeds
        $this->state_instructions = array();
        // files that need to be downloaded if we want to blow away
        // local changes (as we can't apply patches to
        // locally-modified files - we need to go back to the server
        // and grab a pristine copy).
        $this->to_download = array();

        // state vars for use when parsing xml
        $this->path_stack = array();
        $this->reading_href = $this->reading_txdelta = $this->reading_checksum = $this->adding_file = $this->target_revision = false;

        try {
            // prepare to parse the response
            $this->xp = xml_parser_create();
            xml_set_object($this->xp, $this);
            xml_set_element_handler($this->xp, "xml_open_tag", "xml_close_tag");
            xml_set_character_data_handler($this->xp, "xml_data");
            
            // now parse the response
            $f = fopen($this->diff_fn, "rt");
            if (!$f) throw new Subversion_Failure("can't open $this->diff_fn");
            while (!feof($f)) {
                $data = fread($f, 8192);
                $r = xml_parse($this->xp, $data);
                if (!$r) {
                    throw new Subversion_Failure("XML error: ".xml_error_string(xml_get_error_code($this->xp))." at line ".xml_get_current_line_number($this->xp));
                }
            }
            fclose($f);
            
            // clean up xml parser
            xml_parser_free($this->xp);

            // commit unsaved changes
            if (!$this->dry_run) {
                $this->out("Processing rename queue...");
                foreach ($this->rename_queue as $names) {
                    list($from_name, $to_name) = $names;
                    if (file_exists($to_name)) {
                        // there's already something in the $to_name
                        // position, so rename it out of the way first
                        // and queue to be deleted if all goes well.
                        $temp_name = tempnam(dirname($to_name), basename($to_name).".pre-update.");
                        if (!$temp_name) throw new Subversion_Failure("Unable to make temporary file in ".dirname($to_name));
                        
                        $this->out("RENAME $to_name &rarr; $temp_name\n");
                        if (!rename($to_name, $temp_name)) {
                            unlink($temp_name);
                            throw new Subversion_Failure("Unable to rename $to_name to $temp_name");
                        }
                        $this->add_revert_rename($temp_name, $to_name);
                        $this->queue_delete($temp_name);
                    }

                    $this->out("RENAME $from_name &rarr; $to_name\n");
                    if (!rename($from_name, $to_name)) throw new Subversion_Failure("Unable to rename $from_name to $to_name");
                    $this->add_revert_rename($to_name, $from_name);
                }
            }
        } catch (Subversion_Failure $e) {
            // something failed!  revert what we've done.
            $this->out("AN ERROR OCCURRED (".$e->getMessage().") - UNDOING ALL OPERATIONS");
            foreach (array_reverse($this->revert_instructions) as $ins) {
                $code = $ins[0];

                switch ($code) {
                case 'rename':
                    list(, $from_name, $to_name) = $ins;
                    $this->out("RENAME $from_name &rarr; $to_name");
                    rename($from_name, $to_name);
                    break;
                case 'delete':
                    list(, $name) = $ins;
                    $this->out("DELETE $name");
                    unlink($name);
                    break;
                case 'rmdir':
                    list(, $name) = $ins;
                    $this->out("REMOVE DIRECTORY $name");
                    rmdir($name);
                    break;
                default:
                    $this->out("ERROR: unknown revert instruction type: $code");
                    break;
                }
            }
            $this->out("UNDO COMPLETE");
            throw $e;
        }

        if (!$this->dry_run) {
            // and finally delete all files/directories queued for
            // deletion, and all temp files.
            $this->out("Processing delete queue...");
            foreach ($this->delete_queue as $name) {
                $this->out("DELETE $name");
                if (is_dir($name)) {
                    rmdir($name);
                } else {
                    unlink($name);
                }
            }
            unlink($this->diff_fn);
            
            // now update the database
            foreach ($this->state_instructions as $ins) {
                $code = $ins[0];
                $path = $ins[1];
                switch ($code) {
                case 'hold':
                    $this->state->set_held_revision($path, $this->current_rev);
                    break;
                case 'update':
                    list(, , $kind, $hash) = $ins;
//                    $this->out("Updating $path ($kind) in DB to hash $hash, revision $this->target_revision");
                    $this->state->update_object($path, $kind, $hash, $this->target_revision);
                    break;
                case 'delete':
//                    $this->out("Deleting $path in DB");
                    $this->state->delete_object($path);
                    break;
                }
            }
            // and the global revision
            $this->state->set_revision($this->target_revision);
            
            // and we're done!
            $this->out("Update complete!");
        }

	return array(
            'target_revision' => $this->target_revision,
            'errors' => $this->errors,
            );
    }

    // turn $rel into a full url, using $root to resolve relative
    // references.
    //
    // (note that this is not exactly the same as Python's
    // urllib.basejoin; url_join("foo/bar", "baz") == "foo/bar/baz"
    // but urllib.basejoin("foo/bar", "baz") == "foo/baz).
    function url_join($root, $rel) {
        $root_info = parse_url($root);
        $rel_info = parse_url($rel);

        // is $rel a complete url?
        if (@$rel_info['scheme']) return $rel;

        // otherwise use the scheme/host from $root, and work out the path
        if (!@$root_info['scheme']) throw new Subversion_Failure("$root is not an absolute URL");

        // start with root_scheme://root_host
        $url = $root_info['scheme'].'://'.$root_info['host'];

        // if $rel starts with a slash, we ignore $root's path
        if (preg_match("|^/|", $rel_info['path'])) {
            // --> root_scheme://root_host/rel_path
            return $url . $rel_info['path'];
        }

        // looks like $rel's path is truly relative, so we concatenate the paths
        $url .= $root_info['path'];

        // does $rel have a path part?
        // --> root_scheme://root_host/root_path
        if (!@$rel_info['path']) return $url;

        // add a slash if required
        if (!preg_match("|/$|", $url)) $url .= "/";

        // and finish it off
        // --> root_scheme://root_host/root_path/rel_path
        return $url . $rel_info['path'];
    }

    function download_incomplete_files($tmp_dir) {
        foreach ($this->to_download as $dl) {
            // work out the url and final path
            $output_fn = $dl['full_path'] . '.r' . $this->target_revision;
            $url = $this->url_join($this->state->get_repository_root(), $dl['href']);
            $this->out("Downloading $url to $output_fn");

            // make sure the file isn't already there
            if (file_exists($output_fn) && md5_file($output_fn) == $dl['checksum']) {
                $this->out("Looks like we've already got it - good!");
                continue;
            }

            // looks like we'll have to download the file.  see if we
            // can write to the target location...
            if (!$this->can_write_to($output_fn)) throw new Subversion_Failure("I don't have permission to write to $output_fn.  <br/>Try: <code>chmod 777 ".basename($output_fn)."</code>");

            // likewise for the temp file
            $tmp_fn = tempnam($tmp_dir, "svn.download.tmp");
            $this->out("(using $tmp_fn as a temporary filename)");

            // and do the download
            $req = new HTTP_Request($url);
            $f = fopen($tmp_fn, "wb");
            if (!$f) throw new Subversion_Failure("Unable to open file $tmp_fn");
            $req->attach(new Subversion_FileListener($f));
            $req->sendRequest(FALSE);
            fclose($f);
            clearstatcache();

            // got it!  check checksum
            if (md5_file($tmp_fn) != $dl['checksum']) throw new Subversion_Failure("checksum of downloaded file $url does not match checksum in xml");

            // and move it to the final location
            if (!rename($tmp_fn, $output_fn)) {
                unlink($tmp_fn);
                throw new Subversion_Failure("Unable to save downloaded file as $output_fn");
            }
        }
    }
    
}

?>