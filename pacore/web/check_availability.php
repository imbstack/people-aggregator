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
/*
 * Project:     PeopleAggregator: a social network developement platform
 * File:        check_availability.php, ajax file to find network availablity
 * Author:      tekritisoftware
 * Version:     1.1
 * Description: It is used to find if the given network address can be used
 *              for creation of the network
 * The lastest version of PeopleAggregator can be obtained from:
 * http://peopleaggregator.org
 * For questions, help, comments, discussion, etc. please visit 
 * http://wiki.peopleaggregator.org/index.php
 *
 */
$login_required = TRUE;
include_once("web/includes/page.php");
global $invalid_network_address;
require_once "api/Validation/Validation.php";
if(!empty($_GET['check_address'])) {
    $address = trim($_GET['check_address']);
    if(strlen($address) < 3) {
        print '<span class="required">Network address can not have less than 3 characters.</span>';
    }
    elseif(strlen($address) > 10) {
        print '<span class="required">Network address can not have more than 10 characters.</span>';
    }
    elseif(!Validation::validate_alpha_numeric($address)) {
        print '<span class="required">Network address can not contain non alpha numeric characters.</span>';
    }
    elseif(in_array($address, $invalid_network_address)) {
        print '<span class="required"> Special subdomain names like ftp, mail, smtp, pop etc. and Special keywords are not allowed in network address.</span>';
    }
    elseif(Network::check_already($address)) {
        print '<span class="required">Network address '.stripslashes($address).' is not available.</span>';
    }
    else {
        print '<span class="required">Network address '.stripslashes($address).' is available.</span>';
    }
}
else {
    print '<span class="required">Please enter the network address.</span>';
}
?>