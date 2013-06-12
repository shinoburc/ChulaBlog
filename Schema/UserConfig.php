<?php
/**
 * Table Definition for userconfig
 */
require_once 'DB/DataObject.php';

class DataObjects_UserConfig extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'userconfig';               // table name
    var $config_name;                          // varchar(-1)  
    var $user_id;                              // varchar(-1)  
    var $config_value;                         // varchar(-1)  
    var $optional_config_value;                // varchar(-1)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_UserConfig',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
