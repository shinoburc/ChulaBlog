<?php

/**
 * Admin (testing)
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: Admin.php,v 1.14 2005/05/05 14:09:44 shinobu Exp $
 */
    class Admin extends ChulaPlugin{
        private $name = "Admin";
        private static $static_name = "Admin";

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);
            $key = $HTTPQuery->getSd(2);

            switch($target){
                case $this->name :
                    switch($command){
                        case "listup" :
                            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::display_form"
                                                            ,$this->name
                                                            ,"display_form");
                            break;
                        case "add_user" :
                            $this->add_user($_POST[$this->name . "_user_id"]
                                           ,$_POST[$this->name . "_password"]
                                           ,$_POST[$this->name . "_group_id"]
                                           ,$_POST[$this->name . "_name"]
                                           ,$_POST[$this->name . "_mail"]
                                           ,$_POST[$this->name . "_role"]);
                            /* display_form again */
                            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::display_form"
                                                            ,$this->name
                                                            ,"display_form");
                            break;
                        case "del_user" :
                            $this->del_user($_POST[$this->name . "_user_id"]);
                            /* display_form again */
                            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::display_form"
                                                            ,$this->name
                                                            ,"display_form");
                            break;
                        default :
                            break;
                    }
                    break;
                default :
                    break;
            }
            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::link"
                                            ,$this->name
                                            ,"display");
        }

        public function display($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            echo "<p><a href=\"" . $HTTPQuery->genURI($to,$this->name . "::listup") . "\">Admin Menu</a>";
        }

        public function display_form(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
?>
<p>add user
<p><form action="<?= $HTTPQuery->genURI($to,$this->name . "::add_user") ?>" method=POST>
<table>
    <tr>
        <td>UserID</td>
        <td><input type=text name="<?= $this->name ?>_user_id"></td>
    </tr>
    <tr>
        <td>Password</td>
        <td><input type=password name="<?= $this->name ?>_password"></td>
    </tr>
    <tr>
        <td>GroupID</td>
        <td><input type=text name="<?= $this->name ?>_group_id"></td>
    </tr>
    <tr>
        <td>Name</td>
        <td><input type=text name="<?= $this->name ?>_name"></td>
    </tr>
    <tr>
        <td>Mail</td>
        <td><input type=text name="<?= $this->name ?>_mail"></td>
    </tr>
    <tr>
        <td>Role</td>
        <td><select name="<?= $this->name ?>_role">
        <?
        $Role = DB_DataObject::factory('Role');
        $Role->selectAdd("role_id,role_name");
        $Role->find();
        while($Role->fetch()){
            echo "<option value=\"$Role->role_id\">$Role->role_name";
        }
        ?>
        </select>
        </td>
    </tr>
    <tr>
        <td colspan=2><input type=submit value="add"></td>
    </tr>
</table>
</form>
<?

            $Config = $im->new_once("Config");
            /* Cannot delete user */
            $super_admin_user_id = $Config->get_value("ChulaBlog","super_admin_user_id");
            $guest_user_id = $Config->get_value("ChulaBlog","guest_user_id");

            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->selectAdd("user_id");
            $UserInfo->whereAdd("user_id != '" . $super_admin_user_id . "'");
            $UserInfo->whereAdd("user_id != '" . $guest_user_id . "'","AND");
            $UserInfo->find();
            echo "<p>del user";
            echo "<p><form action=\"" . $HTTPQuery->genURI($to,$this->name . "::del_user") . "\" method=POST>";
            echo "UserID : <select name=\"" . $this->name . "_user_id\">";
            while($UserInfo->fetch()){
                echo "<option value=\"" . $UserInfo->user_id . "\">" . $UserInfo->user_id;
            }
            echo "</select>";
            echo "<input type=submit value=\"del\">";
            echo "</form>";

            /* NotificationCenter */
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification = $im->new_new('Notification', 'AdminNotification', NULL, $this->name, NULL);
            $NotificationCenter->postNotification($Notification);
        }

        private function add_user($user_id,$password,$group_id,$name,$mail,$role){
            /* if have admin flag */
            /*
            if(!$this->have_admin_flag($_SESSION["user_id"])){
                return;
            }
            */
            /* have to validation! */
            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->user_id = addslashes($user_id);
            $UserInfo->group_id = addslashes($group_id);
            $UserInfo->name = addslashes($name);
            $UserInfo->mail = addslashes($mail);
            $UserInfo->password = md5($user_id . $password);
            $UserInfo->role_id = addslashes($role);
            $UserInfo->insert();

            global $im;
            /* AddUserNotification */
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification = $im->new_new('Notification', 'AddUserNotification', $user_id, $this->name, NULL);
            $NotificationCenter->postNotification($Notification);
        }

        private function del_user($user_id){
            global $im;
            $Config = $im->new_once("Config");
            $Session = $im->new_once("Session");

            /* Cannot delete user */
            $super_admin_user_id = $Config->get_value("ChulaBlog","super_admin_user_id");
            $guest_user_id = $Config->get_value("ChulaBlog","guest_user_id");
            if(($user_id == $super_admin_user_id) or ($user_id == $guest_user_id)){
                return;
            }

            if(!$this->have_admin_flag($Session->getUserId())){
                return;
            }
            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->user_id = addslashes($user_id);
            $UserInfo->delete();

            /* DelUserNotification */
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification = $im->new_new('Notification', 'DelUserNotification', $user_id, $this->name, NULL);
            $NotificationCenter->postNotification($Notification);
        }

        private function have_admin_flag($user_id = NULL){
            /* XXX : role */
            if(is_null($user_id)){
                return FALSE;
            }
            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->whereAdd("user_id = '" . $user_id . "'");
            $UserInfo->find();
            $UserInfo->fetch();
            if(!isset($UserInfo->user_id)){
                return FALSE;
            }
            if(!$UserInfo->admin_flag){
                return FALSE;
            } else {
                return TRUE;
            }
        }
        public static function description(){
            return "Admin menu";
        }

        public static function getState(){
            return array(
                        self::$static_name . "::link"
                        ,self::$static_name . "::display_form"
                        );
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::link",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::display_form",$user_id,"MAINPANE");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::link",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::display_form",$user_id);
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
