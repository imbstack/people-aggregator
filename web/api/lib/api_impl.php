<?php

// actual implementation of api functions goes here
require_once "db/Dal/Dal.php";
require_once "api/User/User.php";
require_once "api/User/Registration.php";
require_once "api/Category/Category.php";
require_once "api/ImageResize/ImageResize.php";
require_once "api/Message/Message.php";
require_once "api/MessageBoard/MessageBoard.php";
require_once "api/Network/Network.php";
require_once "api/Theme/Template.php";
require_once "api/Validation/Validation.php";
require_once "api/Storage/Storage.php";
require_once "ext/Album/Album.php";    
require_once "ext/Image/Image.php";
require_once "ext/Audio/Audio.php";
require_once "ext/Video/Video.php";
require_once "ext/BlogPost/BlogPost.php";
require_once "web/api/lib/project_api_impl.php";

// one day we'll move everything inside the API class ...
class API {
    public static $album_type_from_id = array(
	IMAGE_ALBUM => "image",
	AUDIO_ALBUM => "audio",
	VIDEO_ALBUM => "video",
	);
    
    public static $album_type_to_id = array(
	"image" => IMAGE_ALBUM,
	"audio" => AUDIO_ALBUM,
	"video" => VIDEO_ALBUM,
	);
}

// shortcut for making an error response
function api_err($code, $msg) {
    return array(
	'success' => FALSE,
	'code' => $code,
	'msg' => $msg,
	);
}

function api_err_from_exception($e) {
    $code_string = pa_get_error_name($e->code);
    $ret = api_err($code_string, $e->message);
    return $ret;
}

// calculates the number of pages given total item count and number of items per page
function api_n_pages($n_items, $perpage)
{
    return intval(ceil(floatval($n_items)/$perpage));
}

function peopleaggregator_echo($args)
{
    return array(
        'success' => TRUE,
        'echoText' => $args['echoText'],
        );
}

function peopleaggregator_echoPost($args)
{
    return array(
        'success' => TRUE,
        'echoText' => $args['echoText'],
        );
}

function peopleaggregator_errorTest($args)
{
    trigger_error("This is a test error.");
}

function peopleaggregator_exceptionTest($args)
{
    throw new PAException(GENERAL_SOME_ERROR, "This is a test exception.");
}

function api_split_search_query__token_push(&$ret, $tok, &$last_modifier)
{
    switch ($last_modifier) {
    case '-': $group = 'notwords'; break;
    case '+': $group = 'allwords'; break;
    default: $group = 'anywords'; break;
    }

    $ret[$group][] = $tok;
}

function api_split_search_query($q)
{
    $delims = array("'", '"'); // phrase delimiters
    $ws = array(" ", "\t", "\r", "\n"); // whitespace chars
    $modifiers = array("+", "-"); // standalone tokens

    $tokens = array(); // tokens collected

    $ret = array(
        'allwords' => array(),
        'phrase' => array(),
        'anywords' => array(),
        'notwords' => array(),
        );

    $tok = ""; // current token
    $in_tok = $in_phrase_tok = FALSE; // flag to say if we are reading a token
    $phrase_delim = FALSE; // delimiter for current quoted phrase (" or ')
    $last_modifier = FALSE; // last modifier seen (+, -, cleared after first token)

    // loop through chars and tokenise
    foreach (str_split($q." ") as $c) {
        if ($in_phrase_tok) {
            // (inside a quoted term)
            if ($c == $phrase_delim) {
                // phrase finished
                $ret['phrase'][] = $tok; 
                $tok = ""; $in_phrase_tok = FALSE; $last_modifier = FALSE;
            } else {
                $tok .= $c;
            }
        } else if ($in_tok) {
            // (inside a term)
            if (in_array($c, $ws)) {
                // token finished
                api_split_search_query__token_push($ret, $tok, $last_modifier);
                $tok = ""; $in_tok = FALSE; $last_modifier = FALSE;
            }
            else {
                $tok .= $c;
            }
        } else {
            // (not inside a term)
            if (in_array($c, $ws)) {
                // ignore
            } else if (in_array($c, $modifiers)) {
                // this char is a modifier (+, -, etc)
                $last_modifier = $c;
            } else if (in_array($c, $delims)) {
                // starting a quoted term
                $in_phrase_tok = TRUE;
                $phrase_delim = $c;
            } else {
                // starting an ordinary term
                $in_tok = TRUE;
                $tok = $c;
            }
        }
    }

    if ($in_tok) api_error("Still in token at end of search query - something wrong with parser");
    if ($in_phrase_tok) api_error("Unfinished quoted string");

    return $ret;
}

function api_load_user($login, $password) {
    $user = new User();
    $user->load($login);
    if ($user->password != md5($password)) {
	throw new PAException(USER_INVALID_PASSWORD, "Incorrect password for user $login");
    }
    return $user;
}

function api_resize_user_image($picture, $imageWidth, $imageHeight) {

    $picture = trim($picture);
    if (empty($picture)) return NULL;

    // ripped off from web/includes/image_resize.php (uihelper_preprocess_pic_path)
    if (defined("NEW_STORAGE") && preg_match("|^pa://|", $picture)) {
	     $picture = Storage::get($picture);
    } else {
	     $picture = "files/$picture";
	     if (!file_exists(PA::$project_dir. "/web/$picture") &&
           !file_exists(PA::$core_dir. "/web/$picture"))
           return NULL;
    }

    if ($imageWidth) {
	      // scale image down
	      $im_info = ImageResize::resize_img("web", PA::$url, "files/rsz", $imageWidth, $imageHeight, $picture);
    } else {
	      $im_size = @getimagesize(Storage::getPath($picture));
	      $im_info = array(
	        'url' => Storage::getURL($picture),
	        'width' => $im_size[0],
	        'height' => $im_size[1],
	      );
    }
    return array(
	    'url' => $im_info['url'],
	    'height' => (int)$im_info['height'],
	    'width' => (int)$im_info['width'],
	);
}

function peopleaggregator_login($args)
{
    $login = $args['login'];
    $pwd = $args['password'];

    $user = api_load_user($login, $pwd);

    $lifetime = 86400;
    $token = $user->get_auth_token($lifetime);

    return array(
	'success' => TRUE,
	'authToken' => $token,
	'tokenLifetime' => $lifetime,
	);
}

function peopleaggregator_checkToken($args)
{
    $token = $args['authToken'];

    $user = User::from_auth_token($token);

    return array(
	'success' => TRUE,
	'login' => $user->login_name,
	'tokenLifetime' => $user->auth_token_expires - time(),
	);
}

function peopleaggregator_getUserRelations($args)
{

	$login = $args['login'];
	$page = $args['page'];
	$perpage = $args['resultsPerPage'];
//	$detail = $args['detailLevel'];
	$imageSize = $args['profileImageSize'];
	if (preg_match("/^(\d+)x(\d+)$/", $imageSize, $m)) {
		$imageWidth = (int)$m[1];
		$imageHeight = (int)$m[2];
	} else $imageWidth = $imageHeight = 0;
	
	// look up user ID
	$user = new User();
	$user->load($login);

        $total = Relation::count_relations($user->user_id);
        $total_pages = api_n_pages($total, $perpage);

	$relations_out = array();
	foreach (Relation::get_all_relations($user->user_id, 0, FALSE, $perpage, $page) as $rel) {
		$rel_out = array(
                        'id' => 'user:'.$rel['user_id'],
			'login' => $rel['login_name'],
                        'relation' => $rel['relation_type'],
			'url' => PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $rel['user_id'],
		);
		$img_info = api_resize_user_image($rel['picture'], $imageWidth, $imageHeight);
		if ($img_info) $rel_out['image'] = $img_info;
		$relations_out[] = $rel_out;
	}

        if (sizeof($relations_out)) {
                $first = ($page - 1) * $perpage + 1;
                $msg = "Retrieved relations $first-".($first + sizeof($relations_out)) . " of $total for user $user->login_name.";
        } else {
                $msg = "Retrieved zero relations.";
                if ($page > $total_pages) {
                        $msg .= "  Try specifying a page number between 1 and $total_pages.";
                }
        }

	return array(
	    'success' => TRUE,
            'msg' => $msg,
	    'login' => $user->login_name,
	    'totalPages' => $total_pages,
	    'resultsPerPage' => $perpage,
	    'totalResults' => $total,
	    'page' => $page,
	    'relations' => $relations_out
	    );
}

function peopleaggregator_newUserRelation($args)
{
    $token = $args['authToken'];
    $dest_login = $args['login'];
    $rel_type_id = Relation::lookup_relation_type_id($args['relation']);

    $user = User::from_auth_token($token);

    $dest_user = new User();
    $dest_user->load($dest_login);

    // throw an exception if the relation already exists
    try {
	// this will throw RELATION_NOT_EXIST if the relation doesn't exist
	Relation::get_relation($user->user_id, $dest_user->user_id);
	// if we got this far, there must be a relation, which is bad
	throw new PAException(RELATION_ALREADY_EXISTS, "There is already a relation between users $user->login_name and $dest_user->login_name");
    } catch (PAException $e) {
	if ($e->code != RELATION_NOT_EXIST) throw $e;
    }

    // now add the relation
    Relation::add_relation($user->user_id, $dest_user->user_id, $rel_type_id);

    return array(
	'success' => TRUE,
	'msg' => 'Added relation',
	);
}

function peopleaggregator_deleteUserRelation($args)
{
    $token = $args['authToken'];
    $dest_login = $args['login'];

    $user = User::from_auth_token($token);

    $dest_user = new User();
    $dest_user->load($dest_login);

    // actually delete the relation.  this will throw a
    // RELATION_NOT_EXIST exception if the relation doesn't exist.
    Relation::delete_relation($user->user_id, $dest_user->user_id);

    return array(
	'success' => TRUE,
	'msg' => 'Deleted relation',
	);
}

function peopleaggregator_editUserRelation($args)
{
    $token = $args['authToken'];
    $dest_login = $args['login'];
    $rel_type_id = Relation::lookup_relation_type_id($args['relation']);

    $user = User::from_auth_token($token);

    $dest_user = new User();
    $dest_user->load($dest_login);

    // make sure there is a relation, and fail if not
    Relation::get_relation($user->user_id, $dest_user->user_id);

    // now make the change
    Relation::add_relation($user->user_id, $dest_user->user_id, $rel_type_id);

    return array(
	'success' => TRUE,
	'msg' => 'Edited relation',
	);
}

function peopleaggregator_getUserRelation($args)
{
    $login = $args['login'];
    $dest_login = $args['relation_login'];

    // look up user IDs
    $user = new User();
    $user->load($login);
    $dest_user = new User();
    $dest_user->load($dest_login);

    // get relation type id - this throws RELATION_NOT_EXIST if no relation exists
    $rel_type_id = Relation::get_relation($user->user_id, $dest_user->user_id);

    return array(
	'success' => TRUE,
	'msg' => 'Retrieved relation info.',
	'relation' => Relation::lookup_relation_type($rel_type_id),
	);
}

function peopleaggregator_getUserProfile($args)
{
    // global var $_base_url has been removed - please, use PA::$url static variable


    $login = $args['login'];

    $user = new User();
    $user->load($login);

    $profile = array();
    foreach (array(BASIC => "basic", GENERAL => "general", PERSONAL => "personal", PROFESSIONAL => "professional") as $slicekey => $slicename) {
	$section = array();
	foreach (User::load_user_profile($user->user_id, 0, $slicekey) as $info) {
	    $v = $info['value']; $k = $info['name'];
	    if ($v) {
		// some values require munging
		switch ($slicekey) {
		case GENERAL:
		    switch ($k) {
		    case 'dob':
			$v = new IXR_Date((int)$v);
			break;
		    }
		    break;
		}
		// now shove it in the results
		$section[$k] = $v;
	    }
	}
	$profile[$slicename] = $section;
    }

    return array(
        'success' => TRUE,
        'login' => $user->login_name,
        'id' => "user:".$user->user_id,
        'url' => PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id,
        'name' => "$user->firstName $user->lastName",
	      'profile' => $profile,
      );
}

function peopleaggregator_newUser($args)
{
    // check admin password
    global $admin_password;
    if (!$admin_password)
	throw new PAException(OPERATION_NOT_PERMITTED, "newUser API method may not be called without an admin password defined in local_config.php");
    if ($admin_password != $args['adminPassword'])
	throw new PAException(USER_INVALID_PASSWORD, "adminPassword incorrect");

    // fetch network info
    $home_network = Network::get_network_by_address($args['homeNetwork']);
    if (!$home_network)
	throw new PAException(INVALID_ID, "Network ".$args['homeNetwork']." not found");

    // register the user
    $reg = new User_Registration();
    if (!$reg->register(
	    array(
		'login_name' => $args['login'],
		'first_name' => $args['firstName'],
		'last_name' => $args['lastName'],
		'email' => $args['email'],
		'password' => $args['password'],
		'confirm_password' => $args['password'],
		), $home_network)) {
	return array(
	    'success' => FALSE,
	    'msg' => $reg->msg,
	    );
    }

    // success!
    $user = $reg->newuser;

    return array(
	'success' => TRUE,
	'msg' => "Created a user: id=$user->user_id; login=$user->login_name; firstName=$user->first_name; lastName=$user->last_name; email=$user->email; password=$user->password; joined to network id $home_network->network_id name $home_network->address",
	'id' => 'user:'.$user->user_id,
	);
}

function peopleaggregator_getUserList($args)
{
    $page = $args['page'];
    $perpage = $args['resultsPerPage'];
    $imageSize = $args['profileImageSize'];
      
    $showProfileImages = TRUE; $imageWidth = $imageHeight = 0;
    if ($imageSize == 'none') {
	$showProfileImages = FALSE;
    } else if ($imageSize == 'full') {
    } else if (preg_match("/^(\d+)x(\d+)$/", $imageSize, $m)) {
	$imageWidth = (int)$m[1];
	$imageHeight = (int)$m[2];
    }

    $total_users = User::count_users();
    $total_pages = api_n_pages($total_users, $perpage);

    $users = User::allUsers_with_paging(FALSE, $perpage, $page);
    $users_out = array();
    foreach ($users['users_data'] as $user) {
	$user_out = array(
	    "id" => "user:".$user['user_id'],
	    "login" => $user['login_name']
	    );
	if ($showProfileImages) {
	    $img_info = api_resize_user_image($user['picture'], $imageWidth, $imageHeight);
	    if ($img_info) $user_out['image'] = $img_info;
	}
	$users_out[] = $user_out;
    }

    return array(
        'success' => TRUE,
	'page' => $page,
	'resultsPerPage' => $perpage,
	'totalUsers' => $total_users,
	'totalPages' => $total_pages,
	'users' => $users_out
	);
}

function peopleaggregator_getCategories($args)
{
    $cats = Category::build_root_list("Group");
    $total_cats = sizeof($cats);

    $cats_out = array();
    foreach ($cats as $cat) {
	$cats_out[] = array(
	    "id" => "cat:".intval($cat->category_id),
	    "name" => $cat->name,
	    "groupCount" => intval($cat->total_threads), // misnomer: total_threads = # of groups in this category
	    );
    }

    return array(
	'success' => TRUE,
	'msg' => "Retrieved ".sizeof($cats_out)." categories",
	'totalResults' => $total_cats,
	'categories' => $cats_out,
	);
}

function peopleaggregator_getGroups($args)
{
    // global var $_base_url has been removed - please, use PA::$url static variable


    $page = $args['page'];
    $perpage = $args['resultsPerPage'];
    $context = $args['context'];

    if ($context == "global") {
        $total_groups = Group::get_all("", "all", TRUE);
        $groups = Group::get_all("", $perpage, FALSE, $perpage, $page);
    }
    elseif (preg_match("/^user:(\d+)$/", $context, $m)) {
        $uid = (int)$m[1];
        $user = new User();
        $user->load($uid);

        $total_groups = Group::get_user_groups($user->user_id, TRUE);
        $groups = Group::get_user_groups($user->user_id, FALSE, $perpage, $page);
        foreach ($groups as &$g) {
            // copy some attributes so that they have the same names as those from Group::get_all
            $g['group_id'] = $g['gid'];
            $g['title'] = $g['name'];
            $g['members'] = -1;
        }
    }
    else {
        throw new PAException(INVALID_ID, "Invalid group list context: $context");
    }

    $groups_out = array();
    foreach ($groups as $group) {
	$groups_out[] = array(
	    "id" => "group:".$group['group_id'],
	    "name" => $group['title'],
	    "memberCount" => intval($group['members']),
	    "url" => PA::$url . PA_ROUTE_GROUP . "/gid=" . $group['group_id'],
	    );
    }

    return array(
	'success' => TRUE,
	'msg' => "Retrieved ".sizeof($groups_out)." groups",
	'page' => $page,
	'resultsPerPage' => $perpage,
	'totalResults' => $total_groups,
	'totalPages' => api_n_pages($total_groups, $perpage),
	'groups' => $groups_out,
	);
}

function api_enum_to_int($possibilities, $value) {
    $pos = 0;
    foreach ($possibilities as $opt) {
        if ($value == $opt) return $pos;
        ++ $pos;
    }
    return -1;
}

function api_extract_id($id_prefix, $txt) {
    if (preg_match("/^$id_prefix:(\d+)$/", $txt, $m)) {
        return $m[1];
    }
    throw new PAException(INVALID_ID, "Invalid id '$txt'; must be of the form '$id_prefix:123'");
}

function peopleaggregator_newGroup($args)
{
    $token = $args['authToken'];
    $g_name = $args['name'];
    $g_desc = $args['description'];
    $g_pic = $args['image']; //FIXME: right now this won't work as a url - change to a base64 file?
    $g_tags = $args['tags'];
    $g_category_id = api_extract_id("cat", $args['category']);
    $access_type = api_enum_to_int(array("public", "members"), $args['accessType']);
    $reg_type = api_enum_to_int(array("open", "moderated", "invite"), $args['registrationType']);
    $mod_type = api_enum_to_int(array("direct", "moderated"), $args['moderationType']);

    $user = User::from_auth_token($token);

    // now create the group

    $group_id = Group::save_new_group(
        0, // collection id?
        $user->user_id,
        $g_name,
        $g_desc,
        $g_pic,
        $g_tags,
        $g_category_id,
        $access_type,
        $reg_type,
        $mod_type);

    return array(
        'success' => TRUE,
        'id' => "group:".$group_id,
        );
}

function api_parse_group_id($id) {
    if (preg_match("/^group:(\d+)$/", $id, $m)) {
        return $m[1];
    }
    else {
        throw new PAException(INVALID_ID, "Group ID must be of the form 'group:123' ('$id' is not valid)");
    }
}

function peopleaggregator_deleteGroup($args)
{
    $user = User::from_auth_token($args['authToken']);
    $ccid = api_parse_group_id($args['id']);

    // find group
    $g = api_load_group($ccid, $user);

    // are we the owner?
    if ($g->author_id != $user->user_id)
	throw new PAException(USER_ACCESS_DENIED, "Only the owner of a group can delete it");

    // kill it!
    $g->delete();

    return array(
	'success' => TRUE,
	);
}

function peopleaggregator_findGroup($args)
{
    $name = $args['name'];

    $groups = array();
    foreach (ContentCollection::findByTitle($name) as $group) {
	$groups[] = array(
	    "id" => "group:".$group['ccid'],
	    "name" => $group['title'],
	    );
    }

    return array(
	'success' => TRUE,
	'groups' => $groups,
	);
}

function peopleaggregator_joinGroup($args)
{
    $user = User::from_auth_token($args['authToken']);
    $ccid = api_parse_group_id($args['id']);

    // find group
    $g = ContentCollection::load_collection($ccid, $user->user_id);

    // join user to group
    if (!$g->join($user->user_id, $user->email))
	throw new PAException(OPERATION_NOT_PERMITTED, "Failed to join group");

    return array(
	'success' => TRUE,
	'joinState' => ($g->reg_type == $g->REG_MODERATED) ? "in_moderation" : "joined",
	);
}

function peopleaggregator_leaveGroup($args)
{
    $user = User::from_auth_token($args['authToken']);
    $ccid = api_parse_group_id($args['id']);

    // not a member?
    if (!Group::member_exists($ccid, $user->user_id))
	throw new PAException(OPERATION_NOT_PERMITTED, "User $user->login_name is not a member of that group");

    // trying to leave own group?
    if (Group::is_admin($ccid, $user->user_id))
	throw new PAException(OPERATION_NOT_PERMITTED, "Group leader cannot leave the group");

    // find group
    $g = ContentCollection::load_collection($ccid, $user->user_id);

    // remove user from group
    if (!$g->leave($user->user_id))
	throw new PAException(OPERATION_NOT_PERMITTED, "Unable to leave group");

    return array(
	'success' => TRUE,
	);
}

function peopleaggregator_getBoardMessages($args)
{
    if ($args['authToken'])
        $user = User::from_auth_token($args['authToken']);
    else
        $user = NULL;
    $context = $args['context'];
    $perpage = $args['resultsPerPage'];
    $page = $args['page'];

    if (preg_match("/^group:(\d+)$/", $context, $m)) {
        // getting list of topics for a group
        $parent_id = $m[1];
        $parent_type = "collection";
    }
    else if (preg_match("/^msg:(\d+)$/", $context, $m)) {
        // getting a thread
        $parent_id = $m[1];
        $parent_type = "message";
    }
    else {
        throw new PAException(INVALID_ID, "Only 'group' and 'msg' type IDs are valid contexts for getBoardMessages().");
    }

    $mb = new MessageBoard();
    $mb->set_parent($parent_id, $parent_type);
    $total_messages = (int)$mb->get(TRUE);
    $msgs = $mb->get(FALSE, $perpage, $page);

    $msgs_out = array();
    foreach ($msgs as $msg) {
        $m = array(
            "id" => "msg:".$msg['boardmessage_id'],
            "title" => $msg["title"],
            "content" => $msg["body"],
            "created" => $msg['created'],
            "author" => array(
                "id" => $msg["user_id"] ? "user:".$msg["user_id"] : "",
                ),
            );
        if ($msg["user_id"]) {
            $m["author"]["name"] = "".$msg["user_name"];
        }
        $msgs_out[] = $m;
    }

    return array(
        'success' => TRUE,
        'page' => $page,
        'resultsPerPage' => $perpage,
        'totalMessages' => $total_messages,
        'totalPages' => api_n_pages($total_messages, $perpage),
        'messages' => $msgs_out,
        );
}

function peopleaggregator_newBoardMessage($args)
{
    if ($args['authToken'])
        $user = User::from_auth_token($args['authToken']);
    else
        $user = NULL;
    $context = $args['context'];
    $title = $args['title'];
    $body = $args['content'];
    $allow_anon = $args['allowAnonymous'];

    if (preg_match("/^group:(\d+)$/", $context, $m)) {
        // posting a new topic to a group
        $parent_id = $m[1];
        $parent_type = "collection";

        //FIXME: check that we can access the group.  or does MessageBoard do this?
    }
    else if (preg_match("/^msg:(\d+)$/", $context, $m)) {
        // replying to an existing topic
        $parent_id = $m[1];
        $parent_type = "message";

        //FIXME: load parent, make sure it is a topic
        //FIXME: check if we are allowed to access this group
    }
    else {
        throw new PAException(INVALID_ID, "You can only post a message to a group or a topic.  Parent ID '$context' is not allowed.");
    }

    // create topic
    $cat_obj = new MessageBoard();
    $cat_obj->set_parent($parent_id, $parent_type);
    $cat_obj->title = $title;
    $cat_obj->body = $body;
    $cat_obj->user_id = $user ? $user->user_id : NULL;
    $cat_obj->allow_anonymous = $allow_anon ? 1 : 0;
    $mid = $cat_obj->save($cat_obj->user_id);

    return array(
        'success' => TRUE,
        'id' => "msg:".$mid,
        );
}

function peopleaggregator_getContent($args)
{
    // global var $_base_url has been removed - please, use PA::$url static variable


    $page = $args['page'];
    $perpage = $args['resultsPerPage'];
    $detail = $args['detailLevel'];
    $context = $args['context'];

    // fetch content from the appropriate location
    $must_retrieve_content = FALSE; // for most contexts, we don't need to retrieve the content

    if ($context == "global") {
        $total_items = Content::load_content_id_array(0, NULL, TRUE);
        $content = Content::load_content_id_array(0, NULL, FALSE, $perpage, $page);
    }
    else if (preg_match("/^group:(\d+)$/", $context, $m)) {
        $gid = $m[1];

        $g = new Group();
        $g->collection_id = $gid;

        $total_items = $g->get_contents_for_collection('all', TRUE);
        $content = $g->get_contents_for_collection('all', FALSE, $perpage, $page);
    }
    else if (preg_match("/^user:(\d+)$/", $context, $m)) {
        $uid = $m[1];

        $u = new User();
        $u->load((int)$uid);

        $total_items = Content::load_content_id_array($u->user_id, NULL, TRUE);
        $content = Content::load_content_id_array($u->user_id, NULL, FALSE, $perpage, $page);
    }
    else if (preg_match("/^tag:(\d+)$/", $context, $m)) {
        $tag_id = intval($m[1]);
        $total_items = Tag::get_associated_content_ids($tag_id, TRUE);
        $content = Tag::get_associated_content_ids($tag_id, FALSE, $perpage, $page);
        $must_retrieve_content = TRUE; // for tags, we have to fetch the rest of the content later on
    }
    else if (preg_match("/^search:(.*)$/", $context, $m)) {
        $terms = api_split_search_query($m[1]);
        $total_items = Content::content_search($terms, TRUE);
        $content = Content::content_search($terms, FALSE, $perpage, $page);
    }
    else {
        throw new PAException(INVALID_ID, "Invalid context '$context'");
    }

    if (!$content) $content = array(); //api_error("Content fetching function for context type $context (ID $context_id) returned no data");

    // format $content
    $items_out = array();
    foreach ($content as $item) {
        // FIXME: Do we need to call Content::load_content for
        // everything, to check permissions etc?  ShowContentModule
        // does (via uihelper_generate_center_content), but I'm not
        // sure if this is required or it's just been done this way
        // for convenience.

        // The $must_retrieve_content flag will be TRUE if we really
        // don't have the content.  If FALSE, title and body are
        // available already in $item.

        // For the moment, we call Content::load_content for
        // everything, just to be safe.
        $cid = intval($item['content_id']);

        $c = Content::load_content($cid, 0);

        if ($c->parent_collection_id > 0) {
            $content_id = "group:$c->parent_collection_id:$c->content_id";
        } else {
            $content_id = "user:$c->author_id:$c->content_id";
        }

        $i = array();
        switch ($detail) {
        case 'all':
            //fallthru
        case 'content':
            $i['content'] = str_replace("\r\n", "\n", $c->body);
            //fallthru
        case 'summary':
            $i['title'] = $c->title;
            //fallthru
        default:
            $i['id'] = $content_id;
            $i['url'] = PA::$url . PA_ROUTE_CONTENT . "/cid=$cid";
            break;
        }
        $items_out[] = $i;
    }

    return array(
        'success' => TRUE,
        'msg' => "Retrieved ".sizeof($items_out)." item(s).",
        'detailLevel' => $detail,
        'page' => $page,
        'resultsPerPage' => $perpage,
        'totalResults' => (int)$total_items,
        'totalPages' => api_n_pages($total_items, $perpage),
        'items' => $items_out,
        );
}

function peopleaggregator_newContent($args)
{
    // global var $_base_url has been removed - please, use PA::$url static variable


    $user = User::from_auth_token($args['authToken']);
    $uid = (int)$user->user_id;
    $context = $args['context'];
    $c_title = $args['title'];
    $c_content = $args['content'];
    $c_trackbacks = Content::split_trackbacks($args['trackbacks']);
    $c_tags = Tag::split_tags($args['tags']);

    list($ccid, $context, $group) = api_ccid_from_blogid($user, $context);

    $r = BlogPost::save_blogpost(
        0, $user->user_id,
        $c_title, $c_content, $c_trackbacks, $c_tags,
        $ccid);
    $cid = $r['cid'];

    return array(
        'success' => TRUE,
        'id' => "$context:$cid",
        'url' => PA::$url . PA_ROUTE_CONTENT . "/cid=$cid",
        'msg' => "Successfully added content",
        'errors' => $r['errors'],
        );
}

function blogger_getUserInfo($args) {
    $login = $args['login'];
    $password = $args['password'];

    $user = api_load_user($login, $password);

    // global var $_base_url has been removed - please, use PA::$url static variable


    return array(
        'nickname' => $user->login_name,
        'userid' => "user:".$user->user_id,
        'url' => PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id,
        'email' => $user->email,
        'lastname' => $user->last_name,
        'firstname' => $user->first_name,
        );
}

function blogger_getUsersBlogs($args) {
    $login = $args['login'];
    $password = $args['password'];

    $user = api_load_user($login, $password);

    // global var $_base_url has been removed - please, use PA::$url static variable


    $blogs = array();

    // add in user's personal blog
    $blogs[] = array(
        'isAdmin' => TRUE,
        'url' => PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user->user_id,
        'blogid' => "user:".$user->user_id,
        'blogName' => $user->login_name."'s blog",
        );

    // and all group blogs
    $groups = Group::get_user_groups($user->user_id);
    foreach ($groups as $group) {
        $blogs[] = array(
            'isAdmin' => ($group['access'] == "owner"),
            'url' => PA::$url . PA_ROUTE_GROUP . "/gid=" . $group['gid'],
            'blogid' => 'group:'.$group['gid'],
            'blogName' => $group['name'],
            );
    }

    return $blogs;
}

function metaWeblog_getCategories($args) {
    $blogid = $args['blogid'];
    $login = $args['login'];
    $password = $args['password'];

    $user = api_load_user($login, $password);

    return array(
	array(
	    "categoryId" => "none",
	    "categoryName" => "Uncategorized",
	    "description" => "Uncategorized items",
	    "rssUrl" => "not sure",
	    "htmlUrl" => "not sure",
	    )
	);
}

function metaWeblog_getRecentPosts($args) {
    // global var $_base_url has been removed - please, use PA::$url static variable


    $blogid = $args['blogid'];
    $login = $args['login'];
    $password = $args['password'];
    $n_posts = $args['n_posts'];

    $user = api_load_user($login, $password);

    // find content collection info from $blogid
    list($ccid, $post_id_prefix, $group) = api_ccid_from_blogid($user, $blogid);

    if (!$ccid) {
	// fetching the user's blog posts
	$posts = Content::load_content_id_array($user->user_id, NULL, FALSE, 0, $n_posts);
    } else {
	$posts = $group->get_contents_for_collection('all', FALSE, $n_posts, 1);
    }
    if (!$posts) $posts = array();

    $posts_out = array();
    foreach ($posts as $post) {
	$posts_out[] = array(
	    "userid" => "user:$user->user_id",
	    "postid" => "$post_id_prefix:".$post['content_id'],
	    "title" => $post['title'],
	    "description" => $post['body'],
	    "dateCreated" => new IXR_Date((int)$post['created']),
	    "link" => "",
	    "permaLink" => PA::$url . PA_ROUTE_CONTENT . "/cid=".$post['content_id'],
	    "categories" => array("Uncategorized"),
	    );
    }

    return $posts_out;
}

function metaWeblog_getPost($args) {
    // global var $_base_url has been removed - please, use PA::$url static variable


    $user = api_load_user($args['login'], $args['password']);
    list($ccid, $context, $group, $cid, $content) = api_parse_postid($user, $args['postid']);

    //FIXME: assert that the user is the owner of the content

    return array(
	"userid" => "user:$user->user_id",
	"postid" => "$context:".$content->content_id,
	"title" => $content->title,
	"description" => $content->body,
	"dateCreated" => new IXR_Date((int)$content->created),
	"link" => "",
	"permaLink" => PA::$url . PA_ROUTE_CONTENT . "/cid=".$content->content_id,
	"categories" => array("Uncategorized"),
	);
}

function api_load_group($ccid, $user) {
    $group = new Group();
    $group->load($ccid);
    $group->assert_user_access($user->user_id);
    return $group;
}

function api_ccid_from_blogid($user, $context) {
    $group = NULL;
    if (empty($context)) {
        // posting to the authenticated user's blog
        $ccid = 0;
        $context = "user:".$user->user_id;
    }
    else if (preg_match("/^user:(\d+)$/", $context, $m)) {
        if ($m[1] != $user->user_id) {
            // posting to another user's blog - not allowed (yet?)
            throw new PAException(OPERATION_NOT_PERMITTED, "You can't post to another user's blog");
        }
        $ccid = 0;
    }
    else if (preg_match("/^group:(\d+)$/", $context, $m)) {
        // posting to a group blog
        $ccid = (int)$m[1];
	$group = api_load_group($ccid, $user);
    }
    else {
        throw new PAException(INVALID_ID, "Context passed to newContent must be a group ID, a user ID or a blank string");
    }

    return array($ccid, $context, $group);
}

function api_parse_postid($user, $postid) {
    $id_bits = preg_split("/:/", $postid);
    list($ccid, $context, $group) = api_ccid_from_blogid($user, $id_bits[0].":".$id_bits[1]); // this checks that the user can access the ContentCollection
    $cid = (int)$id_bits[2];

    // now verify that this bit of content actually belongs to that collection
    //FIXME: how?
    $content = Content::load_content($cid, $user->user_id);
    Logger::log("loaded content $cid for user $user->user_id: ".var_export($content, TRUE)."\n");

    return array($ccid, $context, $group, $cid, $content);
}

function blogger_newPost($args) {
    $blogid = $args['blogid'];
    $login = $args['login'];
    $password = $args['password'];
    $content = $args['content'];
    $publish = $args['publish']; // ignored

    $user = api_load_user($login, $password);

    // figure out what we are posting to
    list($ccid, $post_id_prefix, $group) = api_ccid_from_blogid($user, $blogid);

    $r = BlogPost::save_blogpost(
        0, $user->user_id,
        "Blogger API post", $content, array(), array(),
        $ccid);
    $cid = $r['cid'];

    return "$post_id_prefix:$cid";
}

function metaWeblog_newPost($args) {
    $blogid = $args['blogid'];
    $login = $args['login'];
    $password = $args['password'];
    $post = $args['post'];
    $publish = $args['publish']; // ignored

    $user = api_load_user($login, $password);

    // figure out what we are posting to
    list($ccid, $post_id_prefix, $group) = api_ccid_from_blogid($user, $blogid);

    $r = BlogPost::save_blogpost(
        0, $user->user_id,
        $post['title'], $post['description'], array(), array(),
        $ccid);
    $cid = $r['cid'];

    return "$post_id_prefix:$cid";
}

function metaWeblog_editPost($args) {
    global $network_info;

    $postid = $args['postid'];
    $login = $args['login'];
    $password = $args['password'];
    $post = $args['post'];
    $publish = $args['publish']; // ignored

    $user = api_load_user($login, $password);

    list($ccid, $context, $group, $cid, $content) = api_parse_postid($user, $postid);

    // assert access
    if ($content->author_id != $user->user_id)
        throw new PAException(USER_ACCESS_DENIED, "Only the author can edit a post");
    
    // save changes to post
    BlogPost::save_blogpost(
        $cid, $user->user_id,
        $post['title'], $post['description'], array(), array(),
        $ccid);

    // invalidate caches
    $cache_id = 'content_'.$cid;
    if ($network_info) $cache_id .= '_network_'.$network_info->network_id;
    CachedTemplate::invalidate_cache($cache_id);
    Logger::log("invalidating cache for $cache_id");

    return true;
}

function blogger_deletePost($args) {
    $postid = $args['postid'];
    $login = $args['login'];
    $password = $args['password'];
    $publish = $args['publish']; // ignored

    $user = api_load_user($login, $password);

    // try to locate the content
    list($context, $context_id, $cid) = explode(":", $postid);
    //FIXME: $ccid isn't required for editing/deletion, but it would be nice to verify it
//    list($ccid, $post_id_prefix) = api_ccid_from_blogid($user, $context, $context_id);
    $cid = (int)$cid;

    // delete it!
    $content = Content::load_content($cid, $user->user_id);
    if($content->author_id == $user->user_id) {
        Content::delete_by_id($cid);
    }

    return TRUE;
}

function peopleaggregator_getFolders($args) {
    $user = User::from_auth_token($args['authToken']);

    $folders = array();
    foreach (Message::get_user_folders($user->user_id, TRUE) as $folder) {
        $folders[] = array(
            'name' => $folder['name'],
            );
    }

    return array(
        'success' => TRUE,
        'folders' => $folders,
        );
}

function peopleaggregator_getMessages($args) {
    $user = User::from_auth_token($args['authToken']);
    $detail = $args['detailLevel'];
    
    $sort_by = "sent_time";
    $sort_flag = 0; // descending order

    $msgs = Message::load_folder_for_user($user->user_id, $args['folder'], false, $args['page'], $args['resultsPerPage'], $sort_by, $sort_flag);

    $msgs_out = array();
    foreach ($msgs as $msg) {
        $recips = array();
        foreach (array_map("trim", explode(",", $msg['all_recipients'])) as $recip_login) {
            $recips[] = array("login" => $recip_login);
        }
        $m = array(
            'id' => "privmsg:".$msg["message_id"],
            'sender' => array(
                'id' => "user:".$msg["sender_id"],
                ),
            'recipients' => $recips,
            'title' => $msg["subject"],
            );
        if ($detail == "all") {
            $m['content'] = $msg['body'];
        }
        $msgs_out[] = $m;
    }

    return array(
        'success' => TRUE,
        'messages' => $msgs_out,
        );
}

function peopleaggregator_sendMessage($args) {
    $user = User::from_auth_token($args['authToken']);
    $recipients = $args['recipients'];
    $subject = $args['title'];
    $body = $args['content'];

    // actually send the message
    Message::add_message($user->user_id, NULL, $recipients, $subject, $body);
    
    return array(
        'success' => TRUE,
        //FIXME: should also have: 'id' => 'privmsg:'.$mid,
        );
}

function api_get_url_of_path($raw_path) {

    // normalize directory names and figure out URL to upload dir
    $path = realpath($raw_path);
    if (!$path) throw new PAException(GENERAL_SOME_ERROR, "Upload directory ($raw_path) does not exist");
    
    $prefix_path = realpath(PA::$path);
    if (!$prefix_path) throw new PAException(GENERAL_SOME_ERROR, "Something is broken, PA::\$path (".PA::$path.") directory does not exist");
    
    $base_path = "$prefix_path/web";
    
    if (strpos($path, $base_path) !== 0) throw new PAException(GENERAL_SOME_ERROR, "Upload directory ($uploaddir) appears to be outside PA::\$path (".PA::$path.")");
    
    $url = PA::$url . substr($path, strlen($base_path));
    return $url;
}

function peopleaggregator_getAlbums($args) {
    global $_PA;

    if (!empty($args['authToken'])) {
	$user = User::from_auth_token($args['authToken']);
	$display_user_id = $auth_user_id = $user->user_id;
    } else {
	$user = $display_user_id = $auth_user_id = NULL;
    }
    $context = $args['context'];

    $show_personal = $show_group = FALSE;
    if (preg_match("/^user:(\d+)$/", $context, $m)) {
	$show_personal = TRUE;
	$display_user_id = (int)$m[1];
    } else {
	if (empty($user)) throw new PAException(OPERATION_NOT_PERMITTED, "'user', 'group' and 'all' contexts are not allowed for anonymous users");
	switch ($context) {
	case 'user':
	    $show_personal = TRUE;
	    break;
	case 'group':
	    $show_group = TRUE;
	    break;
	case 'all':
	    $show_personal = $show_group = TRUE;
	    break;
	default:
	    throw new PAException(INVALID_ID, "context argument must be 'all', 'user' or 'group'");
	}
    }

    $albums_out = array();

    if ($show_personal) {
	$types_seen = array();

        // get personal albums
        $albums = Album::load_all($display_user_id);
	foreach ($albums as $album) {
	    $types_seen[(int)$album['album_type_id']] = TRUE;
	    $albums_out[] = array(
		'id' => "user:$display_user_id:album:".$album['collection_id'],
		'title' => $album['description'],
		'access' => ($display_user_id == $auth_user_id) ? "write" : "read",
		'created' => $album['created'],
		'type' => array(
		    API::$album_type_from_id[(int)$album['album_type_id']],
		    ),
		);
	}

	if ($display_user_id == $auth_user_id) {
	    // We don't reliably create personal albums on user creation, so add them in here if necessary...
	    foreach (array(IMAGE_ALBUM, AUDIO_ALBUM, VIDEO_ALBUM) as $alb_type) {
		if (!isset($types_seen[$alb_type])) {
		    // insert default album for this type
		    $albums_out[] = array(
			'id' => "user:$user->user_id:album:default:".API::$album_type_from_id[$alb_type],
			'title' => $_PA->default_album_titles[$alb_type],
			'access' => "write",
			'type' => array(API::$album_type_from_id[$alb_type]),
			);
		}
	    }
	}
    }

    if ($show_group) {
        // get group albums
        $groups = Group::get_user_groups($user->user_id, FALSE);
        foreach ($groups as $g) {
            $albums_out[] = array(
                'id' => "group:".$g['gid'],
                'title' => $g['name'],
		'access' => "write",
                'type' => array("image", "audio", "video"),
                );
        }
    }

    return array(
        'success' => TRUE,
        'albums' => $albums_out,
        );
}

function api_sanitize_filename($fn) {
    // munge characters that won't save on unix or go through urls.
    $bad_chars = array("/", "\\");
    $s = "";
    $len = strlen($fn);
    for ($i = 0; $i < $len; ++$i) {
        $c = $fn[$i];
        if (ord($c) < 32 || in_array($c, $bad_chars))
            $c = "_";
        $s .= $c;
    }
    return $s;
}

function api_validate_album_context($context, $user, $access="write") {
    if (preg_match("/^group:(\d+)$/", $context, $m)) {
        // group album
        $collection_id = $m[1];
        
        // load group and verify access
        $obj = new Group();
        $obj->load($collection_id);
        $obj->assert_user_access($user->user_id);
    } elseif (preg_match("/^user:\d+:album:(\d+)$/", $context, $m)) {
        // personal album
        $collection_id = $m[1];
        
        // load album and verify access
        $obj = new Album();
        $obj->load($collection_id);
	if ($access != "read") {
	    if ($obj->author_id != $user->user_id)
		throw new PAException(USER_ACCESS_DENIED, "You are not the creator of this album");
	}
    } else {
        throw new PAException(INVALID_ID, "File context must be an album or group ID");
    }

    return array($collection_id, $obj);
}

// get a url of a file in the web/files directory.
function api_get_url_of_file($fn) {
    if (preg_match("|^http://|", $fn)) {
	// already a URL
	return $fn;
    }

    global $uploaddir;
    $upload_path = realpath($uploaddir);
    $upload_url = api_get_url_of_path($upload_path);
    return $upload_url."/".rawurlencode(basename($fn));
}

function peopleaggregator_newFile($args) {
    global  $uploaddir;

    $user = User::from_auth_token($args['authToken']);
    $title = strip_tags($args['title']);
    $body = strip_tags($args['content']);
    $file_type = $args['type'];
    $tags = $args['tags'];
    $access = $args['access'];
    $context = $args['context'];
    // URL or file?
    if (!empty($args['url'])) {
	$upload_type = 'url';
	$url = $args['url'];
    } else {
	$upload_type = 'file';
	$filename = api_sanitize_filename($args['filename']); // strip attempts to ascend the directory tree
	$data = $args['data'];
    }

    $alb_type = API::$album_type_to_id[$file_type];
    switch ($alb_type) {
    case IMAGE_ALBUM:
        $new_img = new Image();
        $new_img->type = IMAGE;
	break;
    case AUDIO_ALBUM:
        $new_img = new Audio();
        $new_img->type = AUDIO;
	break;
    case VIDEO_ALBUM:
        $new_img = new Video();
        $new_img->type = VIDEO;
	break;
    default:
	throw new PAException(INVALID_ID, "file type must be 'image', 'audio' or 'video' (not $file_type)");
    }

    // When uploading a file, we can use the special 'default album' context: 'user:123:album:default'
    if (preg_match("/^user:\d+:album:default:([a-z]+)$/", $context, $m)) {
	$default_alb_type = API::$album_type_to_id[$m[1]];
	$collection = Album::get_or_create_default($user->user_id, $default_alb_type);
	$collection_id = $collection->collection_id;
    } else {
	list($collection_id, $collection) = api_validate_album_context($context, $user, "write");
    }

    if ($collection instanceof Album) {
	$album_file_type = API::$album_type_from_id[(int)$collection->album_type];
	if ($album_file_type != $file_type)
	    throw new PAException(OPERATION_NOT_PERMITTED, "Attempting to upload a file of type '$file_type' into an album that can only contain '$album_file_type' files");
    }

    try {
	switch ($upload_type) {
	case 'url':
	    // just supplying a URL; no upload to handle
	    $new_img->file_name = $url;
	    break;
	    
	case 'file':
	    // we're uploading a file - figure out where to put it
	    $upload_path = realpath($uploaddir);
	    
	    // make a filename that isn't already used
	    $fn_munge = ""; $munge_serial = 0;
	    while (1) {
		$fn = "$upload_path/".$user->user_id."_".$fn_munge.$filename;
		if (!file_exists($fn)) break; // we have our filename!
		$fn_munge = (++$munge_serial)."_";
	    }
	    
	    // and try to save the file, then put it in the database,
	    // removing the file if an exception occurs at any point.
	    
	    if (file_put_contents($fn, $data) != strlen($data)) {
		global $php_errormsg;
		throw new PAException(FILE_NOT_UPLOADED, "An error occurred when saving the file: $php_errormsg");
	    }
	    
	    $new_img->file_name = basename($fn);
	    break;
	}

	$access_map = array(
	    'nobody' => 0,
	    'everybody' => 1,
	    'relations' => 2,
	    );

	$new_img->file_perm = $access_map[$access];
	$new_img->author_id = $user->user_id;
	$new_img->title = $new_img->excerpt = $title;
	$new_img->body = $body;
	$new_img->allow_comments = 1;
	$new_img->parent_collection_id = $collection_id;
	$new_img->save();
        
        if ($tags) {
            Tag::add_tags_to_content($new_img->content_id, Tag::split_tags($tags));
        }

    } catch (PAException $e) {
	if ($upload_type == 'file') {
	    // delete file
	    @unlink($fn);
	}
        throw $e;
    }

    return array(
        'success' => TRUE,
        'id' => "file:$new_img->content_id",
        'url' => api_get_url_of_file($new_img->file_name),
        );
}

function peopleaggregator_getFiles($args) {
    $user = User::from_auth_token($args['authToken']);
    $context = $args['context'];
	
    $files_out = array();

    // When uploading a file, we can use the special 'default album' context: 'user:123:album:default:image'
    if (preg_match("/^user:\d+:album:default:[a-z]+$/", $context)) {
	// no files - this album doesn't exist yet
    } else {
	list($collection_id, $album) = api_validate_album_context($context, $user, "read");
	
	foreach (array(
		     array("audio", Audio::load_audios_for_collection_id ($collection_id, 0, "C.created")), //FIXME: order
		     array("image", Image::load_images_for_collection_id ($collection_id, 0, "C.created")),
		     array("video", Video::load_videos_for_collection_id ($collection_id, 0, "C.created")), //FIXME: order
		     ) as $bits) {
	    list($type, $files) = $bits;
	    if ($files) {
		foreach ($files as $c) {
		    $filename = $c[$type.'_file'];
		    $file_out = array(
			'id' => "file:".$c['content_id'],
			'created' => $c['created'],
			'title' => $c['title'],
			'content' => $c['body'],
			'type' => $type,
			'author' => "user:".$c['author_id'],
			);
		    if (preg_match("|^http://|", $filename)) {
			$file_out['url'] = $filename;
		    } else {
			$full_path = PA::$upload_path.'/'.$filename;
			if (file_exists($full_path)) {
			    $file_out['url'] = api_get_url_of_file($filename);
			    if ($type == 'image') {
				list($file_out['width'], $file_out['height']) = getimagesize($full_path);
			    }
			}
		    }
		    $files_out[] = $file_out;
		}
	    }
	}
    }

    return array(
        'success' => TRUE,
        'files' => $files_out,
        );
}

function peopleaggregator_deleteFile($args) {
    $user = User::from_auth_token($args['authToken']);
    $file_id = api_extract_id("file", $args['id']);

    // load file
    $f = Content::load_content($file_id, $user->user_id);

    // are we the author?
    if ($f->author_id != $user->user_id)
        throw new PAException(USER_ACCESS_DENIED, "You can only delete your own files");

    // delete it!
    $f->delete();

    return array(
        "success" => TRUE,
        );
}

function peopleaggregator_getPersonas($args) {
    $user = User::from_auth_token($args['authToken']);
    $personas &= Persona::load_personas($user->user_id);
    if ($personas) {
      throw new PAException(INVALID_ID, "Problem loading personas for user.");
    }
    $ret = array(
        "success" => TRUE,
	"personas" => $personas
    );
}

function peopleaggregator_countPersonas($args) {
}

function peopleaggregator_getPersona($args) {
}

function peopleaggregator_newPersona($args) {
}

function peopleaggregator_deletePersona($args) {
}

function peopleaggregator_listAds($args) {
    // global var $path_prefix has been removed - please, use PA::$path static variable
    require_once "api/Advertisement/Advertisement.php";

    // map 'page_type' arg to a page key like PAGE_HOMEPAGE
    $page_type = $args['page_type'];
    $page_key = NULL;
    foreach (Advertisement::get_pages() as $page) {
	if ($page['api_id'] == $page_type) {
	    $page_key = $page['value'];
	    break;
	}
    }
    if ($page_key === NULL) throw new PAException(INVALID_ID, "Invalid advertisement page type '$page_type'.");

    // build orientation map
    $orientation_map = array();
    foreach (Advertisement::get_orientations() as $ori) {
	$orientation_map[$ori['value']] = $ori['caption'];
    }

    $ads_out = array();
    foreach (Advertisement::get(NULL, array('page_id' => $page_key, 'is_active' => ACTIVE)) as $ad) {
	$ad_out = array(
	    "id" => "ad:".$ad->ad_id,
	    "title" => $ad->title,
	    "description" => $ad->description,
	    "orientation" => $ad->orientation,
	    );
	if (!empty($ad->ad_image)) {
	    list ($w, $h) = getimagesize(PA::$upload_path."/".$ad->ad_image);
	    $ad_out['image'] = array(
		"url" => api_get_url_of_file($ad->ad_image),
		"width" => $w,
		"height" => $h,
		);
	}
	if (!empty($ad->url)) $ad_out['url'] = $ad->url;
	if (!empty($ad->javascript)) $ad_out['javascript'] = $ad->javascript;
	$ads_out[] = $ad_out;
    }

    return array(
	"success" => TRUE,
	"msg" => "Retrieved ".count($ads_out)." ad(s)",
	"ads" => $ads_out,
	);
}


function peopleaggregator_getFriendAddresses($args) {
  // global var $path_prefix has been removed - please, use PA::$path static variable
  
  $success    = false;
  $msg        = "";
  $contacts   = array();
  
  $service_type = strtoupper($args['serviceName']);
  $login        = $args['login'];
  $password     = $args['password'];

  switch($service_type) {

     case 'PLAXO':
           $matches   = array();
           require_once "web/includes/classes/PlaxoClient.class.php";
           if(preg_match("/^\w+[\w-\.]*\@(\w*\.[a-z]{2,3})$/", $login, $matches)) {       //check is email passed as ID
              $id = $matches[0];
              $auth_type = 'Plaxo';
           } else {
              $id = $login;
              $auth_type = 'Aol';
           }
           if(!empty($id) && !empty($password)) {
              $plx = new PlaxoClient($auth_type);
              if($plx->plaxoLogIn($id, $password)) {
                 $contacts = $plx->plaxoGetContactsList();
                 if(count($contacts) > 0) {
                   $success = true;
                   $msg     = 'Retrieved '.count($contacts).' contacts.';
                 }
                 else {
                   $success = false;
                   $msg     = 'Personal contacts list empty.';
                 }  
              } else {
                 $success = false;
                 $msg     = 'Incorrect login ID or password.';
              }
           } else {
              $success = false;
              $msg     = 'Empty user name and/or password field.';
           }
           
     break;
     case 'WINDOWSLIVE':
            if(!empty($login) && !empty($password)) {
               define('MSN_LIVE_SOAP_FILE', "web/includes/xml/MSNLiveAuth.xml");
               require_once "web/includes/classes/MSNLiveClient.class.php";
               $msn = new MSNLiveClient();
               if( $msn->authenticate($login, $password, MSN_LIVE_SOAP_FILE)) {
                   $result = $msn->getAddressBook();
                   $contacts = $msn->getContactsList();
                  if($result && (count($contacts) > 0)) {
                     $success = true;
                     $msg     = 'Retrieved '.count($contacts).' contacts.';
                  }   
                  else {
                     $success = false;
                     $msg     = 'Personal contacts list empty.';
                  } 
               } else {
                 $success = false;
                 $msg     = 'Incorrect login ID or password.';
               }
            } else {
              $success = false;
              $msg     = 'Empty user name and/or password field.';
            }

     break;
     default:
              $success = false;
              $msg     = 'Unknown service name.';
  }
  return array( "success"  => $success,
                "msg"      => $msg,
                "contacts" => $contacts,
         );

 }
?>