<?php
/** $pa_error_name_mapping is used to map exceptions to API responses
 * - for the external (web services) API.
 */
$pa_error_name_mapping = array();
/** define an error code using define() and also stash a copy of it in
 * $pa_error_name_mapping so we can look it up if an exception occurs
 * during an API call.
 */
function define_err($name, $value) {
  global $pa_error_name_mapping;
  $pa_error_name_mapping[$value] = strtolower($name);
  define($name, $value);
}
/** translate an error code (e.g. GENERAL_SOME_ERROR) into an error
 * string suitable for returning in an API call response
 * (e.g. "general_some_error") */
function pa_get_error_name($code) {
  global $pa_error_name_mapping;
  $code_string = @$pa_error_name_mapping[$code];
  if ($code_string === NULL)
    return "<Invalid error code: $code>";
  return $code_string;
}

/**
 * General Exception Codes
 * Range: 1 to 99
 * Prefix: GENERAL_
 */
define_err("GENERAL_SOME_ERROR", 1);
define_err("ROLE_ID_NOT_EXIST", 2);
define_err("AUTHOR_NAME_NOT_PRESENT", 3);
define_err("AUTHOR_EMAIL_NOT_PRESENT", 4);
define_err("AUTHOR_HOMEPAGE_NOT_PRESENT", 5);
define_err("ROW_DOES_NOT_EXIST", 6);
define_err("BLOG_NOT_EXIST", 7);
define_err("INVALID_INVITATION_ID", 8);
define_err("MAIL_FUNCTION_FAILED",9);
define_err("OPERATION_NOT_PERMITTED", 10);
define_err("RELATION_ALREADY_EXISTS", 11);
define_err("COOKIE_FAILED", 12);
define_err("TAG_NOT_EXIST", 13);
define_err("FILE_NOT_UPLOADED", 14);
define_err("INVALID_INVITATION_EMAIL", 15);
define_err("INVALID_MESSAGE", 16);
define_err("INVALID_FORGOT_PASSWORD_ID", 17);
define_err("RELATION_NOT_EXIST", 18);
define_err("INVALID_RELATION", 19);
define_err("REMOTE_ERROR", 20); // error returned from remote service - e.g. flickr
define_err("VALIDATION_INCORRECT_TYPE", 21); // api call validation error
define_err("USER_ACCESS_DENIED", 22); // attempt to access or post to a collection you don't have access to
define_err("MISSING_DEPENDENCY", 23); // missing something like GD or imagemagick
define_err("FILE_NOT_FOUND", 24);
define_err("INVALID_ID", 25); // invalid id passed to an api function (i.e. "asdf:123" instead of "group:123")
define_err("BAD_PARAMETER", 26);
define_err("ITEM_ALREADY_EXISTS", 28); // used by badge - if you try to rename a badge to have the same name as an existing badge
define_err("IMPORT_URL_ALREADY_EXISTS", 29);
define_err("IMPORT_FAILED", 30);
define_err("STORAGE_ERROR", 31); // error from storage system
define_err("NON_MEMBER_ACCESS_TO_NETWORK", 32);
define_err("INVALID_FILE", 33); // attempted to upload file of the wrong type, or failed to sniff file type
/**
 * Database Exception Codes
 * Range: 100 to 199
 * Prefix: DB_
 */
define_err("DB_QUERY_FAILED", 100);
define_err("DB_CONNECTION_FAILED", 101);
define_err("RECORD_DOES_NOT_EXIST", 102);
define_err("DUPLICATED_RECORD_FOUND", 103);

/**
 * Content Related Exception Codes
 * Range: 200 to 299
 * Prefix: CONTENT_
 */
 define_err("CONTENT_NOT_FOUND", 200);
 define_err("CONTENT_COLLECTION_NOT_FOUND", 201);
 define_err("COMMENT_NOT_EXIST", 202);
 define_err("CONTENT_HAS_BEEN_DELETED",203);
 define_err("CONTENT_COLLECTION_TITLE_ALREADY_EXIST", 205);
 define_err("CONTENT_INVALID", 206);
 define_err("CONTENT_NOT_AUTHORISED_TO_ACCESS", 207);

 /**
 * User Related Exception Codes
 * Range: 300 to 399
 * Prefix: USER_
 */
 define_err("USER_NOT_FOUND", 300);
 define_err("USER_INVALID_LOGIN_NAME", 301);
 define_err("PASSWORD_NOT_EXIST", 302);
 define_err("SAVING_DELETED_USER", 303);
 define_err("USER_LOGINNAME_TAKEN", 304);
 define_err("USER_ALREADY_DELETED", 305);
 define_err("USER_INVALID_PASSWORD", 306);
 define_err("USER_INVALID_NAME", 307);
 define_err("USER_INVALID_EMAIL", 308);
 define_err("USER_INVALID", 309);
 define_err("USER_TOKEN_EXPIRED", 310);
 define_err("USER_TOKEN_INVALID", 311);
 define_err("SAVING_UNVERIFIED_USER", 312);
 define_err("USER_EMAIL_NOT_UNIQUE", 313);


/**
 * Type Related Exception Codes
 * Range: 400 to 499
 * Prefix: TYPE_
 */
 define_err("TYPE_NOT_AN_ARRAY", 400);





 /** General required variable missing Exception Codes
 * Range: 500 to 599
 * Prefix: REQUIRED_
 */

 define_err("REQUIRED_PARAMETERS_MISSING", 500);

/**
 * Event Related Exception Codes
 * Range: 600 to 699
 * Prefix: EVENT_
 */
 define_err("EVENT_ORGANIZER_NOT_EXIST",600);
 define_err("EVENT_VENUE_NOT_EXIST",601);
 define_err("EVENT_START_TIME_NOT_EXIST",602);
 define_err("EVENT_END_TIME_NOT_EXIST",603);
 define_err("EVENT_START_DATE_NOT_EXIST",604);
 define_err("EVENT_END_DATE_NOT_EXIST",605);
 define_err("EVENT_DESCRIPTION_NOT_EXIST",606);

 // added by Martin for the Event class
 // starting from 620 to not conflict with pervious definitions
 define_err("EVENT_NOT_EXIST",620);

/**
 * Review Related Exception Codes
 * Range: 700 to 799
 * Prefix: REVIEW_
 */
 define_err("REVIEW_NAME_NOT_EXIST",700);
 define_err("REVIEW_TYPE_NOT_EXIST",701);
 define_err("REVIEW_RATING_NOT_EXIST",702);
 define_err("REVIEW_DESCRIPTION_NOT_EXIST",704);
 define_err("REVIEW_SUMMARY_NOT_EXIST",705);

/**
 * Group Related Exception Codes
 * Range: 800 to 899
 * Prefix: GROUP_
 */
 define_err("GROUP_NAME_NOT_EXIST",800);
 define_err("GROUP_ACCESS_TYPE_NOT_EXIST",801);
 define_err("GROUP_REGISTRATION_TYPE_NOT_EXIST",802);
 define_err("GROUP_IS_MODERATED_NOT_EXIST",803);
 define_err("GROUP_DESCRIPTION_NOT_EXIST",804);
 define_err("GROUP_PARAMETER_ERROR",805);
 define_err("GROUP_CREATION_FAILED",806);
 define_err("GROUP_NOT_INVITED", 807);
 define_err("INVALID_INV", 808);

 // attempt to join an invite-only group without an invitation


 /**
 * MESSAGEBOARD  && CATEGORY Related Exception Codes
 * Range: 900 to 1000
 * Prefix: GROUP_
 */
 define_err("CATEGORY_DOES_NOT_EXIST",900);

  /**
 * NETWORK Related Exception Codes
 * Range: 1000 to 1100
 * Prefix: NETWORK_
 */
 define_err("NETWORK_ALREADY_EXISTS",1000);
 define_err("NETWORK_DIRECTORY_PERMISSION_ERROR",1001);
 define_err("NETWORK_MYSQL_FILE",1002);
 define_err("NETWORK_NOT_FOUND",1003);
 define_err("NETWORK_INVALID_STEP",1004);
 define_err("NETWORK_INTERNAL_ERROR",1005);

 //configurations errors
 define_err("MOTHER_NETWORK_NOT_DEFINED",1006);

 define_err("INVALID_ARGUMENTS",1007);

 /**
 * ITEM error codes.
 **/
 define_err("ITEM_NOT_FOUND",1101);
 define_err("ITEM_COLLECTION_NOT_LOADED",1102);
 define_err("ITEM_CONTENT_NOT_LOADED",1103);
?>
