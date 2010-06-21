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
* @class ServiceProtocolHandler
*
* @author     Zoran Hron <zhron@broadbandmechanics.com>
* @version    0.1.0
*
* @brief Abstract class ServiceProtocolHandler
*
*
**/
abstract class ServiceProtocolHandler {

    public $connected;

    public $lastError;

    private $connHandler;
    // connection handler
    private $dataMapper;
    // data converter (mapper)
    public $profile_sections = array(
        'general',
        'personal',
        'professional',
    );
    // Extending class should define these methods
    abstract public function connectService($auth_data = array());

    abstract public function disconnectService();

    abstract public function readData($params = array());

    abstract public function writeData($params = array(), $data);

    abstract public function synchronizeData($params = array());

    abstract public function getLastError();

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
