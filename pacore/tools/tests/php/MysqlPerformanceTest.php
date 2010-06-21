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

// Explain all SQL queries in the PA code.
// Author: marek
require_once dirname(__FILE__)."/lib/common.php";
// {{{ MysqlPerformanceTest extends PHPUnit_Framework_Test
class MysqlPerformanceTest implements PHPUnit_Framework_Test {

    private $queries;
    // {{{ __construct($queriesData)
    public function __construct($queriesData) {
        $this->queries = $queriesData;
    }
    // }}}
    // {{{ count()
    public function count() {
        return sizeof($this->queries);
    }
    // }}}
    // {{{ run(PHPUnit_Framework_TestResult $result = NULL)
    public function run(PHPUnit_Framework_TestResult$result = NULL) {
        if($result === NULL) {
            $result = new PHPUnit_Framework_TestResult;
            $result->startTest($this);
            $counter = 0;
            foreach($this->queries as $query_data) {
                $query            = 'EXPLAIN '.$query_data['query'];
                $parameters       = $query_data['parameters'];
                $parameters_print = '';
                try {
                    if(!empty($parameters)) {
                        $res = Dal::query($query, $parameters);
                        $parameters_print = 'PARAMETERS:'."\n";
                        foreach($parameters as $param) {
                            $parameters_print .= '- '.$param."\n";
                        }
                    }
                    else {
                        $res = Dal::query($query);
                    }
                }
                catch(PAException$e) {
                    try {
                        PHPUnit_Framework_Assert::assertEquals($e->getCode(), DB_QUERY_FAILED);
                    }
                    catch(PHPUnit_Framework_AssertionFailedError$e) {
                        $result->addFailure($this, $e);
                    }
                    catch(Exception$e) {
                        $result->addError($this, $e);
                    }
                }
                $tables = array();
                print "{{{ ==================================================================\n";
                $query_row = wordwrap($explain."QUERY: \"$query\"", 70);
                print $query_row."\n";
                if(!empty($parameters_print)) {
                    print "----------------------------------------------------------------------\n";
                    print $parameters_print;
                }
                while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                    print "----------------------------------------------------------------------\n";
                    print 'ID: '.$row->id."\n";
                    print 'SELECT TYPE: '.$row->select_type."\n";
                    print 'TABLE: '.$row->table."\n";
                    if(!empty($row->table)) {
                        $tables[] = $row->table;
                    }
                    print 'TYPE: '.$row->type."\n";
                    print 'POSSIBLE KEYS: '.$row->possible_keys."\n";
                    print 'KEY: '.$row->key."\n";
                    print 'KEY LENGTH: '.$row->key_len."\n";
                    print 'REFERENCE: '.$row->ref."\n";
                    print 'ROWS: '.$row->rows."\n";
                    print 'EXTRA: '.$row->Extra."\n";
                    $counter++;
                }
                // Now show all the tables used in the query.
                foreach($tables as $table) {
                    print "----------------------------------------------------------------------\n";
                    try {
                        $create_table = Dal::query_one("SHOW CREATE TABLE $table");
                    }
                    catch(PAException$e) {
                        if($e->getCode() != DB_QUERY_FAILED) {
                            throw $e;
                        }
                        $bits = preg_split("/(\s+|,)/", $query);
                        $pos = array_search($table, $bits);
                        if($pos === NULL) {
                            throw new PAException(GENERAL_SOME_ERROR, "Failed to find real name for table $table in query $sql");
                        }
                        $table = (strtolower($bits[$pos-1]) == 'as') ? $bits[$pos-2] : $bits[$pos-1];
                        $create_table = Dal::query_one("SHOW CREATE TABLE $table");
                    }
                    echo $create_table[1]."\n";
                }
                print "================================================================== }}}\n";
            }
            $result->endTest($this);
            return $result;
        }
    }
    // }}}
}
// }}}
// {{{ Application data (SELECT statements with some test data)
$queries = array();
// {{{ Testing data for queries
$spam_domains_domain = 'forumage.com';
$domain_id           = 20;
$persona_id          = 1;
$persona_service_id  = 1;
// MySpace
$user_id = 1;
// arvind
$relation_id  = 3;
$term         = 'ibelgique.com';
$rel_type_id  = 4;
$rel_type     = 'best relation';
$content_type = 5;
// voice
$collection_id = 5;
$content_id    = 5;
$page          = 1;
$pagesize      = 2;
$collection_id_array = array(
    1,
    2,
    3,
    4,
    5,
);
$inv_id              = '857e4f55523a79de3aca9bfd7602fd82';
$network_id          = 1;
$users_order_by      = 'login_name';
$limit               = 'LIMIT 1';
$inv_user_email      = 'nirmalpreet@gmail.com';
$content_type_name   = 'Audio';
$with_type           = '';
$network_name        = 'openlaszlo';
$enc_title           = 'Audio';
$boardmessage_id     = 1;
$parent_type         = 'collection';
$parent_id           = 13;
$tag_id              = 3;
$album_type_id       = 2;
$is_active           = 1;
$author_id           = 2;
$title               = 'My Audio Album';
$date_from           = '2007-01-01';
$date_to             = '2007-03-01';
$comment_id          = 5;
$fid                 = 1;
$modulename          = 'LogoModule';
$first_day_of_month  = '2007-02-01';
$last_day_of_month   = '2007-02-28';
$page_id             = 1;
$massage_folder_name = 'Inbox';
$category_id         = 1;
$category_name       = 'Search Engines';
$tag_name            = 'peepagg';
$email               = 'gauravbhatnagar@gmail.com';
// }}}
// {{{ DONE
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM networks WHERE is_active=1 AND type=".REGULAR_NETWORK_TYPE,
);
$queries[] = array(
    'query' => "SELECT * FROM spam_domains WHERE domain=?",
    'parameters' => array(
        $spam_domains_domain,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(c.comment_id) FROM comments c, domains_in_comments dic WHERE c.comment_id=dic.comment_id AND c.is_active=1 AND dic.domain_id=?",
    'parameters' => array(
        $domain_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {persona_properties} WHERE persona_id = ?",
    'parameters' => array(
        $persona_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {persona_service_paths} WHERE persona_service_id = ?",
    'parameters' => array(
        $persona_service_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {persona_services}",
    'parameters' => array(),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {personas} WHERE user_id = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {users} WHERE is_active = 1",
);
$queries[] = array(
    'query' => "SELECT COUNT(comment_id) FROM domains_in_comments WHERE domain_id=?",
    'parameters' => array(
        $domain_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {comments} WHERE is_active = 1",
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {contents}",
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM spam_domains WHERE blacklisted=1",
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM spam_domains",
);
$queries[] = array(
    'query' => "SELECT * FROM {relations} WHERE user_id=? AND relation_id=?",
    'parameters' => array(
        $user_id,
        $relation_id,
    ),
);
$queries[] = array(
    'query' => "SELECT id FROM spam_terms WHERE term=?",
    'parameters' => array(
        $term,
    ),
);
$queries[] = array(
    'query' => "SELECT relation_type FROM {relation_classifications} WHERE relation_type_id=?",
    'parameters' => array(
        $rel_type_id,
    ),
);
$queries[] = array(
    'query' => "SELECT relation_type_id FROM {relation_classifications} WHERE relation_type=?",
    'parameters' => array(
        $rel_type,
    ),
);
$queries[] = array(
    'query' => "SELECT C.content_id AS content_id, C.title AS title, C.body AS body, C.author_id AS author_id, C.created AS created, C.changed AS changed FROM {contents} AS C WHERE C.type = ? AND C.is_active = ? AND C.collection_id = ? ORDER BY C.created DESC",
    'parameters' => array(
        $content_type,
        1,
        $collection_id,
    ),
);
$queries[] = array(
    'query' => "SELECT C.content_id AS content_id, C.title AS title, C.body AS body, C.type AS type, C.author_id AS author_id, C.created AS created, C.changed AS changed FROM {contents} AS C WHERE C.is_active = ? AND C.collection_id = ? ORDER BY C.created DESC",
    'parameters' => array(
        1,
        $collection_id,
    ),
);
$queries[] = array(
    'query' => "SELECT collection_id FROM {contents} WHERE content_id = ?",
    'parameters' => array(
        $content_id,
    ),
);
$queries[] = array(
    'query' => "SELECT content_id, title, body, changed, is_active FROM {contents} WHERE collection_id < 0 AND author_id = ? AND is_active = ? ORDER BY created DESC",
    'parameters' => array(
        $user_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT content_id, title, body, changed, is_active FROM {contents} WHERE collection_id < 0 AND author_id = ? ORDER BY created DESC LIMIT $page, $pagesize",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT count(*) AS cnt FROM {networks}  WHERE is_active=?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT count(*) as cnt from {users} where is_active=1",
);
$queries[] = array(
    'query' => "SELECT count(*) as invitations, inv_id, inv_user_email FROM {invitations} WHERE inv_collection_id = ? AND user_id = ? AND inv_status = ? GROUP BY inv_user_email",
    'parameters' => array(
        $collection_id,
        $user_id,
        INVITATION_PENDING,
    ),
);
$queries[] = array(
    'query' => "SELECT count(*) as invitations, inv_id, inv_user_email FROM {invitations} WHERE  user_id = ? AND inv_status = ? AND  inv_collection_id IN (".implode($collection_id_array,
    ',').") GROUP BY inv_user_email",
    'parameters' => array(
        $user_id,
        INVITATION_PENDING,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {contentcollections} WHERE collection_id = ? AND is_active = ?",
    'parameters' => array(
        $collection_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {invitations} WHERE inv_id = ?",
    'parameters' => array(
        $inv_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {networks_users} WHERE network_id = ? AND user_id=? AND user_type <> ?",
    'parameters' => array(
        $network_id,
        $user_id,
        NETWORK_WAITING_MEMBER,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {networks_users} WHERE network_id = ? AND user_id = ? AND user_type = ?",
    'parameters' => array(
        $network_id,
        $user_id,
        $user_type,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {users} LIMIT $page, $pagesize",
);
$queries[] = array(
    'query' => "SELECT * FROM {users} where is_active = '1'  ORDER BY $users_order_by $limit",
);
$queries[] = array(
    'query' => "SELECT I.inv_id, I.inv_user_email, I.inv_collection_id, CC.title,U.user_id, U.first_name, U.last_name FROM {invitations} as I LEFT JOIN {contentcollections} as CC ON I.inv_collection_id = CC.collection_id INNER JOIN {users} as U on CC.author_id = U.user_id WHERE I.inv_collection_id <> -1 AND I.inv_user_email = ? AND I.inv_status = ?",
    'parameters' => array(
        $inv_user_email,
        INVITATION_PENDING,
    ),
);
$queries[] = array(
    'query' => "SELECT I.inv_id, I.inv_user_email, I.inv_user_id FROM {invitations} AS I LEFT JOIN {users} AS U ON I.inv_user_id = U.user_id WHERE  I.user_id = ? AND I.inv_status = ? AND U.is_active = ? AND  I.inv_collection_id IN (".implode($collection_id_array,
    ',').") GROUP BY inv_user_email",
    'parameters' => array(
        $inv_user_email,
        INVITATION_ACCEPTED,
        ACTIVE,
    ),
);
$queries[] = array(
    'query' => "SELECT inv_id, inv_status FROM {invitations} WHERE inv_collection_id = ?",
    'parameters' => array(
        $collection_id,
    ),
);
$queries[] = array(
    'query' => "SELECT inv_id, inv_status FROM {invitations} WHERE user_id = ? AND inv_collection_id = ?",
    'parameters' => array(
        $user_id,-1,
    ),
);
$queries[] = array(
    'query' => "SELECT relation_id, relationship_type FROM {relations} WHERE user_id = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT type FROM {networks} WHERE  network_id = ?",
    'parameters' => array(
        $network_id,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id FROM {invitations} WHERE inv_id = ?",
    'parameters' => array(
        $inv_id,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id, inv_collection_id, inv_user_id FROM {invitations} WHERE inv_id=?",
    'parameters' => array(
        $inv_id,
    ),
);
$queries[] = array(
    'query' => "SELECT  user_type FROM {networks_users} WHERE network_id = ? AND user_id=?",
    'parameters' => array(
        $network_id,
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT COUNT(*) FROM {contents} WHERE collection_id=-1 AND is_active=1 AND display_on=? $with_type",
    'parameters' => array(
        DISPLAY_ON_HOMEPAGE,
    ),
);
$queries[] = array(
    'query' => "SELECT member_count FROM {networks} WHERE network_id = ?",
    'parameters' => array(
        $network_id,
    ),
);
$queries[] = array(
    'query' => "SELECT created FROM users WHERE user_id=?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT created FROM users WHERE user_id=?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT address FROM networks WHERE is_active=1",
);
$queries[] = array(
    'query' => "SELECT collection_id,title FROM {contentcollections} WHERE title LIKE '%$enc_title%' AND is_active=1",
);
$queries[] = array(
    'query' => "SELECT * FROM {networks} WHERE is_active=1 AND address=?",
    'parameters' => array(
        $network_name,
    ),
);
$queries[] = array(
    'query' => "SELECT id,domain,active_count,count,blacklisted FROM spam_domains ORDER BY active_count DESC $limit",
);
$queries[] = array(
    'query' => "SELECT id FROM spam_domains",
);
$queries[] = array(
    'query' => "SELECT relation_type_id, relation_type FROM {relation_classifications}",
);
$queries[] = array(
    'query' => "SELECT term FROM spam_terms",
);
$queries[] = array(
    'query' => "SELECT address from {networks} where network_id=?",
    'parameters' => array(
        $network_id,
    ),
);
$queries[] = array(
    'query' => "SELECT allow_anonymous FROM {boardmessages} WHERE boardmessage_id = ?",
    'parameters' => array(
        $boardmessage_id,
    ),
);
$queries[] = array(
    'query' => "SELECT  boardmessage_id FROM {boardmessages} WHERE parent_id = ? AND parent_type=? ",
    'parameters' => array(
        $parent_id,
        $parent_type,
    ),
);
$queries[] = array(
    'query' => "SELECT  boardmessage_id FROM {boardmessages} WHERE user_id = $user_id ",
);
$queries[] = array(
    'query' => "SELECT CC.collection_id as collection_id, CC.title as name, CC.is_active as is_active FROM {tags_contentcollections} as TCC, {contentcollections} as CC WHERE tag_id = ? AND TCC.collection_id = CC.collection_id",
    'parameters' => array(
        $tag_id,
    ),
);
$queries[] = array(
    'query' => "SELECT C.content_id AS content_id, C.author_id AS author_id, C.type AS type, C.body AS body, C.created AS created, C.changed AS changed, C.collection_id AS ccid FROM {contents} AS C WHERE C.collection_id = ? ORDER BY C.created DESC $limit",
    'parameters' => array(
        $collection_id,
    ),
);;
$queries[] = array(
    'query' => "SELECT C.content_id AS content_id, C.author_id AS author_id, C.type AS type, C.title AS title, C.body AS body, C.allow_comments AS allow_comments, C.created AS created, C.changed AS changed, C.trackbacks as trackbacks, C.is_active AS is_active, C.is_html AS is_html FROM {contents} AS C WHERE C.content_id = ? AND C.is_active <> ?",
    'parameters' => array(
        $content_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT C.content_id as content_id,C.title as title,C.is_active as is_active FROM {tags_contents} as TC, {contents} as C WHERE tag_id = ? AND TC.content_id = C.content_id AND C.is_active = 1",
    'parameters' => array(
        $tag_id,
    ),
);
$queries[] = array(
    'query' => "SELECT CCT.name AS collection_type_name FROM {contentcollections} AS CC, {contentcollection_types} AS CCT WHERE CC.collection_id = ? AND CC.type = CCT.type_id",
    'parameters' => array(
        $collection_id,
    ),
);
$queries[] = array(
    'query' => "SELECT CCT.name AS name ,CCT.type_id AS type FROM {contentcollections} AS CC, {contentcollection_types} AS CCT WHERE CC.collection_id = ? AND CC.type = CCT.type_id",
    'parameters' => array(
        $collection_id,
    ),
);
$queries[] = array(
    'query' => "SELECT collection_id, album_type_id FROM {contentcollections} AS CC, {contentcollections_albumtype} AS CCA WHERE CC.author_id = ? AND CC.title = ? AND CC.collection_id = CCA.contentcollection_id AND CCA.album_type_id = $album_type_id AND is_active = ?",
    'parameters' => array(
        $author_id,
        $title,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT collection_id FROM {contentcollections} WHERE author_id = ? AND title = ? AND is_active = ?",
    'parameters' => array(
        $author_id,
        $title,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT collection_id FROM {contentcollections} WHERE collection_id NOT IN ('$collection_id')  AND author_id = ? AND title = ? AND is_active = ?",
    'parameters' => array(
        $author_id,
        $title,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT COM.comment_id, COM.content_id, COM.user_id, COM.subject, COM.comment, COM.created, COM.changed, COM.name, COM.email, COM.homepage FROM {comments} AS COM, {contents} AS CON WHERE 1 AND COM.is_active = ? AND COM.content_id=CON.content_id AND CON.is_active = ?",
    'parameters' => array(
        $is_active,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT content_id, title, body, author_id, changed FROM {contents} WHERE collection_id < 0 AND is_active = 1 AND (title like '%".$enc_title."%' OR body like '%".$enc_title."%')",
);
$queries[] = array(
    'query' => "SELECT content_id, title, body, author_id, changed FROM {contents} WHERE collection_id < 0 AND is_active = 1  AND created >= ".$date_from." AND created <= ".$date_to,
);
$queries[] = array(
    'query' => "SELECT content_id, title, body, type, author_id, changed, created, is_active FROM {contents} order by created DESC $limit",
);
$queries[] = array(
    'query' => "SELECT content_id, user_id FROM {comments} WHERE comment_id = ?",
    'parameters' => array(
        $comment_id,
    ),
);
$queries[] = array(
    'query' => "SELECT count(*) AS cnt FROM {boardmessages} WHERE  parent_id = ? AND parent_type=?",
    'parameters' => array(
        $parent_id,
        $parent_type,
    ),
);
$queries[] = array(
    'query' => "SELECT count(*) AS CNT FROM {boardmessages} WHERE parent_id = ? AND parent_type = ?",
    'parameters' => array(
        $parent_id,
        $parent_type,
    ),
);
$queries[] = array(
    'query' => 'SELECT count(*) as cnt  FROM {contents} WHERE is_active = '.$is_active,
);
$queries[] = array(
    'query' => " SELECT count(*) as cnt FROM {tags_contentcollections} WHERE tag_id='$tag_id'",
);
$queries[] = array(
    'query' => " SELECT count(*) as cnt FROM {tags_contents} WHERE tag_id='$tag_id'",
);
$queries[] = array(
    'query' => "SELECT count(*)  as cnt FROM {tags_users} WHERE tag_id='$tag_id'",
);
$queries[] = array(
    'query' => "SELECT count(*) as records from {contents} where collection_id < 0 and author_id = ? ",
    'parameters' => array(
        $author_id,
    ),
);
$queries[] = array(
    'query' => "SELECT count(mid) AS total from {user_message_folder} WHERE fid = ?",
    'parameters' => array(
        $fid,
    ),
);
$queries[] = array(
    'query' => "SELECT count(TC.tag_id) as cnt, TC.tag_id,T.tag_name FROM {tags_contents} AS TC, {tags} AS T, {contents} AS C WHERE T.tag_id=TC.tag_id AND TC.content_id = C.content_id AND C.is_active = ? GROUP BY TC.tag_id ORDER BY cnt DESC",
    'parameters' => array(
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE  C.is_active = ?  AND C.created BETWEEN $first_day_of_month AND $last_day_of_month AND CT.type_id = C.type ORDER BY C.created DESC $limit ",
    'parameters' => array(
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE  C.is_active = ?  AND  CT.type_id = C.type ORDER BY C.created DESC $limit ",
    'parameters' => array(
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT CT.name AS content_name, C.content_id, C.collection_id, C.title, C.body, C.type, C.author_id, C.changed, C.created FROM {contents} AS C, {content_types} AS CT WHERE C.title LIKE ? AND C.is_active = ?  AND CT.type_id = C.type  AND C.created BETWEEN '$first_day_of_month' AND '$last_day_of_month' ORDER BY C.created DESC $limit ",
    'parameters' => array(
        $content_type_name,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT  data AS data FROM {moduledata}  WHERE modulename LIKE ? ",
    'parameters' => array(
        $modulename,
    ),
);
$queries[] = array(
    'query' => "SELECT default_settings FROM {page_default_settings} WHERE page_id = ?",
    'parameters' => array(
        $page_id,
    ),
);
$queries[] = array(
    'query' => "SELECT DISTINCT C.content_id as content_id,C.title as title, C.body as body, C.author_id as author_id, C.changed as changed FROM {tags_contents} as TC, {contents} as C WHERE tag_id = ? AND TC.content_id = C.content_id AND C.is_active = 1",
    'parameters' => array(
        $tag_id,
    ),
);
$queries[] = array(
    'query' => " SELECT distinct(TC.content_id) FROM {tags_contents} AS TC LEFT JOIN {tags} AS T ON TC.tag_id = T.tag_id LEFT JOIN {contents} AS C ON TC.content_id = C.content_id WHERE T.tag_id = ? AND C.is_active = ? ORDER BY C.created DESC $limit",
    'parameters' => array(
        $tag_id,
        $is_active,
    ),
);
$queries[] = array(
    'query' => "SELECT fid from {message_folder} WHERE uid = ? AND name = ?",
    'parameters' => array(
        $user_id,
        $massage_folder_name,
    ),
);
$queries[] = array(
    'query' => 'SELECT fid FROM {user_message_folder} WHERE index_id = 1',
);
$queries[] = array(
    'query' => 'SELECT * FROM {boardmessages}',
);
$queries[] = array(
    'query' => " SELECT * FROM {boardmessages} WHERE  boardmessage_id = ?  ",
    'parameters' => array(
        $boardmessage_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * from {categories} WHERE category_id = ?",
    'parameters' => array(
        $category_id,
    ),
);
$queries[] = array(
    'query' => " SELECT * FROM {categories} WHERE position RLIKE '^[0-9]+>$' AND is_active =1 ORDER BY name ",
);
$queries[] = array(
    'query' => "SELECT * FROM {categories} WHERE position RLIKE  '^".$position."[0-9]+>$'",
);
$queries[] = array(
    'query' => "SELECT * FROM {comments} WHERE comment_id = ?",
    'parameters' => array(
        $comment_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {comments} WHERE user_id = ? AND is_active = ? ORDER BY created DESC $limit",
    'parameters' => array(
        $user_id,
        $is_active,
    ),
);
$queries[] = array(
    'query' => " SELECT * FROM {contents} WHERE 1 AND is_active = 1 ",
);
$queries[] = array(
    'query' => "SELECT * FROM {contents} WHERE content_id = ?",
    'parameters' => array(
        $content_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {contents} WHERE is_active=1 AND author_id=? AND collection_id=-1 ORDER BY created DESC LIMIT ?",
    'parameters' => array(
        $author_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {contents} WHERE is_active=1 AND display_on=? AND collection_id=-1 ORDER BY created DESC LIMIT ?",
    'parameters' => array(
        0,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {feed_data}",
);
$queries[] = array(
    'query' => "SELECT * FROM {feed_data} WHERE feed_id = ? ORDER BY feed_id DESC",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {forgot_password} WHERE forgot_password_id = ?",
    'parameters' => array(
        'fa5bd232c256d5504f96bd03c26a66be',
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {invitations} WHERE inv_id = ? AND inv_status = ?",
    'parameters' => array(
        $inv_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {linkcategories} WHERE category_name = ? AND user_id = ? AND category_id <> ?",
    'parameters' => array(
        $category_name,
        $user_id,
        $category_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * from {message_folder} WHERE fid = ?",
    'parameters' => array(
        $fid,
    ),
);
$queries[] = array(
    'query' => "SELECT * from {message_folder} WHERE uid = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * from {message_folder} WHERE uid = ? AND name = ?",
    'parameters' => array(
        $user_id,
        $massage_folder_name,
    ),
);
$queries[] = array(
    'query' => "SELECT * from {networks_users} WHERE network_id = ? AND user_id = ? AND user_type = ?",
    'parameters' => array(
        $network_id,
        $user_id,
        $user_type,
    ),
);
$queries[] = array(
    'query' => " SELECT * FROM {networks} WHERE 1 AND is_active = 1 AND type = ".REGULAR_NETWORK_TYPE,
);
$queries[] = array(
    'query' => " SELECT * FROM {networks} WHERE network_id = ? AND is_active = 1 ",
    'parameters' => array(
        $network_id,
    ),
);
$queries[] = array(
    'query' => " SELECT * FROM {networks} WHERE type = ".MOTHER_NETWORK_TYPE,
);
$queries[] = array(
    'query' => "SELECT * FROM {page_default_settings} WHERE page_id = 1",
);
$queries[] = array(
    'query' => "SELECT * FROM {page_settings} WHERE user_id=? AND page_id=?",
    'parameters' => array(
        $user_id,
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_properties} WHERE persona_id = ?",
    'parameters' => array(
        $persona_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_properties} WHERE persona_id = ? AND name = ?",
    'parameters' => array(
        $persona_id,
        'vshivak',
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_properties} WHERE persona_property_id = ?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_service_paths} WHERE persona_service_id = ?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_service_paths} WHERE persona_service_path_id = ?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_services} ORDER BY sequence",
);
$queries[] = array(
    'query' => "SELECT * FROM {persona_services} WHERE persona_service_id = ?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {personas} WHERE persona_id = ?",
    'parameters' => array(
        $persona_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {personas} WHERE user_id = ? ORDER BY sequence",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {tags} WHERE tag_name = ?",
    'parameters' => array(
        $tag_name,
    ),
);
$queries[] = array(
    'query' => "SELECT * FROM {user_feed} WHERE user_id = ? AND feed_id = ?",
    'parameters' => array(
        $user_id,
        1,
    ),
);
$queries[] = array(
    'query' => 'SELECT user_id FROM {users} WHERE is_active = 1',
);
$queries[] = array(
    "query" => "SELECT * FROM {relations} WHERE in_family = ? AND relation_id = ? AND user_id = ?",
    'parameters' => array(
        null,
        $relation_id,
        $user_id,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {routing_destination_types}",
);
$queries[] = array(
    "query" => "SELECT * FROM {trackback_contents} WHERE content_id = ?",
    "parameters" => array(
        $content_id,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {trackback_contents} WHERE content_id = ? AND trackback = ?",
    "parameters" => array(
        1615,
        1,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {user_profile_data}",
);
$queries[] = array(
    "query" => "SELECT * FROM {user_profile_data} WHERE user_id = ? ",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {user_profile_data} WHERE user_id = ? AND field_type = ?",
    'parameters' => array(
        $user_id,
        4,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {users_online} WHERE user_id = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {users} WHERE email = ?",
    'parameters' => array(
        $email,
    ),
);
$queries[] = array(
    "query" => "SELECT * FROM {users} WHERE email = ? AND is_active <> ?",
    'parameters' => array(
        $email,
        $is_active,
    ),
);
$queries[] = array(
    "query" => "SELECT * from {users} WHERE user_id=? AND is_active = ?",
    'parameters' => array(
        $user_id,
        $is_active,
    ),
);
$queries[] = array(
    "query" => "SELECT index_id, UMF.fid FROM {user_message_folder} AS UMF LEFT JOIN {message_folder} AS MF ON UMF.fid = MF.fid WHERE UMF.mid = ? AND MF.uid = ?",
    'parameters' => array(
        1,
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT L.* FROM {links} AS L INNER JOIN {linkcategories} AS LC ON L.category_id = LC.category_id WHERE LC.user_id = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT name FROM {message_folder} WHERE fid = ?",
    'parameters' => array(
        $fid,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id,created FROM {boardmessages} WHERE  parent_id = ? LIMIT 1",
    'parameters' => array(
        13,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id FROM {networks_users} WHERE network_id = ? AND user_type = 'owner'",
    'parameters' => array(
        $network_id,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id FROM {relations} WHERE relation_id = ?",
    'parameters' => array(
        $relation_id,
    ),
);
$queries[] = array(
    'query' => "SELECT user_id from {relations} where relation_id = $user_id",
);
$queries[] = array(
    'query' => " SELECT position from {categories} WHERE category_id = ? ",
    'parameters' => array(
        $category_id,
    ),
);
$queries[] = array(
    'query' => "SELECT relation_id FROM {relations} WHERE user_id = ? AND relation_id > 0",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT role_id FROM {users_roles} WHERE user_id = ?",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT tag_id FROM {tags} WHERE tag_name= ?",
    'parameters' => array(
        $tag_name,
    ),
);
$queries[] = array(
    'query' => "SELECT tag_name FROM {tags}  WHERE tag_id = ?",
    'parameters' => array(
        $tag_id,
    ),
);
$queries[] = array(
    'query' => "SELECT T.tag_id AS tag_id, T.tag_name AS tag_name FROM {tags} AS T LIMIT 0, ?",
    'parameters' => array(
        1,
    ),
);
$queries[] = array(
    'query' => "SELECT user_type FROM {networks_users} WHERE network_id = ? AND user_id = ? ",
    'parameters' => array(
        1,
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT page_id, settings FROM {page_settings} WHERE user_id=? AND page_id=?",
    'parameters' => array(
        $user_id,
        $page_id,
    ),
);
$queries[] = array(
    'query' => "SELECT persona_id FROM {personas} WHERE user_id = ? ORDER BY sequence",
    'parameters' => array(
        $user_id,
    ),
);
$queries[] = array(
    'query' => "SELECT L.* FROM {links} AS L INNER JOIN {linkcategories} AS LC ON L.category_id = LC.category_id WHERE L.title = ? AND L.category_id = ? AND LC.user_id = ? AND L.link_id <> ?",
    'parameters' => array(
        'Google Search',
        1,
        $user_id,
        2,
    ),
);
$queries[] = array(
    'query' => "SELECT network_id FROM {networks} WHERE is_active = ? AND address = ? ",
    'parameters' => array(
        $is_active,
        'teknokrats',
    ),
);
$queries[] = array(
    'query' => "SELECT new_msg from {user_message_folder} WHERE fid = ?",
    'parameters' => array(
        $fid,
    ),
);
$queries[] = array(
    'query' => " SELECT N.maximum_members,N.network_id, N.name as network_name, C.category_id, C.name as category_name,N.address from { networks } as N RIGHT JOIN { categories } as C on N.category_id = C.category_id  AND N.type = ".REGULAR_NETWORK_TYPE." WHERE C.position RLIKE '^[0-9]+>$'  ORDER BY C.category_id ",
);
$queries[] = array(
    'query' => "SELECT N.*, NU.user_id FROM {networks} AS N LEFT JOIN {networks_users} AS NU ON NU.network_id=N.network_id WHERE N.is_active = ? AND N.network_id = ? AND NU.user_type = ? AND N.type = ?",
    'parameters' => array(
        $is_active,
        $network_id,
        'owner',
        0,
    ),
);
$queries[] = array(
    'query' => " SELECT N.*,NU.user_type FROM {networks} AS N, {networks_users} AS NU WHERE NU.user_id = ? AND NU.network_id = N.network_id AND N.is_active = ? AND N.type = ? ",
    'parameters' => array(
        $user_id,
        $is_active,
        0,
    ),
);
$queries[] = array(
    'query' => "SELECT personas.persona_id FROM personas, persona_services WHERE (personas.user_id = ?) AND (personas.persona_service_id = persona_services.persona_service_id) AND (persona_services.symbol = ?) ORDER BY personas.sequence",
    'parameters' => array(
        $user_id,
        '',
    ),
);
// }}}
// {{{ $sql =
/* {{{ Todo: OPTIMIZE ME!!!
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = 7 AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'media/audio%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created  FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = 7 AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'media/image%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = 7 AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'media/video%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = ? AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'event%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = ? AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'review%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = ? AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'showcase/group%' ORDER BY $order_by $limit", 'parameters' => array());
$queries[] = array('query' => "SELECT C.content_id As content_id, C.title As title, C.body As body, C.author_id As author_id, C.type as type, C.changed As changed, C.created as created FROM {contents} As C, {contents_sbmicrocontents} As CM, {sbmicrocontent_types} As SM WHERE C.collection_id < 0 AND C.is_active = ?$author AND C.type = ? AND CM.content_id = C.content_id AND SM.sbtype_id = CM.microcontent_id AND SM.name LIKE 'showcase/person%' ORDER BY $order_by $limit", 'parameters' => array());
}}} */
/*
//$queries[] = array('query' => "SELECT content_id, title, body, author_id, type, changed, name, created FROM {contents} As C LEFT JOIN {content_types} As CT ON C.type=CT.type_id WHERE C.collection_id = -1 AND C.author_id = ? AND C.is_active = 1 $with_type ORDER BY $order_by $limit", 'parameters' => array());
//$queries[] = array('query' => "SELECT content_id, title, body, type, author_id, changed, created FROM {contents} WHERE collection_id = -1 AND is_active = 1 AND display_on = ? $with_type ORDER BY $order_by $limit", 'parameters' => array());
$queries[]   = array('query' => "SELECT count(*) AS cnt FROM {networks} AS N WHERE category_id = ? AND is_active = ? AND type = ? ", 'parameters' => array());
//$queries[] = array('query' => "SELECT COUNT(NU.user_id) AS members, N.*, NU.user_id AS owner_id,name as network_name FROM  { networks } AS N LEFT JOIN { networks_users } AS NU ON N.network_id = NU.network_id WHERE NU.user_id = ? AND N.type = ".REGULAR_NETWORK_TYPE." AND N.is_active = 1 GROUP BY N.network_id ORDER BY $order_by  $limit", 'parameters' => array());
//$queries[] = array('query' => " SELECT * FROM {boardmessages} WHERE parent_id = ? AND parent_type = ? ORDER BY $order_by $limit ", 'parameters' => array());
//$queries[] = array('query' => "SELECT * from {message_folder} WHERE uid = ? AND name NOT IN (?, ?, ?)", 'parameters' => array());
//$queries[] = array('query' => "SELECT N.member_count AS members, N.*, N.owner_id AS owner_id, N.name as network_name FROM  {networks} AS N WHERE N.type = ".REGULAR_NETWORK_TYPE." AND N.is_active = 1 ORDER BY $order_by  $limit", 'parameters' => array());
*/
// }}}
// }}}
// {{{ Run the test and show failures
$test     = new MysqlPerformanceTest($queries);
$result   = $test->run();
$failures = $result->failures();
print $failures[0]->thrownException()->toString();
// }}}
?>