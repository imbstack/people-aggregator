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
require_once 'PAExceptionCodes.inc';

/**
* Class PAException extends the base exception class for the PeopleAggregator
*
* @package PAException
* @author Gaurav Bhatnagar
*/
class PAException extends Exception {

    public $code;

    public $message;

    public function __construct($exceptionCode, $exceptionMessage) {
        parent::__construct($exceptionMessage, (int) $exceptionCode);
        $this->code = $exceptionCode;
        $this->message = $exceptionMessage;
    }
}
?>