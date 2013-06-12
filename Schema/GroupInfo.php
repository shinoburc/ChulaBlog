<?php
/**
 * Table Definition for groupinfo
 */
require_once 'DB/DataObject.php';

class DataObjects_GroupInfo extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'groupinfo';               // table name
    var $group_id;                            // varchar(-1)  
    var $name;                                // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_GroupInfo',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
