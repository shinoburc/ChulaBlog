<?php
/**
 * Table Definition for category
 */
require_once 'DB/DataObject.php';

class DataObjects_Category extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'category';                        // table name
    var $categoryid;                      // int4(4)  
    var $user_id;                         // varchar(-1)  
    var $date;                            // int4(4)  
    var $name;                            // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Category',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
