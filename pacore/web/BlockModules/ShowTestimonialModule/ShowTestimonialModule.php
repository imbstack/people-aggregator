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

require_once "api/Testimonials/Testimonials.php";

class ShowTestimonialModule extends Module {

    public $module_type = 'user';

    public $module_placement = 'middle';

    public $outer_template = 'outer_public_center_module.tpl';

    function __construct() {
        parent::__construct();
    }

    public function initializeModule($request_method, $request_data) {
        global $paging;
        if(!empty(PA::$page_uid)) {
            //Approved pending will be shown for the page user.
            $count = Testimonials::count_testimonials(PA::$page_uid, APPROVED);
            if(!$count) {
                return 'skip';
            }
            $this->testimonial_status = APPROVED;
            $this->title              = ucfirst(PA::$page_user->first_name).'\'s ';
            $this->title             .= __('Testimonials');
        }
        elseif(!empty(PA::$login_uid)) {
            //Pending testimonal will be shown for the logged in user.
            $this->mode = PRI;
            $count = Testimonials::count_testimonials(PA::$login_uid, PENDING);
            if(!$count) {
                return 'skip';
            }
            $this->testimonial_status = PENDING;
            $this->title              = __('New Testimonials');
            $this->recipient_id       = PA::$login_uid;
        }
        else {
            return 'skip';
        }
        $this->Paging = $paging;
    }

    function render() {
        global $login_uid, $page_uid;
        $testi                 = new Testimonials();
        $testi->recipient_id   = (isset($this->recipient_id)) ? $this->recipient_id : $page_uid;
        $testi->status         = (isset($this->testimonial_status)) ? $this->testimonial_status : APPROVED;
        $this->Paging["count"] = $testi->get_multiple_testimonials(TRUE);
        $result                = $testi->get_multiple_testimonials(FALSE, $this->Paging["show"], $this->Paging['page']);
        $this->links           = $this->manage_links($result);
        $this->inner_HTML      = $this->generate_inner_html();
        $content               = parent::render();
        return $content;
    }

    function generate_inner_html() {
        $tmp_file = PA::$blockmodule_path.'/'.get_class($this).'/center_inner_public.tpl';
        $inner_html_gen = &new Template($tmp_file, $this);
        $inner_html_gen->set('links', $this->links);
        $inner_html_gen->set('mode', $this->mode);
        $Pagination = new Pagination;
        //    $Pagination->page_var = 'pg';
        $Pagination->setPaging($this->Paging);
        $this->page_first = $Pagination->getFirstPage();
        $this->page_last  = $Pagination->getLastPage();
        $this->page_links = $Pagination->getPageLinks();
        $inner_html_gen->set('page_first', $this->page_first);
        $inner_html_gen->set('page_last', $this->page_last);
        $inner_html_gen->set('page_links', $this->page_links);
        $inner_html = $inner_html_gen->fetch();
        return $inner_html;
    }

    function manage_links($links) {
        global $login_uid, $page_uid;
        $cnt = count($links);
        if($cnt == 0) {
            return $links;
        }
        $result = $links;
        for($i = 0; $i < $cnt; $i++) {
            $login                  = User::get_login_name_from_id($links[$i]['sender_id']);
            $url                    = PA::$url.PA_ROUTE_USER_PUBLIC.'/'.$login;
            $result[$i]['user_url'] = $url;
            // Handling for Buttons
            if($this->mode == PRI) {
                $result[$i]['approve_link'] = PA::$url.'/testimonial_actions.php?uid='.$links[$i]['sender_id'].'&action=approve&id='.$links[$i]['testimonial_id'];
                $result[$i]['deny_link'] = PA::$url.'/testimonial_actions.php?uid='.$links[$i]['sender_id'].'&action=deny&id='.$links[$i]['testimonial_id'];
                $result[$i]['button'] = array(
                    array(
                        'caption' => __('Approve'),
                        'link' => $result[$i]['approve_link'],
                    ),
                    array(
                        'caption' => __('Deny'),
                        'link' => $result[$i]['deny_link'],
                    ),
                );
            }
            else {
                if(in_array($login_uid, array($links[$i]['sender_id'], $links[$i]['recipient_id']))) {
                    $result[$i]['delete_link'] = PA::$url.'/testimonial_actions.php?uid='.$links[$i]['sender_id'].'&action=delete&id='.$links[$i]['testimonial_id'].'&uid='.$page_uid;
                    $result[$i]['button'] = array(
                        array(
                            'caption' => __('Delete'),
                            'link' => $result[$i]['delete_link'],
                        ),
                    );
                }
            }
        }
        return $result;
    }
}
?>