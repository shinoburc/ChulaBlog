<?php
/**
 * Table Definition for entry
 */
require_once 'DB/DataObject.php';

class DataObjects_State extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'state';                           // table name
    var $state_name;                      // varchar(-1)  
    var $user_id;                         // varchar(-1)  
    var $position;                        // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_State',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
