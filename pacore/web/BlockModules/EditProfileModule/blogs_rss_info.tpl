<?php

require "api/UserProfileFeed/UserProfileFeed.php";

$profile = &$this->user->{'blogs_rss'};

if (isset($_POST['submit']) && ($_POST['profile_type'] == 'blogs_rss')) {
  //code for setting the perm of blog_url and blog_feed same as blog_url
  $blogs = count($_POST['blog_title']);
  foreach ($_POST['blog_title'] as $counter => $v) {
    $perm = $_POST['blog_title'][$counter]['perm'];
    $_POST['blog_url'][$counter]['perm'] = $perm;
    $_POST['blog_feed'][$counter]['perm'] = $perm;

    $blog_url = $_POST['blog_url'][$counter]['value'];
    if (!empty($blog_url)) {
      if (!strstr($blog_url, "http://")) {
        $_POST['blog_url'][$counter]['value'] = "http://".$blog_url;
      }
    }
    $blog_feed = $_POST['blog_feed'][$counter]['value'];
    if (!empty($blog_feed)) {
      if (!strstr($blog_feed, "http://")) {
        $_POST['blog_feed'][$counter]['value'] = "http://".$blog_feed;
      }
    }
  }

  // $this is  DynamicProfile class instance
  $this->processPOST('blogs_rss');
  $this->save('blogs_rss', null, null, false);
	global $error_msg;
	$error_msg = __('Profile updated successfully.');
}

// getting the feed data respective to each blog_feed of user in user_profile.
$UserProfileFeed = new UserProfileFeed();
$UserProfileFeed->user_id = $_SESSION['user']['id'];
try {
  $UserProfileFeed->process_user_feeds();
} catch (PAException $e) {
  //TODO: Proper error message in case try fails
  //$msg = $e->message;
}

$status = BLOG_SETTING_STATUS_NODISPLAY;
if(!empty($this->user->{'general'}['BlogSetting']['value'])) {
  $status = $this->user->{'general'}['BlogSetting']['value'];  //$this->blogrss_setting_status;
}

require_once PA::$blockmodule_path . "/EditProfileModule/blog_setting.tpl";
?>

  <h1><?= __("Blogs/RSS") ?></h1>
    <form enctype="multipart/form-data" action="" method="post">
    <input type="hidden" name="profile_type" value="blogs_rss" />

    <fieldset>
      <input type="button" name="remove" value="<?= __("Add another Blog") ?>" onclick="blog_add(this); return false;" />
        <?php
          if(count(@$profile['blog_title'])) {
            ksort($profile['blog_title']);
          } else {
            // make an empty first blog
            $profile['blog_title'][1] = Array();
          }
        ?>
          <?php
          foreach ($profile['blog_title'] as $seq=>$v) {
          ?>
            <div id="blog_<?=$seq?>">
            <h2><?= __("Blog") ?>
            <!-- UI code to remove this blog -->
            <input type="button" name="remove[<?=$seq?>]"
              value="<?= __("Remove") ?>" onclick="blog_remove(this, '<?=$seq?>'); return false;" />
            </h2>
            <div class="select_access" style="text-align:right;"><?= __("Permission to view your Blog") ?></div>
            <?php
            $this->textfield(__("Blog Title"), 'blog_title', 'blogs_rss', $seq);
            $this->textfield(__("Blog URL"), 'blog_url', 'blogs_rss', $seq, false);
            $this->textfield(__("Blog Feed URL"), 'blog_feed', 'blogs_rss', $seq, false);
            ?>
            </div>
            <?php
          }
          ?>

          <script>
          var last_blog = <?=$seq?>;
          </script>
        </fieldset>

        <div class="button_position">
          <input type="submit" class="button-submit" name="submit" value="<?= __("Apply Changes") ?>" />
        </div>

      </form>