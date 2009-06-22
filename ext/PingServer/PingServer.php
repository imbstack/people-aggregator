<?php
    /*  This Class will send the ping to the central repository of  updates on all the instances of PeopleAggregator.*/
    
    include_once dirname(__FILE__)."/../../config.inc";
    // global var $path_prefix has been removed - please, use PA::$path static variable    
    require_once "api/Logger/Logger.php";
    require_once "api/PAException/PAException.php";
    require_once "db/Dal/Dal.php";

    class PingServer extends DomDocument {
    
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
            Logger::log("Enter: function PingServer::set_params");
            foreach($param_array as $key => $value) {
                $this->$key = $value;
            }
            Logger::log("Exit: function PingServer::set_params");
        }
        
        /**
        *   Public function to save the ping data from PA instances
        */
        public function save () {
            Logger::log("Enter: function PingServer::save");
            $sql = "INSERT INTO ping_server.ping_data (pa_url, pa_activity,  pa_user_url, pa_user_name, pa_permalink, pa_title, pa_group_url, pa_group_name, pa_network_url, pa_network_name, pa_created) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $data = array ($this->pa_url, $this->pa_activity, $this->pa_user_url, $this->pa_user_name, $this->pa_permalink, $this->pa_title, $this->pa_group_url, $this->pa_group_name, $this->pa_network_url, $this->pa_network_name, $this->pa_created);
            
            $res = Dal::query($sql, $data);
            Logger::log("Exit: function PingServer::save");
        }
        
        /**
        *   Public function to load the data from ping repository
        *   @param $condition : associative array of conditons to run the query
        */
        public function load ($condition = NULL) {
            Logger::log("Enter: function PingServer::load");
            
            $sql = "SELECT * FROM ping_server.ping_data WHERE 1";
            
            $data = array();
            if(count($condition) > 0) {
                foreach($condition as $key => $value)  {
                    $sql .= " AND $key = ?";
                    $data[] = $value;
                }
            }
            
            $sql .= " ORDER BY pa_created DESC";
            $res = Dal::query($sql, $data);
            $return = array(); 
            if ($res->numRows() > 0) { 
                while($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $return[] = $row;                  
                }
            }
            
            Logger::log("Exit: function PingServer::load");
            return $return;
        }
        
        public function generate_xml ($pa_activity = PA_ACTIVITY_USER_ADDED) {
            Logger::log("Enter: function PingServer::generate_xml");
            // global var $path_prefix has been removed - please, use PA::$path static variable
            $condition = array('pa_activity'=> $pa_activity);
            $data_array = $this->load ($condition);
            
            switch ($pa_activity) {
                case 2:
                    $activity = 'content';
                    $url = 'pa_permalink';
                    $name = 'pa_title';
                  break;
                  
                case 3:
                    $activity = 'group';
                    $url = 'pa_group_url';
                    $name = 'pa_group_name';
                   break;
                   
                 case 4:
                    $activity = 'network';
                    $url = 'pa_network_url';
                    $name = 'pa_network_name';
                   break;
                 
                 default:
                    $activity = 'user';
                    $url = 'pa_user_url';
                    $name = 'pa_user_name';
                   break;
            }
            
            $records = count($data_array);
            
            $xml = $this->createElement('PAUpdates');
            $xml->setAttribute( "version", "1.0");
            $xml->setAttribute( "updated", date("D, d M Y H:i:s"));
            $xml->setAttribute( "count", $records);
            
            
            if($records > 0) {
            
                for($counter = 0; $counter < $records; $counter++) {
                
                    $root = $this->createElement("PAUpdate");
                    $root->setAttribute( "activity", $activity);
                    $root->setAttribute( "url", $data_array[$counter]->$url);
                    $root->setAttribute( "name", $data_array[$counter]->$name);
                    
                    $xml->appendChild( $root );
                    
                 }
                
            }            
            
            
            $this->appendChild( $xml );            
            $content = $this->saveXml();
            
            // TO DO: need to change the file path.
            $filename = null;
            if(file_exists(PA::$project_dir . '/web/Ping/changes_user.xml')) {
               $filename = PA::$project_dir . '/web/Ping/changes_user.xml';
            } else if(file_exists(PA::$core_dir . '/web/Ping/changes_user.xml')) {
               $filename = PA::$core_dir . '/web/Ping/changes_user.xml';
            }
            
            // make file if it does not exists or remake it after one hour
            if(!$filename || (!(time() - filectime($filename)) < 3600)) {
                if(!$handle = fopen($filename, 'w')) {
                    $log = 'Unable to open '.$filename;
                    Logger::log("Exit: function PingServer::generate_xml\n$log");
                }
                
                if (fwrite($handle, $content) === FALSE) {
                    $log = $filename.' is not writable';
                    Logger::log("Exit: function PingServer::generate_xml\n$log");
                }
                
            }
            Logger::log("Exit: function PingServer::generate_xml");
        }
        
        public function return_xml ($pa_activity) {
            Logger::log("Enter: function PingServer::return_xml");
            $filename = "";
            switch ($pa_activity)  {
                case 1:
                    $filename = PA::$url .'/Ping/changes_user.xml';
                  break;
                case 2:
                    $filename = PA::$url .'/Ping/changes_content.xml';
                  break;
                case 3:
                    $filename = PA::$url .'/Ping/changes_group.xml';
                  break;
                case 4:
                    $filename = PA::$url .'/Ping/changes_network.xml';
                  break;
                default:
                    $filename = PA::$url .'/Ping/changes_user.xml';
            }
            
            Logger::log("Exit: function PingServer::return_xml");
            return $filename;
            /*if(!$handle = fopen($filename, 'r')) {
                $log = "Unable to open file ".$filename;
                Logger::log("Exit: function PingServer::return_xml\n$log");
            }            
            
            if(!$content = fread($handle, filesize($filename))) {
                $log = "Unable to read file ".$filename;
                Logger::log("Exit: function PingServer::return_xml\n$log");
            }*/
            
            //Logger::log("Exit: function PingServer::return_xml");
            //return $content;
        }
        
    }

?>