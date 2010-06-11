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
require_once 'config/logger.conf';

class Logger {
  
  public static function log($message, $severityType = LOGGER_INFO, $to = LOGGER_FILE, $logDest='') {
    // from logger.conf
    if ( LOGGER_WRITE_OFF ) {
      return ;
    }
    global $logger_logTo, $logger_logFile, $logger_severity;
    if (!is_numeric($severityType)) throw new PAException(INVALID_ID, "Bad logger severity type: $severityType");
    if ($severityType >= $logger_severity) {
      if($to == LOGGER_FILE){
        $to = $logger_logTo;
      }
      if($logDest == ''){
        $logDest = $logger_logFile;
      }
      $logMessage = date("[m/d/y G:i:s] [");
  
      switch ($severityType) {
        case LOGGER_INFO:
          $logMessage .= " INFO: ";
          break;
        case LOGGER_WARNING:
          $logMessage .= " WARNING: ";
          break;
        case LOGGER_ERROR:
          $logMessage .= " ERROR: ";
          break;
      }
      $logMessage .= "]"."[ ".$message."]\n";
  
      // log messages only with severity type greater than
      if (@error_log($logMessage, $to, $logDest) == FALSE) {
	echo "<h1>Fatal error!</h1>";
	echo "<p><b>An error occurred while trying to write to the log</b>: <code>$php_errormsg</code>.</p>";
	echo "<p>Check that the log file (<code>$logDest</code>) is writeable by the web server.</p>";
	echo "<p>Here's the message I was trying to log:</p>";
	echo "<div style='border: solid 1px black; background-color: red; padding: 1em; font-weight: bold;'><code>$logMessage</code></div>";
	exit;
      }
    } 
    return;
  }
  
  /**
  * Public static function which will convert the associative array into a string. Will be used for dumping the array to logger file.
  * TODO: Need to provide support for multidimensional arrays
  */
  public static function assoc_array_string($params, $separator="\n") {
    $return = "\n";
    if (!empty($params)) {
      $return .= "Dumping array\n";
      foreach ($params as $key => $value) {
        $return .= $key.' = '.$value.$separator;
      }
    }
    return $return;
  }
  
  
}
?>
