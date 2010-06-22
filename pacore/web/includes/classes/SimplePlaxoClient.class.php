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
* @class SimplePlaxoClient
*
* @author     Zoran Hron <zhron@broadbandmechanics.com>
* @version    0.2.0
*
* @brief Plaxo client class
*
* @NOTE
*   Up to this time Plaxo API support access and
*   sync only for address book entries
*
**/
require_once "web/includes/classes/SimpleWebClient.class.php";
require_once "web/includes/classes/ServiceProtocolHandler.class.php";
require_once "api/Logger/Logger.php";

class PlaxoException extends Exception {

    public function __construct($message, $code = 0) {
        Logger::log("PlaxoException $code: ".$message);
        parent::__construct($message, $code);
    }
}
define("PLAXO_MSG_SUCCESS", "200");
define("PLAXO_SERVICE_URL", "https://www.plaxo.com/rest");

/*
 $plaxo_errors = array( '200' => "Success",
                        '205' => "Client should do a sync",
                        '211' => "Item not found, can’t delete",
                        '400' => "Syntax error",
                        '401' => "Authentication failed",
                        '403' => "Rejected not due authentication",
                        '404' => "Unknown Plaxo account ID",
                        '409' => "Failed due to merge conflict",
                        '410' => "Account is deleted",
                        '412' => "Incomplete command",
                        '417' => "Database server is busy, try again later",
                        '418' => "Item already exists",
                        '419' => "Merge conflict, server data wins",
                        '420' => "Account is out of sync. Client needs to do a slow sync.",
                        '500' => "Unknown error",
                        '506' => "Server is busy, try again later.",
                        '513' => "Client is too old" );

  private $mail_labels  = array('PersonalEmail', 'PersonalEmail2', 'PersonalEmail3',
                                'BusinessEmail', 'BusinessEmail2', 'BusinessEmail3');
  private $ident_labels = array('DisplayName', 'NickName', 'ContactIdentifier', 'NameTitle', 'ManagerName', 'Company');
*/
class SimplePlaxoClient extends ServiceProtocolHandler {
    const PL_CONTACTS = 0;
    const PL_CALENDAR = 1;
    const PL_TASKS    = 2;
    const PL_NOTES    = 3;

    public $guid = false;

    public $uhid = false;

    private $auth_method;

    private $proto_ver;

    private $client_name;

    private $client_os;

    private $client_platform;

    private $agent;

    private $status;

    private $status_code;

    private $last_anchor;

    private $password;

    public $mapped_contacts;

    public function __construct($proto_ver = 1, $client_name = 'Dev Clientname/1.0', $client_os = 'linux/PHP', $client_platform = 'PlaxoThunderBird/0.9') {
        $this->proto_ver       = $proto_ver;
        $this->client_name     = $client_name;
        $this->client_os       = $client_os;
        $this->client_platform = $client_platform;
        $this->last_anchor     = 0;
        $this->connected       = false;
        $this->mapped_contacts = array();
        $this->agent           = new SimpleWebClient(PLAXO_SERVICE_URL);
    }

    public function connectService($auth_data = array()) {
        if(empty($auth_data['user_id']) || empty($auth_data['password'])) {
            $this->lastError = __("User ID or Password field can't be blank.");
            return false;
        }
        if(!$this->agent->connect()) {
            $this->lastError = __("Can't estabilish WEB connection.");
            return false;
        }
        if(!$this->getGUID()) {
            return false;
        }
        $user_id           = trim($auth_data['user_id']);
        $password          = trim($auth_data['password']);
        $email_regexp      = "/^[a-z0-9!\#$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+(?:\.[a-z0-9!\#$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
        $this->auth_method = (preg_match($email_regexp, $user_id)) ? 'Plaxo' : 'Aol';
        return $this->plaxoLogIn($user_id, $password);
    }

    public function getGUID() {
        $result = false;
        $buffer = 'package=';
        $params = array(
            'ProtoVer' => $this->proto_ver,
            'Client'   => $this->client_name,
            'OS'       => $this->client_os,
            'Platform' => $this->client_platform,
        );
        $buffer .= $this->formatPlaxoTag('Header', $params)."\n";
        $buffer .= $this->formatPlaxoTag('CreateGUID', array());
        try {
            $resp_data = $this->readData(array('buffer' => $buffer));
        }
        catch(PlaxoException$e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        $data = $this->findPlaxoTag('Data', $resp_data);
        if(!$data) {
            $this->lastError = __("Plaxo data parser error.");
            return false;
        }
        $guid       = $data['fields']['GUID'];
        $parts      = explode(':', $guid);
        $this->guid = trim($parts[1]);
        $result     = true;
        return $result;
    }

    public function plaxoLogIn($username, $password) {
        $result = false;
        if($this->guid) {
            $params = array(
                'ProtoVer'   => $this->proto_ver,
                'ClientID'   => 'PLXI: '.$this->guid,
                'Identifier' => $username,
                'AuthMethod' => $this->auth_method,
                'Password'   => $password,
                'Client'     => $this->client_name,
                'OS'         => $this->client_os,
                'Platform'   => $this->client_platform,
            );
            $buffer = 'package='.$this->formatPlaxoTag('Header', $params);
            try {
                $resp_data = $this->readData(array('buffer' => $buffer));
            }
            catch(PlaxoException$e) {
                $this->lastError = $e->getMessage();
                return false;
            }
            if($this->status_code != PLAXO_MSG_SUCCESS) {
                $this->lastError = $this->status;
                return false;
            }
            $header = $this->findPlaxoTag('Header', $resp_data);
            if(!$header) {
                $this->lastError = __("Invalid Plaxo Header received.");
                return false;
            }
            $uhid            = $header['fields']['Uhid'];
            $this->uhid      = trim($uhid);
            $this->password  = $password;
            $this->connected = true;
            $result          = true;
        }
        return $result;
    }

    public function getPlaxoFolders() {
        $result = false;
        if(!$this->uhid || empty($this->password)) {
            $this->lastError = "Function getPlaxoFolders() can't be called before Plaxo authentication.";
            return false;
        }
        $buffer = 'package=';
        $params1 = array(
            'ProtoVer' => $this->proto_ver,
            'ClientID' => 'PLXI: '.$this->guid,
            'Uhid'     => $this->uhid,
            'Password' => $this->password,
            'Client'   => $this->client_name,
            'OS'       => $this->client_os,
            'Platform' => $this->client_platform,
        );
        $params2 = array(
            'Type' => 'folder',
            'Target' => 'folders',
        );
        $buffer .= $this->formatPlaxoTag('Header', $params1)."\n";
        $buffer .= $this->formatPlaxoTag('Get', $params2);
        try {
            $resp_data = $this->readData(array('buffer' => $buffer));
        }
        catch(PlaxoException$e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        if($this->status_code != PLAXO_MSG_SUCCESS) {
            $this->lastError = __("Unable to get list of the Plaxo folders.");
            return false;
        }
        $data = $this->findPlaxoTag('Data', $resp_data, true);
        if(count($data) <= 0) {
            $this->lastError = __("Plaxo data parser error.");
            return false;
        }
        foreach($data as $folder_data) {
            $this->folders[] = $folder_data['fields'];
        }
        return true;
    }

    public function readPlaxoFolder($target, $source) {
        if(!$this->uhid || empty($this->password)) {
            $this->lastError = "Function readPlaxoFolder() can't be called before Plaxo authentication.";
            return false;
        }
        $buffer = 'package=';
        $params1 = array(
            'ProtoVer' => $this->proto_ver,
            'ClientID' => 'PLXI: '.$this->guid,
            'Uhid'     => $this->uhid,
            'Password' => $this->password,
            'Client'   => $this->client_name,
            'OS'       => $this->client_os,
            'Platform' => $this->client_platform,
        );
        $params2 = array(
            'Target'     => $target,
            'Source'     => $source,
            'LastAnchor' => $this->last_anchor,
            'NextAnchor' => $this->last_anchor+1,
        );
        $buffer .= $this->formatPlaxoTag('Header', $params1)."\n";
        $buffer .= $this->formatPlaxoTag('Sync', $params2);
        try {
            $resp_data = $this->readData(array('buffer' => $buffer));
        }
        catch(PlaxoException$e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        if($this->status_code != PLAXO_MSG_SUCCESS) {
            $this->lastError = __("Unable to read the Plaxo folders.");
            return false;
        }
        $data = array();
        $data_arr = $this->findPlaxoTag('Data', $resp_data, true);
        foreach($data_arr as $entry) {
            $data[] = $entry['fields'];
        }
        return $data;
    }

    public function getPlaxoContacts($data_mapper = 'PlaxoDataMapper', $email_invite = false) {
        if($this->getPlaxoFolders()) {
            $contact_folders = array();
            foreach($this->folders as $folder) {
                if($folder['Type'] == SimplePlaxoClient::PL_CONTACTS) {
                    $contact_folders[] = $folder['DisplayName'].'/'.$folder['FolderID'];
                }
            }
            $contacts = array();
            foreach($contact_folders as $folder_name) {
                $cntcts = array();
                $cntcts = $this->readPlaxoFolder($folder_name, 'personal');
                if($cntcts) {
                    $contacts = array_merge($contacts, $cntcts);
                }
            }
            if(count($contacts) > 0) {
                if(!@include_once("api/ProfileIO/map/$data_mapper.class.php")) {
                    $this->lastError = "Data mapper class \"$data_mapper\" not found";
                    return false;
                }
                eval("$this->mapped_contacts = $data_mapper::processInData($contacts);");
                //      echo "Contacts: <pre>" . print_r($contacts, 1) . "</pre>";
                //      echo "Mapped: <pre>" . print_r($this->mapped_contacts, 1) . "</pre>";
            }
            if($email_invite) {
                return $this->getInviteMails($this->mapped_contacts);
            }
            else {
                return $this->mapped_contacts;
            }
        }
        return false;
    }

    public function readData($params = array()) {
        $resp              = null;
        $this->status      = null;
        $this->status_code = 0;
        if(!empty($params['buffer'])) {
            if(!$this->agent->send($params['buffer'], true)) {
                throw new PlaxoException($this->agent->getError());
            }
            $resp = $this->agent->getResponse();
            if(!$resp) {
                throw new PlaxoException('No response from Plaxo service.');
            }
            $response_data = $this->parseResponseData($resp);
            $status = $this->findPlaxoTag('Status', $response_data);
            if($status) {
                $this->status = $status['fields']['Message'];
                $this->status_code = $status['fields']['Code'];
            }
        }
        return $response_data;
    }

    public function disconnectService() {
    }

    public function writeData($params = array(), $data) {
    }

    public function synchronizeData($params = array()) {
    }

    public function parseResponseData($buffer) {
        $matches = array();
        $ret_data = array();
        preg_match_all("#\[([^\x5D])*\]#", $buffer, $matches);
        if(isset($matches[0]) && (count($matches[0]) > 0)) {
            foreach($matches[0] as $line) {
                $line = trim($line, "[ ]");
                $is_close = strpos($line, "'/");
                if(($is_close == 0) && ($is_close !== false)) {
                    // closing tag - ignore it
                    continue;
                }
                $str_arr  = explode(',', $line);
                $tag_name = trim(array_shift($str_arr), " '");
                $data_arr = array();
                $offs     = 0;
                for($i = 0; $i < ((count($str_arr))/2); $i++) {
                    if(!empty($str_arr[$i+$offs+1])) {
                        $data_arr[trim($str_arr[$i+$offs], " '")] = trim($str_arr[$i+$offs+1], " '");
                        $offs += 1;
                    }
                }
                $ret_data[] = array(
                    'tag' => $tag_name,
                    'fields' => $data_arr,
                );
            }
        }
        return $ret_data;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function findPlaxoTag($tag_name, $response_arr, $all_tags = false) {
        $result = ($all_tags) ? array() : null;
        foreach($response_arr as $tag_arr) {
            if($tag_arr['tag'] == $tag_name) {
                if($all_tags == false) {
                    $result = $tag_arr;
                    break;
                }
                else {
                    $result[] = $tag_arr;
                }
            }
        }
        return $result;
    }

    private function formatPlaxoTag($tag_name, $params = array(), $tag_content = null) {
        $tag = "['$tag_name'";
        foreach($params as $key => $value) {
            $tag .= ", '$key', '$value'";
        }
        $tag .= "]%0a";
        if($tag_content) {
            $tag .= $tag_content;
        }
        $tag .= "['/".$tag_name."']";
        return $tag;
    }
}
?>
