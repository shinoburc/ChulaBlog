<?php

    /*
     * $Id: CategoryEditor.php,v 1.15 2005/05/05 14:09:44 shinobu Exp $
     *
     * ChulaBlog CategoryEditor class.
     * 
     * state : CategoryEditor::update
     *         CategoryEditor::delete
     *         CategoryEditor::insert
     */

    class CategoryEditor extends ChulaPlugin{
        private $name = "CategoryEditor";
        private static $static_name = "CategoryEditor";

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);

            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::insert_link", $this->name,"display_form");
            switch($target){
                case $this->name :
                    switch($command){
                        case "insert_form" :
                        case "update_form" :
                            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::insert_form", $this->name,"display_form");
                            break;
                        case "insert" :
                                $this->insert($_POST[$this->name . "_name"]);
                            break;
                        case "update" :
                            $this->update($ary[2],$ary[3]);
                            break;
                        case "delete" :
                            $this->delete($ary[2],$ary[3]);
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
                default :
                    break;
            }
        }

        private function insert_link(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            print "<a href=\"" . $HTTPQuery->genURI($to,$this->name . "::insert_form") . "\" method=\"POST\">Category_EDIT</a>";
        }

        private function insert_form(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            print "<form action=\"" . $HTTPQuery->genURI($to,$this->name . "::insert") . "\" method=\"POST\">";
            print "<table><tr>";
            print "<td> Category name </td>";
            print "<td>";
            print "<input type=\"text\" name=\"" . $this->name . "_name\">";
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
            print "<form action=\"" . $HTTPQuery->genURI($to,$this->name . "::insert") . "\" method=\"POST\">";
            print "<table><tr>";
            print "<td> Category name </td>";
            print "<td>";
            print "<input type=\"text\" name=\"" . $this->name . "_name\">";
            print "</td>";
            print "</tr>";
            print "<tr>";
            print "<td><input type=\"submit\"></td>";
            print "</tr>";
            print "</table>";
            print "</form>";
        }

        private function insert($name = NULL){
            global $im;
            $Session = $im->new_once("Session");

            $name = addslashes($name);
            $Category = DB_DataObject::factory('Category');
            $Category->selectAdd("categoryID");
            $Category->orderBy("categoryID DESC");
            $Category->find();
            $Category->fetch();
            if(isset($Category->categoryID)){
                $_categoryID = $Category->categoryID + 1;
            } else {
                $_categoryID = 0;
            }

            $Category->categoryID = $_categoryID;
            $Category->user_id = $Session->getUserId();
            $Category->name = $name;
            $Category->date = time();
            $Category->insert();
        }
        private function update($categoryID = NULL){
        }

        private function delete($categoryID = NULL){
        }

        public static function getState(){
            return array(
                         self::$static_name . "::insert_link"
                        ,self::$static_name . "::insert_form"
                        );
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::insert_link",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::insert_form",$user_id,"MAINPANE");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::insert_link",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::insert_form",$user_id);
        }

        public static function description(){
            return "Category editor";
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
