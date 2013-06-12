<?php

    /*
     * $Id: LatestEntry.php,v 1.12 2005/05/05 14:09:44 shinobu Exp $
     *
     * Latest Entry 
     * 
     * state : LatestEntry::latest_entry
     */

    class LatestEntry extends ChulaPlugin{
        private $name = "LatestEntry";
        private static $static_name = "LatestEntry";
        private $limit = 5;

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");

            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);

            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::latest_entry",$this->name,"display");
            //$NotificationCenter->setObserver("FunctionZoneNotification",$this->name,"function_zone");
        }

        public function display($notification){
            switch($notification->getMessage()){
                case "ChulaViewNotification::$this->name::latest_entry" :
                    global $im;
                    $HTTPQuery = $im->new_once("HTTPQuery");
                    $to = $HTTPQuery->getId();
                    $Entry = DB_DataObject::factory('Entry');
                    $Entry->selectAdd("categoryID,entryID,date,name");
                    $Entry->whereAdd("user_id = '". $to . "'");
                    $Entry->orderBy("date DESC");
                    $Entry->limit(0,$this->limit);
                    $Entry->find();

                    print "Latest Post Entry<table border=1 BGCOLOR=\"#ffcccc\" width=\"100%\">";
                    while($Entry->fetch()){
                        print "<tr>";
                        print "<td>" . date("Y/m/d H:i",$Entry->date) . "</td>";
                        print "<td><a href=\"" 
                                . $HTTPQuery->genURI($to,"Category::display::" . $Entry->categoryID . "::" . $Entry->entryID) 
                                . "\""
                                . " title=\""
                                . $Entry->name
                                . "\">" 
                                . htmlspecialchars(mb_substr($Entry->name,0,8,'euc-jp')) 
                                . "...</a></td>";
                        print "</tr>";
                    }
                    print "</table>";
                    break;
                default :
                    break;
            }
        }

        public function function_zone($notification){
        }

        public static function getState(){
            return array(self::$static_name . "::latest_entry");
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::latest_entry",$user_id,"MENU");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::latest_entry",$user_id);
        }

        public static function description(){
            return "List up latest post entry";
        }
    }
?>
