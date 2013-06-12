<?php
/**
 * Table Definition for role
 */
require_once 'DB/DataObject.php';

class DataObjects_Role extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'role';                          // table name
    var $role_id;                                   // varchar(-1)  
    var $role_name;                                 // varchar(-1)  
    var $user_permissions;                          // varchar(-1)  
    var $group_permissions;                         // varchar(-1)  
    var $plugin_list;                               // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Role',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
