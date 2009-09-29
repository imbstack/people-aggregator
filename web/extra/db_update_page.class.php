<?php
ini_set('max_execution_time', 1200);
ini_set('max_input_time', 1200);

if (!defined('PA_DISABLE_BUFFERING'))
{
    define('PA_DISABLE_BUFFERING', TRUE);
}

$here = dirname(__FILE__);
require_once "$here/../../../project_config.php";
require_once 'web/includes/functions/functions.php';
require_once 'api/Content/Content.php';
require_once 'db/Dal/Dal.php';
require_once 'db/Dal/DbUpdate.php';
require_once 'web/extra/net_extra.php';
class db_update_page
{
    private $xmlUpdAppConf;
    
    public static function check_quiet()
    {
        return defined('PEEPAGG_UPDATING');
    }
    
    function __construct()
    {
        // set to false to only update a single network (see: $this->update_single_network($network_name))
        $this->full_update = TRUE;  // set to true to run update without any output
        $this->quiet = db_update_page::check_quiet();
        $this->running_on_cli = !isset($_SERVER['REQUEST_METHOD']);
    }
    
    function do_updates()
    {
        // ALL DATABASE UPDATES GO IN HERE!
        // FOR EACH SQL STATEMENT YOU WANT TO EXECUTE, GIVE IT A 'KEY', THEN CALL:
        // $this->qup("key", "sql statement");
        // eg. $this->qup("new foobar table", "create table foobar (id int not null, primary key(id))");
        // YOU SHOULD NORMALLY PUT YOUR UPDATES AT THE *END* OF THIS FUNCTION.
        
        /** NOTE: KEY must be unique for each update query */
        
        /** EXAMPLE ADD NEW TABLE */
        /*
        $this->qup("new mc_feeds table",
                     "CREATE TABLE mc_feeds (
                     user_id int not null,
                     id int not null auto_increment,
                     primary key(user_id,id),
                     feed_url text not null,
                     feed_name varchar(255)
        )"); 
        */
        /** EXAMPLE ALTER TABLE */
        // $this->qup("add feed_description to mc_feeds", "ALTER TABLE mc_feeds ADD COLUMN feed_description TEXT");
        /** EXAMPLE INSERT INTO TABLE */
        // $this->qup("insert default data 1 for relation classifications", "INSERT INTO `relation_classifications` (`relation_type`, `relation_type_id`) VALUES ('acquaintance', '1');");
        /** EXAMPLE UPDATE TABLE */
        // $this->qup("changed id field in review-type movie", "UPDATE review_type SET review_id = 1 WHERE review_name = 'Movie'");
        // finally, run the 'safe' updates in net_extra.php.

        $child_role = array('id' => 9, 'name' => 'Child', 'description' => 'Role for family members with Child status', 'read_only' => 1, 'type' => 'group', 'tasks' => array(12, 13, 15, 16, 22, 30));
        $this->qup_all_networks("2009-09-28, by: Zoran Hron - adding Child role, ID: " . $child_role['id'],
                                "INSERT INTO {roles} (id, name, description, created, changed, read_only, type)
                                 VALUES (".$child_role['id'].", '".$child_role['name']."', '".$child_role['description']."', ".time().", ".time().", ".$child_role['read_only'].", '".$child_role['type']."')
                                 ON DUPLICATE KEY UPDATE name = '".$child_role['name']."', description = '".$child_role['description']."', read_only = ".$child_role['read_only'].", type = '".$child_role['type']."'"
                               );
       foreach($child_role['tasks'] as $task_id) {
         $this->qup_all_networks("2009-09-28, by: Zoran Hron - adding tasks/permissions for Child role. ID=".$child_role['id'].", task ID=" . $task_id,
                                 "INSERT IGNORE INTO {tasks_roles} (`task_id`, `role_id`) VALUES (".$task_id.", ".$child_role['id'].");"
                                );
       }

        $this->run_xml_updates();
        run_net_extra();
    }//__endof__ do_updates
    
    
    function write($s, $newline=TRUE)
    {
        echo "$s" , ($newline ? "\n" : '');
        flush();
    }
    
    function note($msg)
    {
        $this->li("* $msg *");
    }
    
    function li($msg)
    {
        if (!$this->quiet)
        {
            
            if ($this->running_on_cli)
            {
                $this->write("* $msg");
            }
            else
            {
                $this->write("<tr><td>$msg</td><td style='color: green'>OK</td></tr>");
            }
        }
    }
    
    function query($sql)
    {
        $this->li($sql);
        Dal::query($sql);
    }
    
    function is_applied($key, $network=NULL)
    {
        if (!$network)
        {
            $network = '';
        }
        
        $r = Dal::query_one('SELECT * FROM mc_db_status WHERE stmt_key=? AND network=?', Array($key, $network));
        return $r ? TRUE : FALSE;
    }
    
    // call qup for the main network plus all others
    function qup_all_networks($k, $sql_stmts)
    {
        if (!is_array($sql_stmts))
        {
            $sql_stmts = array($sql_stmts);
        }
        
        
        if (!$this->quiet)
        {
            $this->note("Applying '$k' update to all networks ...");
        }
        
        
        foreach ($sql_stmts as $sql)
        {
            
            if (!is_callable($sql) && (strpos($sql, '{') === FALSE))
            {
                die("ERROR: SQL '$sql' is to be applied to all networks, but contains no {bracketed} table names!");
            }
        }
        //    $sth = Dal::query("SELECT address FROM networks WHERE is_active=1");
        //    while (list($network_address) = Dal::row($sth)) {
        //      var_dump($this->networks);
        //    }
        $seen_default = FALSE;
        global $network_prefix;
        $prev_network_prefix = $network_prefix;
        $nets_done = $nets_updated = 0;
        $nets_total = count($this->networks);
        $last_prefix = '';// to work out spacing
        foreach ($this->networks as $network_prefix)
        {
         // set_time_limit(30);
            ++$nets_done;
            
            if ($network_prefix == 'default')
            {
                $network_prefix = '';// default network has network=='' in mc_db_status table
                $seen_default = TRUE;
            }
            
            
            if ($this->is_applied($k, $network_prefix))
            {
                continue;
            }
            
            
            if (!$this->quiet)
            {
                
                if ($this->running_on_cli)
                {
                    echo "\r";
                    $len_diff = strlen($last_prefix) - strlen($network_prefix);
                    $spacing = ($len_diff > 0) ? str_repeat(' ', $len_diff) : '';
                }
                else
                {
                    $spacing = '';
                }
                
                $this->write("* $nets_done/$nets_total [$network_prefix]$spacing", FALSE);
            }
            
            
            foreach ($sql_stmts as $sql)
            {
                
                if (is_callable($sql))
                {
                    $sql();
                }
                else
                {
                    $new_sql = Dal::validate_sql($sql, $network_prefix);
                    Dal::query($new_sql);
                }
            }
            
            Dal::query('INSERT INTO mc_db_status SET stmt_key=?, network=?', Array($k, $network_prefix));
            ++ $nets_updated;
            $last_prefix = $network_prefix;
        }
        
        $network_prefix = $prev_network_prefix;
        
        if (!$seen_default && $this->full_update)
        {
            $this->write("WARNING: applied change '$k' to all known networks, but the default network doesn't have an entry in the 'networks' table.  This means net_extra.php hasn't been run; something has gone wrong with the upgrade.");
        }
        
        
        if (!$this->quiet && $this->running_on_cli && $nets_updated)
        {
            echo "\n";
        }
    }
    
    function qup($k, $sql_or_func_array, $func_args = array())
    {
     // set_time_limit(30);
        if (!$this->full_update)
        {
         // we're only updating a single network - so don't do global changes
            return;
        }
        
        
        if ($this->is_applied($k, NULL))
        {
         // $this->note("$k already applied");
            return;
        }
        
        
        if (!is_array($sql_or_func_array))
        {
            $sql_or_func_array = array($sql_or_func_array);
        }
        
        
        foreach ($sql_or_func_array as $sql_or_func)
        {
            
            if (is_callable($sql_or_func))
            {   if(count($func_args) > 0) 
                {
                  $sql_or_func($func_args);
                } 
                else 
                {
                  $sql_or_func();
                }  
            }
            else
            {
                
                if (!$this->quiet)
                {
                    $this->note("applying patch $k" . ($this->running_on_cli ? (' (<a href="db_update.php?override='.htmlspecialchars($k).'">override</a>)') : ''));
                }
                
                $this->query($sql_or_func);
            }
        }
        
        Dal::query('INSERT INTO mc_db_status SET stmt_key=?', Array($k));
    }

    function run_xml_updates()
    {
        if (!$this->full_update)
        {
         // we're only updating a single network - so don't do global changes
            return;
        }
        
        if(!isset($xmlUpdAppConf)) {
           require_once PA::$core_dir . "/web/extra/XmlUpdate.class.php";
           $xmlUpdAppConf = new XmlUpdate(PA_PROJECT_CORE_DIR . APPLICATION_CONFIG_FILE, "application", $this);
        }
        $xmlUpdAppConf->run_updates();
    }


    function dump_schema()
    {
        echo '<table border="1">';
        $th = Dal::query('SHOW TABLES');
        
        while ($tr = Dal::row($th))
        {
            list($tname) = $tr;
            echo "<tr><td><b>TABLE: $tname</b></td></tr>";
            $ch = Dal::query("DESCRIBE $tname");
            
            while ($cr = Dal::row_assoc($ch))
            {
                echo '<tr>';
                
                foreach ($cr as $k => $v)
                {
                    echo "<td>$v</td>";
                }
                
                echo '</tr>';
            }
        }
        
        echo '</table>';
    }
    
    function main()
    {
        $this->db = Dal::get_connection();
        $this->note('Doing database update');
        // We use $this->db->getOne() below instead of Dal::query_one() as
        // the first time this script is run, the mc_db_status table will
        // not exist, which will fire an exception with Dal::query_one()
        // and break the installation.  Please don't change this to
        // Dal::query_one()!  -PP 2006-11-15
        $db_status = $this->db->getOne('SELECT * FROM mc_db_status LIMIT 1');
        
        if (!DB::isError($db_status))
        {
            $this->note('mc_db_status table in place');
        }
        else
        {
            $this->note('Creating mc_db_status table');
            $this->query('CREATE TABLE mc_db_status (stmt_key VARCHAR(255) NOT NULL, PRIMARY KEY(stmt_key))');
        }// add network column
        if (!$this->column_exists('mc_db_status', 'network'))
        {
            $this->query('ALTER TABLE mc_db_status ADD COLUMN network VARCHAR(50) NOT NULL DEFAULT \'\'');
            $this->query('ALTER TABLE mc_db_status DROP PRIMARY KEY');
            $this->query('ALTER TABLE mc_db_status ADD PRIMARY KEY(stmt_key, network)');
        }
        
        /* 'broken' col disabled for now - use $this->broken_networks instead.
        // make sure the network table has the 'broken' column before we get started
        if (!$this->column_exists("networks", "broken")) {
           Dal::query("ALTER TABLE networks ADD COLUMN broken BOOLEAN DEFAULT '0'");
        }*/

        // find networks which have their tables (i.e. skip over broken networks)
        $this->networks = DbUpdate::get_valid_networks();
        $override = @$_GET['override'];
        
        if (!empty($override))
        {
            
            try
            {
                Dal::query('INSERT INTO mc_db_status SET stmt_key=?', Array($override));
            }
            catch (PAException $e)
            {
                echo '<p>exception trying to override: ',$e->getMessage(),'</p>';
            }
        }
        
        $this->do_updates();
        
        if (!$this->quiet)
        {//        $this->dump_schema();
            $this->note('CORE db updates done.');
        }
    }
    
    // Update a single network.  This is used during network creation,
    // to ensure the network schema is up to date.
    function update_single_network($network)
    {
        // make sure the network exists
        if (!DbUpdate::is_network_valid($network))
        {
            throw new PAException(NETWORK_NOT_FOUND, "Cannot update $network as it does not exist in the database");
        }
        // we shouldn't need to care about this, but just in case, keep copies of updated vars
        $stored_full_update = $this->full_update;
        $this->full_update = FALSE;
        $stored_quiet = $this->quiet;
        $this->quiet = TRUE;                // update just the specified network
        $this->networks = array($network);
        $this->do_updates();                // pop old values
        $this->full_update = $stored_full_update;
        $this->quiet = $stored_quiet;
    }
    
    function table_exists($tablename)
    {
        $sql = 'SHOW TABLES LIKE \''.Dal::quote($tablename).'\'';
        $res = Dal::query($sql);
        
        while (list($tname) = Dal::row($res))
        {
            
            if ($tname == $tablename)
            {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    function column_exists($tablename, $column_name)
    {
        $sql = "SHOW COLUMNS FROM $tablename WHERE Field='$column_name'";
        $res = Dal::query($sql);
        
        if ($res->numrows() > 0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
?>