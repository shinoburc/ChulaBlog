<?php

    /*
     * $Id: UserListUp.php,v 1.9 2005/05/05 14:09:44 shinobu Exp $
     *
     * Latest Entry 
     * 
     * state : UserListUp::latest_entry
     */

    class UserListUp extends ChulaPlugin{
        private $name = "UserListUp";
        private static $static_name = "UserListUp";

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");

            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);
            $NotificationCenter->setObserver("ChulaViewNotification::$this->name",$this->name,"display");
        }

        public function display($notification){
            switch($notification->getMessage()){
                case "ChulaViewNotification::$this->name" :
                    $UserInfo = DB_DataObject::factory('UserInfo');
                    $UserInfo->selectAdd("user_id");
                    $UserInfo->find();

                    print "<p>UserListUp<table border=1 BGCOLOR=\"#ffcccc\" width=\"100%\">";
                    global $im;
                    $HTTPQuery = $im->new_once("HTTPQuery");
                    while($UserInfo->fetch()){
                        print "<tr>";
                        print "<td><a href=\"" . $HTTPQuery->genURI($UserInfo->user_id) . "\">" . $UserInfo->user_id . "</a></td>";
                        print "</tr>";
                    }
                    print "</table>";
                    break;
                default :
                    break;
            }
        }

        public static function getState(){
            return array(self::$static_name);
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name,$user_id,"MENU");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name,$user_id);
        }

        public static function description(){
            return "ChulaBlogger page list up";
        }
    }
?>
