<?php
/**
 * Table Definition for entry
 */
require_once 'DB/DataObject.php';

class DataObjects_Entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'entry';                           // table name
    var $categoryid;                      // int4(4)  
    var $entryid;                         // int4(4)  
    var $user_id;                         // varchar(-1)  
    var $date;                            // int4(4)  
    var $name;                            // varchar(-1)  
    var $content;                         // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
