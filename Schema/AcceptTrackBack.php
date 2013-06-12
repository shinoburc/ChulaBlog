<?php
/**
 * Table Definition for accepttrackback
 */
require_once 'DB/DataObject.php';

class DataObjects_AcceptTrackBack extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'accepttrackback';     // table name
    var $categoryid;                      // int4(4)  
    var $entryid;                         // int4(4)  
    var $accept;                          // int4(4)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_AcceptTrackBack',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
