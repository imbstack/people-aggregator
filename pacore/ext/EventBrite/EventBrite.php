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

require_once "HTTP/Client.php";
require_once "ext/parseCSV/parsecsv.php";

class EventBrite {

    public $email = "", $password = "";

    public function __construct($email, $password) {
        $this->email = $email;
        $this->password = $password;
    }

    public function get_attendees($event_id) {
        $url = "http://www.eventbrite.com/myattendeestext/".$event_id."/attendees-".$event_id.".txt?sortby=&show=gross,type,status,prefix,last_name,first_name,email,job_title,company,work,work_phone,survey,actions&filterby=all,attending,all,all";
        $client = new HTTP_Client();
        // log in
        $ret = $client->post("http://www.eventbrite.com/signin", array("submitted" => "1", "referrer" => "", "email" => $this->email, "passwd" => $this->password, "remember_me" => "0"));
        $response = $client->currentResponse();
        //	var_dump($client->currentResponse());
        $dom = new DOMDocument();
        @$dom->loadHTML($response['body']);
        $xp = new DOMXPath($dom);
        //	echo "----------- generated xml ----------------\n";
        //	echo $dom->saveXML();
        $errors = $xp->query("//div[@id='error']");
        if($errors->length) {
            // an error occurred - most likely
            $error_text = trim($xp->query("text()", $errors->item(0))->item(0)->nodeValue);
            throw new PAException(USER_NOT_FOUND, "Error from EventBrite: ".$error_text);
        }
        // now fetch the attendee list CSV
        $ret       = $client->get($url);
        $response  = $client->currentResponse();
        $mime_type = $response['headers']['content-type'];
        if($mime_type != 'text/plain') {
            throw new PAException(GENERAL_SOME_ERROR, "Invalid MIME type received from EventBrite (expected text/plain, received $mime_type)");
        }
        return $this->parse_attendee_list($response['body']);
    }

    public function parse_attendee_list($csv_text) {
        $csv            = new parseCSV();
        $csv->delimiter = "\t";
        $csv->data      = $csv->parse_string($csv_text);
        return $csv->data;
    }
}
?>