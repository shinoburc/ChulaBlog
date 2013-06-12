<?php

    /*
     * $Id: EntryEditor.php,v 1.20 2005/05/05 14:09:44 shinobu Exp $
     *
     * ChulaBlog EntryEditor class.
     * 
     * state : EntryEditor::update
     *         EntryEditor::delete
     *         EntryEditor::insert
     */

    class EntryEditor extends ChulaPlugin{
        private $name = "EntryEditor";
        private static $static_name = "EntryEditor";

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);

            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::insert_link", $this->name,"display_form");
            switch($target){
                case "Category" :
                    $NotificationCenter->setObserver("ForEachEntryNotification", $this->name,"display_form");
                    break;
                /* my operation */
                case $this->name :
                    switch($command){
                        case "insert_form" :
                            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::insert_form", $this->name,"display_form");
                            break;
                        case "update_form" :
                            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::update_form", $this->name,"display_form");
                            break;
                        case "insert" :
                            $this->insert($_POST["categoryID"]);
                            break;
                        case "update" :
                            $this->update($HTTPQuery->getSd(2),$HTTPQuery->getSd(3));
                            break;
                        case "delete" :
                            $this->delete($HTTPQuery->getSd(2),$HTTPQuery->getSd(3));
                            break;
                        default :
                            break;
                    }
                    break;
                default :
                    break;
            }
        }


        public function display_form($notification){
            switch($notification->getMessage()){
                case "ChulaViewNotification::$this->name::insert_link" :
                    $this->insert_link();
                    break;
                case "ChulaViewNotification::$this->name::insert_form" :
                    $this->insert_form();
                    break;
                case "ChulaViewNotification::$this->name::update_form" :
                    $this->update_form();
                    break;
                case "ForEachEntryNotification" :
                    $args = $notification->getObject();
                    echo "&nbsp;";
                    $this->update_link($args[0],$args[1]);
                    echo "&nbsp;";
                    $this->delete_link($args[0],$args[1]);
                    break;
                default :
                    break;
            }
        }

        private function insert_link(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            print "<a href=\"" . $HTTPQuery->genURI($to,$this->name . "::insert_form") . "\" method=\"POST\">Entry_EDIT</a>";
        }
        private function update_link($categoryID,$entryID){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            print "<a href=\"" . $HTTPQuery->genURI($to,$this->name . "::update_form::" . $categoryID . "::" . $entryID) . "\" method=\"POST\">Edit</a>";
        }
        private function delete_link($categoryID,$entryID){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            print "<a href=\"" . $HTTPQuery->genURI($to,$this->name . "::delete::" . $categoryID . "::" . $entryID) . "\" method=\"POST\">Delete</a>";
        }

        private function insert_form(){
            global $im;
            $Session = $im->new_once("Session");

            $Notification = $im->new_once("Notification");
            $NotificationCenter = $im->new_once("NotificationCenter");
            
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            $Category = DB_DataObject::factory('Category');
            $Category->selectAdd("categoryID");
            $Category->selectAdd("name");
            $Category->whereAdd("user_id = '" . $Session->getUserId() . "'");
            $Category->orderBy("categoryID");
            $Category->find();

            print "<form action=\"" . $HTTPQuery->genURI($to,$this->name . "::insert") . "\" method=\"POST\">";
            print "<table><tr>";
            print "<td> Category </td>";
            print "<td>";
            print "<select name=\"categoryID\">";
            while($Category->fetch()){
                print "<option value=\"" . htmlspecialchars($Category->categoryID) . "\">" . htmlspecialchars($Category->name);
            }
            print "</select>";
            print "</td>";
            print "</tr>";
            print "<tr>";
            print "<td> Title </td>";
            print "<td><input type=\"text\" name=\"" . $this->name . "_name\"></td>";
            print "</tr>";
            print "<tr>";
            print "<td> Content </td>";
            print "<td><textarea rows=12 cols=60 name=\"" . $this->name . "_content\"></textarea></td>";
            print "</tr>";
            print "<tr>";
            print "<td colspan=\"2\">";
            $Notification->setMessage("EntryEditFormNotification");
            $NotificationCenter->postNotification($Notification);
            print "</td>";
            print "</tr>";
            print "<tr>";
            print "<td><input type=\"submit\"></td>";
            print "</tr>";
            print "</table>";
            print "</form>";
        }

        private function update_form(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();

            $Notification = $im->new_once("Notification");
            $NotificationCenter = $im->new_once("NotificationCenter");

            $Entry_select = DB_DataObject::factory('Entry');
            $Entry_select->whereAdd("categoryID = " . $HTTPQuery->getSd(2));
            $Entry_select->whereAdd("entryID = " . $HTTPQuery->getSd(3));
            $Entry_select->find();
            $Entry_select->fetch();

            $Category = DB_DataObject::factory('Category');
            $Category->selectAdd("categoryID");
            $Category->selectAdd("name");
            $Category->orderBy("categoryID");
            $Category->find();

            print "<form action=\"" . $HTTPQuery->genURI($to,$this->name . "::update::" . $HTTPQuery->getSd(2) . "::" . $HTTPQuery->getSd(3)) . "\" method=\"POST\">";
            print "<table><tr>";
            print "<td> Category </td>";
            print "<td>";
            print "<select name=\"categoryID\">";
            while($Category->fetch()){
                if($Category->categoryID == $Entry_select->categoryID){
                    print "<option value=\"" . htmlspecialchars($Category->categoryID) . "\" SELECTED>" . htmlspecialchars($Category->name);
                } else {
                    print "<option value=\"" . htmlspecialchars($Category->categoryID) . "\">" . htmlspecialchars($Category->name);
                }
            }
            print "</select>";
            print "</td>";
            print "</tr>";
            print "<tr>";
            print "<td> Title </td>";
            print "<td><input type=\"text\" name=\"" . $this->name . "_name\" value=\"" . $Entry_select->name . "\"></td>";
            print "</tr>";
            print "<tr>";
            print "<td> Content </td>";
            print "<td><textarea rows=12 cols=60 name=\"" . $this->name . "_content\">" . $Entry_select->content . "</textarea></td>";
            print "</tr>";
            print "<tr>";
            print "<td colspan=\"2\">";
            print "keep timestamp <input type=\"checkbox\" name=\"" . $this->name . "_keep_timestamp\" CHECKED>";
            print "<br>";
            $Notification->setMessage("EntryEditFormNotification");
            $NotificationCenter->postNotification($Notification);
            print "</td>";
            print "</tr>";
            print "<tr>";
            print "<td><input type=\"submit\"></td>";
            print "</table>";
            print "</form>";
        }

        private function insert($categoryID = NULL){
            global $im;
            $Session = $im->new_once("Session");
            $name = addslashes($_POST[$this->name . "_name"]);
            $content = addslashes($_POST[$this->name . "_content"]);
            $Entry = DB_DataObject::factory('Entry');
            $Entry->selectAdd("entryID");
            $Entry->whereAdd("categoryID = $categoryID");
            $Entry->orderBy("entryID DESC");
            $Entry->find();
            /* very dirty! */
            /* we have to implement sequense number or normalize entry table. */
            $Entry->fetch();
            if(isset($Entry->entryID)){
                $_entryID = $Entry->entryID + 1;
            } else {
                $_entryID = 0;
            }

            $Entry->categoryID = $categoryID;
            $Entry->entryID = $_entryID;
            $Entry->user_id = $Session->getUserId();
            $Entry->name = $name;
            $Entry->content = $content;
            $Entry->date = time();
            $Entry->insert();

            $Notification = $im->new_once("Notification");
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification->setMessage("EntryInsertNotification");
            $notificationObject = array($categoryID,$_entryID);
            $Notification->setObject($notificationObject);
            $NotificationCenter->postNotification($Notification);

            /* blank mainpane will be shown. */
            /* we have to implement session manager that keeps Serialized Data(sd). */
        }
        private function update($categoryID = NULL, $entryID = NULL){
            if(is_null($categoryID) or is_null($entryID)){
                return;
            }
            global $im;
            $Session = $im->new_once("Session");
            $HTTPQuery = $im->new_once("HTTPQuery");
            $name = $HTTPQuery->getSafePost($this->name . "_name");
            $content = $HTTPQuery->getSafePost($this->name . "_content");

            $keep_time_stamp = $HTTPQuery->getSafePost($this->name . "_keep_timestamp");

            if(!is_null($keep_time_stamp)){
                $Entry_select = DB_DataObject::factory('Entry');
                $Entry_select->whereAdd("categoryID = $categoryID");
                $Entry_select->whereAdd("entryID = $entryID");
                $Entry_select->whereAdd("user_id = '" . $Session->getUserId() . "'");
                $Entry_select->find();
                $Entry_select->fetch();
            }

            $Entry_delete = DB_DataObject::factory('Entry');
            $Entry_delete->categoryID = $categoryID;
            $Entry_delete->entryID = $entryID;
            $Entry_delete->user_id = $Session->getUserId();
            $Entry_delete->delete();

            $this->delete($categoryID,$entryID);

            $Entry_insert = DB_DataObject::factory('Entry');
            $Entry_insert->categoryID = $categoryID;
            $Entry_insert->entryID = $entryID;
            $Entry_insert->user_id = $Session->getUserId();
            $Entry_insert->name = $name;
            if(!is_null($keep_time_stamp)){
                $Entry_insert->date = $Entry_select->date;
            } else {
                $Entry_insert->date = time();
            }
            $Entry_insert->content = $content;
            $Entry_insert->insert();

            $Notification = $im->new_once("Notification");
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification->setMessage("EntryUpdateNotification");
            $notificationObject = array($categoryID,$entryID);
            $Notification->setObject($notificationObject);
            $NotificationCenter->postNotification($Notification);
        }

        private function delete($categoryID = NULL,$entryID = NULL){
            global $im;
            $Session = $im->new_once("Session");
            $Entry = DB_DataObject::factory('Entry');
            $Entry->categoryID = $categoryID;
            $Entry->entryID = $entryID;
            $Entry->user_id = $Session->getUserId();
            $Entry->delete();

            $Notification = $im->new_once("Notification");
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification->setMessage("EntryDeleteNotification");
            $notificationObject = array($categoryID,$entryID);
            $Notification->setObject($notificationObject);
            $NotificationCenter->postNotification($Notification);
        }

        public static function getState(){
            return array(
                         self::$static_name . "::insert_link"
                        ,self::$static_name . "::insert_form"
                        ,self::$static_name . "::update_form"
                        );
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::insert_link",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::insert_form",$user_id,"MAINPANE");
            ChulaDBIO::insertState(self::$static_name . "::update_form",$user_id,"MAINPANE");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::insert_link",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::insert_form",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::update_form",$user_id);
        }
    
        public static function description(){
            return "Entry editor";
        }

        /**
         * This plugin is for owner only.
         * @param  void
         * @return  boolean true
         */
        public static function ownerOnly(){
            return TRUE;
        }
    }
?>
