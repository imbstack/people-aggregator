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

include dirname(__FILE__)."/../config.inc";
$blogid = htmlspecialchars(@$_REQUEST['blogid']);
$xmlrpc_url = PA::$url."/api/xmlrpc";
header("Content-Type: application/xml");
echo "<";?>?xml version="1.0"<?php echo "?";?>>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
 <service>
  <engineName>PeopleAggregator</engineName>
  <engineLink>http://peopleaggregator.com/</engineLink>
  <homePageLink><?php echo PA::$url;?>/</homePageLink>
 <apis>
<?php if(0) {?>  <api name="Movable Type" blogID="<?php echo $blogid?>" preferred="true" apiLink="<?php echo $xmlrpc_url;?>"/><?php
}?>
  <api name="MetaWeblog" blogID="<?php echo $blogid?>" preferred="true" apiLink="<?php echo $xmlrpc_url;?>"/>
  <api name="Blogger" blogID="<?php echo $blogid?>" preferred="false" apiLink="<?php echo $xmlrpc_url;?>"/>
  </apis>
 </service>
</rsd>