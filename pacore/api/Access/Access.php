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
* @package PA
* @copyright http://www.tekritisoftware.com
*/

/*
   * NOTE:  This is temp solution later it will be database driven
*/
class Access {

    /*
    * Constructor
    */
    function Access() {

        /*
        ARO and ACO-only View:
          AROs: Things requesting access
          ACOs: Things to control access on
        ARO, ACO and AXO View:
          AROs: Things requesting access
          ACOs: Actions that are requested
          AXOs: Things to control access on
        */
        // ARO value is currently the user type,
        // this changes to user id in proper implementation
        // No hierarchial inheritance so have to do that the long way
        $this->acl = array();
        // takes care of public group and member's only group
        $this->add_acl('action', 'read', 'users', 'member', 'group', 'member_only');
        $this->add_acl('action', 'read', 'users', 'all', 'group', 'public');
        // takes care of registeration type
        $this->add_acl('action', 'join', 'users', 'registered', 'group', 'open');
        $this->add_acl('action', 'join', 'users', 'registered', 'group', 'moderated');
        $this->add_acl('action', 'join', 'users', 'invited', 'group', 'invlite_only');
        // group contents
        $this->add_acl('action', 'add', 'users', 'member', 'group', 'contents');
        $this->add_acl('action', 'add', 'users', 'moderator', 'group', 'contents');
        $this->add_acl('action', 'add', 'users', 'owner', 'group', 'contents');
        $this->add_acl('action', 'edit', 'users', 'owner', 'group', 'contents');
        $this->add_acl('action', 'edit', 'users', 'moderator', 'group', 'contents');
        $this->add_acl('action', 'delete', 'users', 'owner', 'group', 'contents');
        $this->add_acl('action', 'delete', 'users', 'moderator', 'group', 'contents');
        // group moderate
        $this->add_acl('action', 'moderate', 'users', 'owner', 'group', 'users');
        $this->add_acl('action', 'moderate', 'users', 'owner', 'group', 'contents');
        $this->add_acl('action', 'moderate', 'users', 'moderator', 'group', 'users');
        $this->add_acl('action', 'moderate', 'users', 'moderator', 'group', 'contents');
        // group as a whole
        $this->add_acl('action', 'edit', 'users', 'owner', 'group', 'all');
        $this->add_acl('action', 'add', 'users', 'registered', 'group', 'all');
        //$this->_mos_add_acl( 'action', 'edit', 'users', 'owner', 'content', 'own' );
        $this->acl_count = count($this->acl);
    }

    /*
      This is a temporary function to allow 3PD's to add basic ACL checks for their
      modules and components.  NOTE: this information will be compiled in the db
      in future versions
    */
    function add_acl($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value = NULL, $axo_value = NULL) {
        $this->acl[] = array(
            $aco_section_value,
            $aco_value,
            $aro_section_value,
            $aro_value,
            $axo_section_value,
            $axo_value,
        );
        $this->acl_count = count($this->acl);
    }

    /*======================================================================*\
      Function:   acl_check()
      Purpose:  Function that wraps the actual acl_query() function.
              It is simply here to return TRUE/FALSE accordingly.
    \*======================================================================*/
    function acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value = NULL, $axo_value = NULL) {
        $acl_result = 0;
        //     echo '<pre>';print_r($this->acl);exit;
        for($i = 0; $i < $this->acl_count; $i++) {
            if(strcasecmp($aco_section_value, $this->acl[$i][0]) == 0) {
                if(strcasecmp($aco_value, $this->acl[$i][1]) == 0) {
                    if(strcasecmp($aro_section_value, $this->acl[$i][2]) == 0) {
                        if(strcasecmp($aro_value, $this->acl[$i][3]) == 0) {
                            if(strcasecmp($axo_section_value, $this->acl[$i][4]) == 0) {
                                if(strcasecmp($axo_value, $this->acl[$i][5]) == 0) {
                                    $acl_result = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $acl_result;
    }
}
?>
