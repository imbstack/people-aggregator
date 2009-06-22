<?php
    /*  This Class will send the ping to the central repository of  updates on all the instances of PeopleAggregator.*/
    
    // global var $path_prefix has been removed - please, use PA::$path static variable
    require_once dirname(__FILE__)."/../../config.inc";
    require_once "api/Logger/Logger.php";
    require_once "api/PAException/PAException.php";
    require_once "db/Dal/Dal.php";

    class PingClient {
    
        /**
        *   pa_url : URL of the instance of peopleaggregtor
        *   @var varchar
        */
        public $pa_url;
        
        /**
        *   pa_activity : Integer specifying the type of activity taking place at PA * *   instance. 
        *   @var integer
        */
        public $pa_activity;
        
        /**
        *   pa_user_url : URL of the public page of new user.
        *   @var varchar
        */
        public $pa_user_url;
        
        /**
        *   pa_user_name : Name of the new user.
        *   @var varchar
        */
        public $pa_user_name;
        
        /**
        *   pa_permalink : Permalink of the content posted.
        *   @var varchar
        */
        public $pa_permalink;
        
        /**
        *   pa_title : Title of the content posted.
        *   @var varchar
        */
        public $pa_title;
        
        /**
        *   pa_group_url : URL of the Homepage of the new group added.
        *   @var varchar
        */
        public $pa_group_url;
        
        /**
        *   pa_group_name : Name of the new group added.
        *   @var varchar
        */
        public $pa_group_name;
        
        /**
        *   pa_network_url : URL of the Homepage of the new Network created.
        *   @var varchar
        */
        public $pa_network_url;
        
        /**
        *   pa_network_name : Name of the new Network created.
        *   @var varchar
        */
        public $pa_network_name;
        
        /**
        *   pa_created : Timestamp of the activity.
        *   @var varchar
        */
        public $pa_created;
        
        /**
        *   Public function to set the class variables.
        *   @param $param_array : Associated array of the class variables.
        */
        public function set_params ($param_array) {
            Logger::log("Enter: function PingClient::set_params");
            foreach($param_array as $key => $value) {
                $this->$key = $value;
            }
            $this->pa_created = time();            
            Logger::log("Exit: function PingClient::set_params");
        }
        
        /**
        *   Public function to create a string of all the class variables.
        *   @param no parameters.
        */
        public function make_param_string () {
            Logger::log("Enter: function PingClient::make_param_string");
            $param_string = "";
            foreach($this as $key => $value) {
                if (!empty($param_string)) { $param_string.= "&"; }
                $param_string .= $key."=".urlencode($value);
            }
            Logger::log("Exit: function PingClient::make_param_string");
            return $param_string;
        }
        
        /**
        *   Public function to remake the URL of the ping server.
        *   @param no parameters.
        */
        public function remake_url () {
            Logger::log("Enter: function PingClient::remake_url");
            global $ping_server;
            $ping = array();
            $ping['url'] = $ping_server;
            $ping['url'] = preg_replace("@^http://@i", "", $ping['url']);
            $ping['host'] = substr($ping['url'], 0, strpos($ping['url'], "/"));
            $ping['uri'] = strstr($ping['url'], "/");
            Logger::log("Exit: function PingClient::remake_url");
            return $ping;
        }        
        
        /**
        *   Public function for ping the Ping Server.
        *   @param no parameters
        */
        
        public function send_ping () {
            Logger::log("Enter: function PingClient::send_ping");
            $request_data = $this->make_param_string ();
            $ping = $this->remake_url ();
            $content_length = strlen($request_data);
            
            $request_header = "POST ".$ping['uri']." HTTP/1.1\r\n";
            $request_header .= "Host: ".$ping['host']."\n". "User-Agent: send_ping\r\n";
            $request_header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $request_header .= "Content-Length: $content_length \r\n\r\n";
            $request_header .= "$request_data\r\n";
            
            $socket = fsockopen($ping['host'], 80, $errno, $errstr);

            if (!$socket) {
              $result["errno"] = $errno;
              $result["errstr"] = $errstr;
              Logger::log("Exit: function PingClient::send_ping");
              return $result;
            }
            
            fputs($socket, $request_header);
          
            fclose($socket);
            
            Logger::log("Exit: function PingClient::send_ping");
            return $result;
                                                            
        }
    }

?>