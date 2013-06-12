<?php

/**
 * Login (testing)
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: Login.php,v 1.19 2005/05/05 14:09:44 shinobu Exp $
 */
    class Login extends ChulaPlugin{
        private $name = "Login";
        private static $static_name = "Login";
        private $error_message = NULL;

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");

            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);

            switch($target){
                case $this->name :
                    switch($command){
                        case "login" :
                            if(isset($_POST[$this->name . "_login"])){
                                $this->login($_POST[$this->name . "_user_id"],$_POST[$this->name . "_password"]);
                            } else if(isset($_POST[$this->name . "_logout"])){
                                $this->logout();
                            }
                            break;
                        case "logout" :
                        default :
                            break;
                    }
                    break;
                default :
                    break;
            }

            $NotificationCenter->setObserver("FunctionZoneNotification",$this->name,"function_zone");
        }

        public function function_zone($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $to = $HTTPQuery->getId();
            echo "<noscript>";
            echo "<form action=\"" . $HTTPQuery->genURI($to,$this->name . "::login") . "\" method=\"POST\">";
            echo "&nbsp;ID<input type=text name=\"" . $this->name . "_user_id\">";
            echo "PASS<input type=password name=\"" . $this->name . "_password\">";
            echo "<input type=submit name=\"" . $this->name . "_login\" value=\"login\">";
            echo "<input type=submit name=\"" . $this->name . "_logout\" value=\"logout\">";
            if(!is_null($this->error_message)){
                    echo "&nbsp;$this->error_message";
            }
            echo "</form>";
            echo "</noscript>";
        }

        private function login($user_id,$password){
            global $im;
            $Session = $im->new_once("Session");
            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->whereAdd("user_id = '" . $user_id . "'");
            $UserInfo->find();
            $UserInfo->fetch();
            $md5_password = md5($user_id . $password);
            if(isset($UserInfo->password)){
                if($md5_password == $UserInfo->password){
                        $Session->setUserId($UserInfo->user_id);
                        global $im;
                        $NotificationCenter = $im->new_once("NotificationCenter");
                        $Notification = $im->new_new("Notification","LoginNotification",$UserInfo->user_id,$this->name,NULL);
                        $NotificationCenter->postNotification($Notification);
                        $HTTPQuery = $im->new_once("HTTPQuery");
                        $Session->reload();
                } else {
                        $this->error_message = "Invalid Password";
                }
            } else {
                        $this->error_message = "Invalid UserID";
            }
        }
        private function logout(){
            global $im;
            $Config = $im->new_once("Config");
            $Session = $im->new_once("Session");
            $_user_id = $Session->getUserId();
            $Session->setUserId($Config->get_value("ChulaBlog","guest_user_id"));
            
            $NotificationCenter = $im->new_once("NotificationCenter");
            $Notification = $im->new_new("Notification","LogoutNotification",$_user_id,$this->name,NULL);
            $NotificationCenter->postNotification($Notification);

            $Session->reload();
        }

        public static function getState(){
            return array();
        }

        public static function plugInTrigger($user_id){
        }

        public static function plugOutTrigger($user_id){
        }

        public static function description(){
            return "Login depend on FunctionZone";
        }
    }
?>
