<?php
/*
    ChulaBlog
*/

$start = GetMicroSec();

require_once 'Class/ChulaPlugin.php';
require_once 'Class/HTTPQuery.php';
require_once 'Class/SystemInit.php';
require_once 'Class/Session.php';
require_once 'Class/InstanceManager.php';
require_once 'Class/Config.php';
require_once 'Class/NotificationCenter.php';
require_once 'Class/Notification.php';
require_once 'Class/ChulaDBIO.php';
require_once 'Plugins/ChulaView.php';

/* Instance Manager */
global $im;
$im = new InstanceManager();

/* Config */
$Config = $im->new_once("Config","Config/chulablog.ini");

/* Init database */
if(preg_match("/^sqlite/",$Config->get_value("DB_DataObject","database"))){
    $init_result = SystemInit::init();
    if($init_result != CHULABLOG_INIT_SUCCESS){
        if($init_result == CHULABLOG_DIRECTORY_OPEN_ERROR){
            echo "Cannot create directory. Please check Data directory permission.";
            exit();
        } else if($init_result == CHULABLOG_CREATE_DATABASE_ERROR){
            echo "Cannot create database. Please check Data directory permission or PHP database extention.";
            exit();
        }
    }
}

/* Session */
$Session = $im->new_once("Session",$Config->get_value("ChulaBlog","session_save_path"),$Config->get_value("ChulaBlog","session_life_time"));

/* set include path */
if($Config->get_value("ChulaBlog","use_embedded_pear")){
    $original_include_path = ini_get("include_path");
    ini_set("include_path",".:./Lib/pear:$original_include_path");
}

/* DB_DataObject */
require_once 'DB/DataObject.php';

/* singleton */
$NotificationCenter = $im->new_once("NotificationCenter");
$viewObj = $im->new_once("ChulaView");

/* DB Manager */
$options = &PEAR::getStaticProperty('DB_DataObject','options');
$options = $Config->get_value("DB_DataObject");

/* HTTPQuery */
$HTTPQuery = $im->new_once("HTTPQuery");
$target_id = $HTTPQuery->getId();
if(is_null($target_id) or empty($target_id)){
    $target_id = $Config->get_value("ChulaBlog","guest_user_id");
}

/* get role */
$UserInfo = DB_DataObject::factory('UserInfo');
$UserInfo->whereAdd("user_id = '" . $target_id . "'");
$UserInfo->find();
$UserInfo->fetch();
$role_id = $UserInfo->role_id;

/* load plugin list */
$Role = DB_DataObject::factory('Role');
$Role->whereAdd("role_id = '" . $role_id . "'");
$Role->find();
$Role->fetch();

$plugin_list = explode(",",$Role->plugin_list);

/* load plugins */
$plugins_dir = $Config->get_value("ChulaBlog","plugins_dir");
foreach($plugin_list as $target){
    if(file_exists($plugins_dir . "/" . $target . ".php")){
        require_once $plugins_dir . "/" . $target . ".php";
        if($target_id == $Session->getUserId()){
            $im->new_once("$target");
        } else {
            if(!call_user_func(array($target,"ownerOnly"))){
                $im->new_once("$target");
            }
        }
    }
}

/* View */
$viewObj->run();

$execution_time = GetMicroSec() - $start;
echo "<p>Execution time : $execution_time";

function GetMicroSec(){
    list( $msec, $sec ) = explode( " ", microtime() );
    return ( (float)$sec + (float)$msec );
}

?>
