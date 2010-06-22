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
 * @class MSNLiveClient
 *
 * The MSNLiveClient class implements the basics methods for Windows Live authentication
 * and Windows Live Contacts API - Alpha 1.0 
 * 
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 * 
 */
require_once "web/includes/classes/SimpleSSLClient.class.php";
require_once "web/includes/classes/ServiceProtocolHandler.class.php";
require_once "api/Logger/Logger.php";
define("MSLIVE_MSG_SUCCESS", "200");
define('DEFAULT_MSN_SERVICES_SERVER', 'cumulus.services.live.com');
define('DEFAULT_MSN_SERVICES_PORT', 443);
define('DEFAULT_MSN_LIVE_SERVER', 'dev.login.live.com');
define('DEFAULT_MSN_LIVE_PORT', 443);
define('DEFAULT_MSN_LIVE_PROTOCOL', 'tls');
// NOTE: Microsoft plans to discontinue the use of the SSLv2 (Secure Socket Layer)
// and it will be raplaced with TLSv1
define('MSN_LIVE_XML_FILE', '/web/includes/xml/MSNLiveAuth.xml');

class MSLiveException extends Exception {

    public function __construct($message, $code = 0) {
        Logger::log("MSLiveException $code: ".$message);
        parent::__construct($message, $code);
    }
}

class SimpleMSLiveClient extends ServiceProtocolHandler {

    public $hasErrors = false;

    protected $errors = array();

    /**
     * Application ID - in Windows Live Aplha 1.0 should be set to "10"
     *
     * @var (\p string)    Application ID string
     */
    private $ApplicationID;

    private $PolicyReference;

    private $MSNLiveServer;

    private $MSNLivePort;

    private $AccountID;

    private $authHeader;

    private $binarySecToken;

    private $status;

    private $status_code;

    public $mapped_contacts;

    /**
     * SOAP request
     *
     * @var XML string
     */
    private $Xml;

    public function disconnectService() {
    }

    public function writeData($params = array(), $data) {
    }

    public function synchronizeData($params = array()) {
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function __construct($server = DEFAULT_MSN_LIVE_SERVER, $port = DEFAULT_MSN_LIVE_PORT, $applicationId = '10', $policyReference = 'MBI') {
        $this->connected       = false;
        $this->MSNLiveServer   = $server;
        $this->MSNLivePort     = $port;
        $this->ApplicationID   = $applicationId;
        $this->PolicyReference = trim(str_replace(",", "&", urldecode($policyReference)));
        $this->agent           = new SimpleSSLClient(DEFAULT_MSN_LIVE_PROTOCOL.'://'.DEFAULT_MSN_SERVICES_SERVER, DEFAULT_MSN_SERVICES_PORT, 30);
        $this->auth_agent      = new SimpleSSLClient(DEFAULT_MSN_LIVE_PROTOCOL.'://'.$this->MSNLiveServer, $this->MSNLivePort, 30);
        $this->mapped_contacts = array();
    }

    /**
   * Executes the main Windows Live authentication and authorisation tasks
   * 
   * 
   *   @param $user_id :                      (\p string)    user Windows Live ID (email)
   *   @param $password :                   (\p string)    user password
   *
   * @return                (\p bool)       is success
   *
   */
    public function connectService($auth_data = array()) {
        if(empty($auth_data['user_id']) || empty($auth_data['password'])) {
            $this->lastError = __("User ID or Password field can't be blank.");
            return false;
        }
        $user_id = trim($auth_data['user_id']);
        $password = trim($auth_data['password']);
        return $this->msLiveLogIn($user_id, $password);
    }

    public function msLiveLogIn($user_id, $password) {
        $success = false;
        try {
            $success = $this->getAuthToken($user_id, $password);
        }
        catch(MSLiveException$e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $success;
    }

    private function getAuthToken($user_id, $password) {
        $docXML = new DOMDocument();
        $xml_tpl = (file_exists(PA::$project_dir.MSN_LIVE_XML_FILE)) ? PA::$project_dir.MSN_LIVE_XML_FILE : PA::$core_dir.MSN_LIVE_XML_FILE;
        if(!$docXML->load($xml_tpl)) {
            throw new MSLiveException(__("Invalid or missing SOAP XML file").": \"".MSN_LIVE_XML_FILE."\"");
            return false;
        }
        $this->AccountID = $user_id;
        $this->Xml       = $docXML->saveXML();
        $this->Xml       = str_replace("app_id", $this->ApplicationID, $this->Xml);
        $this->Xml       = str_replace("user_name", $user_id, $this->Xml);
        $this->Xml       = str_replace("user_pswd", $password, $this->Xml);
        $this->Xml       = str_replace("policy_ref", $this->PolicyReference, $this->Xml);
        $SOAPRequest = array(
            'method' => "POST /wstlogin.srf HTTP/1.1\r\n",
            'header' => "Expect: 100-continue\r\n"."Host: $this->MSNLiveServer\r\n"."Content-Type: application/soap+xml; charset=UTF-8\r\n"."Connection: Close\r\n"."Content-Length: ".strlen($this->Xml)."\r\n\r\n".$this->Xml,
        );
        $out_data = implode('', $SOAPRequest);
        try {
            $response = $this->readData(array('buffer' => $out_data, 'agent' => $this->auth_agent));
        }
        catch(MSLiveException$e) {
            throw new MSLiveException($e->getMessage());
        }
        if($this->status_code != MSLIVE_MSG_SUCCESS) {
            throw new MSLiveException($this->status);
        }
        $xmlStart = strpos($response, '<?xml');
        if($xmlStart === false) {
            throw new MSLiveException(__("Invalid response received from").":  '$this->MSNLiveServer'!");
        }
        $xmlResponse = substr($response, $xmlStart);
        $respDOM = @DOMDocument::loadXML($xmlResponse);
        if(!$respDOM) {
            throw new MSLiveException(__("Invalid XML response received from").":  '$this->MSNLiveServer'!");
        }
        $xpath = new DOMXpath($respDOM);
        $xpath->registerNamespace("wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
        $query = '//wsse:BinarySecurityToken/text()';
        $entries = $xpath->query($query);
        if($entries->length == 0) {
            throw new MSLiveException(__("BinarySecurityToken not found in XML response received from").":  '$this->MSNLiveServer'!");
        }
        $this->binarySecToken = substr($entries->item(0)->nodeValue, 2);
        $this->authHeader = 'WLID1.0 t="'.$this->binarySecToken.'"';
        echo($response);
        return true;
    }

    public function readData($params = array()) {
        $resp              = null;
        $this->status      = null;
        $this->status_code = 0;
        if(!empty($params['buffer']) && !empty($params['agent'])) {
            $agent = $params['agent'];
            if(!$agent->connect()) {
                throw new MSLiveException(__("Can't estabilish WEB connection."));
            }
            if(!$agent->send($params['buffer'], true)) {
                throw new MSLiveException($this->agent->getError());
            }
            $resp = trim($agent->getResponse());
            if(!$resp) {
                throw new MSLiveException('No response from Windows Live service. Try again.');
            }
            $this->lastError = $this->getStatus($resp);
            $agent->disconnect();
        }
        return $resp;
    }

    private function getStatus($response) {
        $regexp            = "/.*(\<S\:Envelope.*\<\/S\:Envelope\>).*/";
        $this->status      = 'Ok';
        $this->status_code = 200;
        if(!empty($response)) {
            $matches = array();
            if(preg_match($regexp, $response, $matches)) {
                $resp_xml = trim($matches[0]);
                $respDOM = @DOMDocument::loadXML($resp_xml);
                if(!$respDOM) {
                    $this->lastError = __("Invalid XML response received from").":  '$this->MSNLiveServer'!";
                    return false;
                }
                $xpath = new DOMXpath($respDOM);
                $xpath->registerNamespace("s", "http://www.w3.org/2003/05/soap-envelope");
                $xpath->registerNamespace("wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
                $xpath->registerNamespace("wsu", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd");
                $xpath->registerNamespace("wst", "http://schemas.xmlsoap.org/ws/2005/02/trust");
                $xpath->registerNamespace("psf", "http://schemas.microsoft.com/Passport/SoapServices/SOAPFault");
                $query = '//S:Fault';
                $err_entries = $xpath->query($query);
                if($err_entries->length > 0) {
                    $psf_errs = $xpath->query("//psf:internalerror/psf:code");
                    if($psf_errs->length > 0) {
                        $psf_error_code = trim($psf_errs->item(0)->nodeValue);
                    }
                    $psf_errs = $xpath->query("//psf:internalerror/psf:text");
                    if($psf_errs->length > 0) {
                        $psf_error_text = trim($psf_errs->item(0)->nodeValue);
                    }
                    $general_err = $xpath->query("//S:Reason/S:Text");
                    if($general_err->length > 0) {
                        $general_err_text = trim($general_err->item(0)->nodeValue);
                    }
                    $this->status_code = (isset($psf_error_code)) ? $psf_error_code : 500;
                    // '500' -> Unknown error
                    if(isset($psf_error_code)) {
                        $this->status = $psf_error_text;
                    }
                    elseif(isset($psf_error_code)) {
                        $this->status = $general_err_text;
                    }
                    else {
                        $this->status = __("Unknown communication error has occured.");
                    }
                }
            }
        }
        else {
            $this->status_code = 500;
            $this->status = __('Communication error - no response received.');
        }
        return $this->status;
    }

    /**
   * Retrieve complete user address book and contacts from a Windows Live account
   * 
   * 
   *   @param $server :                     (\p string)    Windows Live contacts server
   *   @param $port :                       (\p string)    port
   *
   * @return                (\p bool)       is success
   *
   */
    public function getAddressBook($server = DEFAULT_MSN_SERVICES_SERVER) {
        $conatacts = array();
        $accountPath = "/$this->AccountID/LiveContacts/MyContacts";
        $requestData = array(
            'method' => "GET $accountPath HTTP/1.1\r\n",
            'header' => "Authorization: $this->authHeader\r\n"."Host: ".$server."\r\n"."Connection: Close\r\n\r\n",
        );
        $out_data = implode('', $requestData);
        try {
            $response = $this->readData(array('buffer' => $out_data, 'agent' => $this->agent));
        }
        catch(MSLiveException$e) {
            throw new MSLiveException($e->getMessage());
        }
        if($this->status_code != MSLIVE_MSG_SUCCESS) {
            throw new MSLiveException($this->status);
        }
        echo htmlentities($response);
        $xmlStart = strpos($response, '<?xml');
        if($xmlStart === false) {
            throw new MSLiveException(__("AdrBook: Invalid response received from").":  '$this->MSNLiveServer'!");
            return false;
        }
        $xmlResponse = substr($response, $xmlStart);
        $respDOM = @DOMDocument::loadXML($xmlResponse);
        if(!$respDOM) {
            throw new MSLiveException(__("Invalid XML response received from").":  '$this->MSNLiveServer'!");
            return false;
        }
        $xpath      = new DOMXpath($respDOM);
        $cont_nodes = $xpath->query('//Contact');
        $cnt        = 0;
        foreach($cont_nodes as $node) {
            $conatacts[$cnt++] = $this->xmlToArray($respDOM->saveXml($node));
        }
        return $conatacts;
    }

    public function getMSLiveContacts($data_mapper = 'MSLiveDataMapper', $email_invite = false) {
        $this->mapped_contacts = array();
        $contacts = array();
        try {
            $contacts = $this->getAddressBook();
        }
        catch(Exception$e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        if(count($contacts) > 0) {
            if(!@include_once("api/ProfileIO/map/$data_mapper.class.php")) {
                $this->lastError = "Data mapper class \"$data_mapper\" not found";
                return false;
            }
            eval("$this->mapped_contacts = $data_mapper::processInData($contacts);");
        }
        if($email_invite) {
            return $this->getInviteMails($this->mapped_contacts);
        }
        else {
            return $this->mapped_contacts;
        }
    }

    /**
   * Return short contacts list with user name and email only.
   * 
   * 
   *   @param $server :                     (\p string)    Windows Live contacts server
   *   @param $port :                       (\p string)    port
   *
   * @return                (\p array)      array(array('UserName' => $fname, 'Email' => $email))
   *
   */
    public function getMSNLiveContacts() {
        if(count($this->profiles) > 0) {
            $cnt = 0;
            foreach($this->profiles as $profile) {
                $fname                 = (isset($profile['CONTACT']['PROFILES']['PERSONAL']['FIRSTNAME'])) ? $profile['CONTACT']['PROFILES']['PERSONAL']['FIRSTNAME'] : '';
                $lname                 = (isset($profile['CONTACT']['PROFILES']['PERSONAL']['LASTNAME'])) ? $profile['CONTACT']['PROFILES']['PERSONAL']['LASTNAME'] : '';
                $fullName              = $fname.' '.$lname;
                $mailAddr              = (isset($profile['CONTACT']['EMAILS']['EMAIL']['ADDRESS'])) ? $profile['CONTACT']['EMAILS']['EMAIL']['ADDRESS'] : '';
                $res[$cnt]['UserName'] = ($fullName != ' ') ? $fullName : __('no name');
                $res[$cnt++]['Email']  = (!empty($mailAddr)) ? $mailAddr : __('no email');
            }
            return $res;
        }
        return false;
    }

    private function xml2array($xmlData) {
        // parse the XML datastring
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $xmlData, $vals, $index);
        xml_parser_free($xml_parser);
        // convert the parsed data into a PHP datatype
        $res = array();
        $xarray[0] = &$res;
        foreach($vals as $xmlElem) {
            $key = $xmlElem['level']-1;
            switch($xmlElem['type']) {
                case 'open':
                    $name                = (array_key_exists('attributes', $xmlElem)) ? $xmlElem['attributes']['ID'] : $xmlElem['tag'];
                    $xarray[$key][$name] = array();
                    $xarray[$key+1]      = &$xarray[$key][$name];
                    break;
                case 'complete':
                    $xarray[$key][$xmlElem['tag']] = (isset($xmlElem['value'])) ? $xmlElem['value'] : '';
                    break;
            }
        }
        return($res);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function setError($msg) {
        $this->hasErrors = true;
        array_push($this->errors, $msg);
    }

    private function simpleXML_to_array($obj, &$input_arr = array()) {
        foreach(get_object_vars($obj) as $key => $var) {
            if(is_object($var)) {
                if(count((array) $var) == 0) {
                    $input_arr[$key] = null;
                }
                elseif(count((array) $var) == 1) {
                    $this->simpleXML_to_array($var, $input_arr[$key]);
                }
                else {
                    $this->simpleXML_to_array($var, $input_arr[$key][0]);
                }
            }
            elseif(is_array($var)) {
                foreach($var as $_k => $_v) {
                    $this->simpleXML_to_array($_v, $input_arr[$key][$_k]);
                }
            }
            else {
                $input_arr[$key] = $var;
            }
        }
    }

    private function map_items(&$element) {
        if(is_array($element)) {
            if(isset($element['item'])) {
                if((count($element['item']) == 1) && ($element['item'] != null)) {
                    $element = array(
                        $element['item'],
                    );
                }
                else {
                    $element = $element['item'];
                }
            }
            else {
                array_walk($element, array($this, 'map_items'));
            }
        }
    }

    public function xmlToArray($xml) {
        $res = array();
        $simple_xml = new SimpleXMLElement($xml);
        $this->simpleXML_to_array($simple_xml, $res);
        array_walk($res, array($this, 'map_items'));
        return $res;
    }
}
?>