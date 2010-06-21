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

define("DOMAIN_BLACKLISTED_MANUALLY", 1);
define("DOMAIN_BLACKLISTED_AUTOMATICALLY", 2);

class SpamDomain {

    public $domain, $count, $blacklisted, $whitelisted;

    public function __construct($host, $noisy = FALSE) {
        if(!trim($host)) {
            throw new PAException(BAD_PARAMETER, "Invalid host");
        }
        $dom = Dal::query_one_assoc("SELECT * FROM spam_domains WHERE domain=?", array($host));
        if($dom) {
            // exists
            foreach($dom as $k => $v) {
                $this->$k = $v;
            }
        }
        else {
            // need to create it
            $this->domain = $host;
            $this->count = $this->blacklisted = $this->whitelisted = 0;
            if($noisy) {
                echo "Querying blacklists for $host:";
            }
            foreach(array("multi.uribl.com", "multi.surbl.org") as $bl) {
                if($noisy) {
                    echo " $bl";
                }
                if(gethostbyname("$host.$bl") != "$host.$bl") {
                    $this->blacklisted = DOMAIN_BLACKLISTED_AUTOMATICALLY;
                    if($noisy) {
                        echo " BLACKLISTED!";
                    }
                    break;
                }
            }
            if($noisy) {
                echo "\n";
            }
            Dal::query("INSERT INTO spam_domains SET domain=?, blacklisted=?", array($this->domain, $this->blacklisted));
            $this->id = Dal::insert_id();
        }
    }

    public function set_blacklisted($state) {
        $this->blacklisted = $state;
        Dal::query("UPDATE spam_domains SET blacklisted=? WHERE id=?", array($this->blacklisted, $this->id));
        if($state) {
            // delete all comments containing this domain.
            Dal::query("UPDATE comments c, domains_in_comments dic SET c.is_active=0, c.spam_state=".SPAM_STATE_DOMAIN_BLACKLIST." WHERE c.comment_id=dic.comment_id AND dic.domain_id=?", $this->id);
        }
    }

    public function set_whitelisted($state) {
        $this->whitelisted = $state;
        Dal::query("UPDATE spam_domains SET whitelisted=? WHERE id=?", array($this->whitelisted, $this->id));
    }

    /* Remember that this domain has appeared $n_occurrences times in
       comment $comment_id */
    public function link_to_comment($comment_id, $n_occurrences) {
        //    Dal::query("DELETE FROM domains_in_comments WHERE domain_id=? AND comment_id=?", array($this->id, (int)$comment_id));
        Dal::query("INSERT INTO domains_in_comments SET domain_id=?, comment_id=?, occurrences=?", array($this->id, (int) $comment_id, (int) $n_occurrences));
    }

    public static function clear_domains_for_comment($comment_id) {
        Dal::query("DELETE FROM domains_in_comments WHERE comment_id=?", array($comment_id));
    }

    public function recalculate_link_counts() {
        return SpamDomain::recalculate_link_counts_for_domain_id($this->id);
    }

    public static function recalculate_link_counts_for_domain_id($domain_id) {
        list($ct) = Dal::query_one("SELECT COUNT(comment_id) FROM domains_in_comments WHERE domain_id=?", array($domain_id));
        list($active_ct) = Dal::query_one("SELECT COUNT(c.comment_id) FROM comments c, domains_in_comments dic WHERE c.comment_id=dic.comment_id AND c.is_active=1 AND dic.domain_id=?", array($domain_id));
        Dal::query("UPDATE spam_domains SET count=?, active_count=? WHERE id=?", array($ct, $active_ct, $domain_id));
    }

    public static function recalculate_total_link_counts() {
        $sth = Dal::query("SELECT id FROM spam_domains");
        while($r = Dal::row($sth)) {
            set_time_limit(60);
            $domain_id = $r[0];
            SpamDomain::recalculate_link_counts_for_domain_id($domain_id);
        }
    }

    public static function get_most_common_domains($n = 25) {
        $sth = Dal::query("SELECT id,domain,active_count,count,blacklisted FROM spam_domains ORDER BY active_count DESC LIMIT $n");
        return Dal::all_assoc($sth);
    }

    public static function count_domains() {
        list($total) = Dal::query_one("SELECT COUNT(*) FROM spam_domains");
        list($total_blacklisted) = Dal::query_one("SELECT COUNT(*) FROM spam_domains WHERE blacklisted=1");
        return array($total, $total_blacklisted);
    }

    public function find_associated_domains() {
        $sth = Dal::query("SELECT sd.domain domain, dic2.domain_id domain_id, COUNT(dic2.domain_id) match_count
      FROM spam_domains sd, domains_in_comments dic1, domains_in_comments dic2
      WHERE dic1.domain_id=? AND dic1.comment_id=dic2.comment_id AND sd.id=dic2.domain_id
      GROUP BY dic2.domain_id ORDER BY match_count DESC", array($this->id));
        return Dal::all_assoc($sth);
    }
}
?>