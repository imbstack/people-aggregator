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
/**
*
* @class CSVParser
*
* @author     Zoran Hron <zhron@broadbandmechanics.com>
* @version    0.1.0
*
* @brief CSV parser / data mapper
*
*
**/
class CSVParser {

    public $lastError;

    public $contacts;

    public $separator;

    public $mapped_contacts;

    private $valid_separators = array(
        ',',
        ';',
        ':',
        '|',
        '\t',
    );

    public function __construct($data_str) {
        $this->contacts = $this->formatData($data_str);
        //    echo "<pre>" . print_r($this->contacts, 1) . "</pre>";
    }

    private function formatData($data_str) {
        $data = array();
        $lines = explode("\n", $data_str);
        $this->separator = $this->getCSVSeparator($lines);
        foreach($lines as $line) {
            $line_str = trim($line);
            if(!empty($line_str)) {
                $data[] = explode($this->separator, $line_str);
            }
        }
        $keys = array_shift($data);
        // first element contains field keys
        $out_data = array();
        foreach($data as $k => $v) {
            for($n = 0; $n < count($keys); $n++) {
                if(!empty($v[$n])) {
                    $out_data[$k][trim($keys[$n], "\"")] = trim($v[$n], "\"");
                }
                else {
                    //           echo "Key: $k, Value: <pre>".print_r($v[$n], 1). "</pre> <br />";
                }
            }
        }
        return $out_data;
    }

    private function getCSVSeparator($csv_lines) {
        $sep_weight = array();
        foreach($this->valid_separators as $key => $val) {
            $sep_weight[$key] = 0;
            foreach($csv_lines as $line) {
                $sep_weight[$key] += substr_count($line, $val);
            }
        }
        $sep_weight = array_flip($sep_weight);
        ksort($sep_weight, SORT_NUMERIC);
        $sep_idx = @end($sep_weight);
        if(!empty($sep_idx) && is_numeric($sep_idx)) {
            $separator = $this->valid_separators[$sep_idx];
            // separator found!
        }
        else {
            $separator = $this->valid_separators[0];
            // return default CSV separator!
        }
        return $separator;
    }

    public function getCSVContacts($data_mapper = 'CSVDataMapper', $email_invite = false) {
        if(count($this->contacts) > 0) {
            if(!@include_once("api/ProfileIO/map/$data_mapper.class.php")) {
                $this->lastError = "Data mapper class \"$data_mapper\" not found";
                return false;
            }
            $res = eval("\$this->mapped_contacts = $data_mapper::processInData(\$this->contacts); return true;");
            if(!$res) {
                $this->lastError = __("Data mapping error. Invalid data format.");
                return false;
            }
        }
        if($email_invite) {
            return $this->getInviteMails($this->mapped_contacts);
        }
        else {
            return $this->mapped_contacts;
        }
    }

    protected function getInviteMails($contacts) {
        $invites = array();
        $cnt = 0;
        foreach($contacts as $contact) {
            $name = null;
            $email = null;
            $type = 'personal';
            if(!empty($contact['personal']['email'])) {
                $email = $contact['personal']['email'];
                $type = 'personal';
            }
            elseif(!empty($contact['professional']['email'])) {
                $email = $contact['professional']['email'];
                $type = 'professional';
            }
            else {
                $email = __('-no email address-');
            }
            if(!empty($contact['personal']['first_name']) || !empty($contact['personal']['last_name'])) {
                $name = null;
                if(!empty($contact['personal']['first_name'])) {
                    $name = $contact['personal']['first_name'].' ';
                }
                if(!empty($contact['personal']['last_name'])) {
                    $name .= $contact['personal']['last_name'];
                }
            }
            elseif(!empty($contact['extra']['contact_identifier'])) {
                $name = $contact['extra']['contact_identifier'];
            }
            elseif(!empty($contact['extra']['e-mail_display_name'])) {
                $name = $contact['extra']['e-mail_display_name'];
            }
            elseif(!empty($contact['professional']['company'])) {
                $name = $contact['professional']['company'];
            }
            else {
                $name = $email;
            }
            if($email) {
                $invites[$cnt]['name'] = $name;
                $invites[$cnt]['email'] = $email;
                $invites[$cnt++]['type'] = $type;
            }
        }
        return $invites;
    }
}
