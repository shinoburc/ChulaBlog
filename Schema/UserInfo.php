<?php
/**
 * Table Definition for userinfo
 */
require_once 'DB/DataObject.php';

class DataObjects_UserInfo extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'userinfo';                // table name
    var $user_id;                             // varchar(-1)  
    var $group_id;                            // varchar(-1)  
    var $name;                                // varchar(-1)  
    var $mail;                                // varchar(-1)  
    var $password;                            // varchar(-1)  
    var $role_id;                             // int4(4)

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_UserInfo',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
