<?php

$use_theme = 'Beta';

$login_required = FALSE;
include_once("web/includes/page.php");
require_once "api/Cache/Cache.php";
require_once "web/includes/classes/Flickrclient.php";

session_write_close(); // close session and release lock, so other scripts can run at the same time as this one

$flickr_id = $_GET['flickr_id'];

// hold any errors we get
$flickr_errors = array();

// get links from cache if possible, otherwise fetch and store
$cache_key = "flickr_pics:$flickr_id";
$pics = Cache::getExtCache(0, $cache_key);
if ($pics === NULL) {
  $flickr = new Flickrclient();

  // first, see if we know the user's nsid.
  $cache_nsid_key = "flickr_nsid:$flickr_id";
  $nsid = Cache::getExtCache(0, $cache_nsid_key);
  if ($nsid === NULL) {
    $user_errors = array();
    // don't have it.  first see if $flickr_id is an e-mail address.
    if (strpos($flickr_id, "@") !== FALSE) {
      // it could possibly be an e-mail address, so pass it to flickr and try to get the nsid.
      try {
	$nsid = $flickr->people_findByEmail($flickr_id);
      } catch (PAException $e) {
	if ($e->getCode() != REMOTE_ERROR) throw $e;
	$user_errors[] = "<b>By e-mail address</b>: ".htmlspecialchars($e->getMessage());
      }
    }
    if (empty($nsid)) {
      // it can't have been an e-mail address; try it as a normal ID.
      try {
	$nsid = $flickr->people_findByUsername($flickr_id);
      } catch (PAException $e) {
	if ($e->getCode() != REMOTE_ERROR) throw $e;
	$user_errors[] = "<b>By username</b>: ".htmlspecialchars($e->getMessage());
      }
    }
    // if any of that succeeded, cache it for a month.  (as it should never change.)
    if (empty($nsid)) {
      $flickr_errors[] = "Failed to look up Flickr ID by e-mail or username:<br>".implode("<br>", $user_errors);
    } else {
      Cache::setExtCache(0, $cache_nsid_key, $nsid, "30 DAY");
    }
  }

  // if we have an nsid, try to get some pictures.
  if (!empty($nsid)) {
    try {
      $pics = $flickr->people_getPublicPhotos($nsid, 6, 1);
    } catch (PAException $e) {
      if ($e->getCode() != REMOTE_ERROR) throw $e;
      $flickr_errors[] = htmlspecialchars($e->getMessage());
    }
    
    // if we got something, save it in the cache
    if (!empty($pics)) {
      Cache::setExtCache(0, $cache_key, $pics);
    }
  }
}

// we (might) have pics: now render!
include "web/$current_theme_rel_path/flickr_pics.tpl";

?>