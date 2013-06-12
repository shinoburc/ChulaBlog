<?php

/**
 * Login MD5
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: LoginMD5.php,v 1.4 2005/05/05 14:09:44 shinobu Exp $
 */
    class LoginMD5 extends ChulaPlugin{
        private $name = "LoginMD5";
        private static $static_name = "LoginMD5";
        private $error_message = NULL;

        /* Constructor */
        function __construct(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");

            $HTTPQuery = $im->new_once("HTTPQuery");
            $Session = $im->new_once("Session");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);

            $Session->setChallengeBefore($Session->getChallenge());
            $Session->setChallenge(md5(uniqid()));

            switch($target){
                case $this->name :
                    switch($command){
                        case "login" :
                            if(!is_null($HTTPQuery->getSafePost($this->name . "_login"))){
                                $this->login($HTTPQuery->getSafePost($this->name . "_user_id"),$HTTPQuery->getSafePost($this->name . "_response"));
                            } else if(!is_null($HTTPQuery->getSafePost($this->name . "_logout"))){
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

            $NotificationCenter->setObserver("HTMLHeaderNotification",$this->name,"html_header");
            $NotificationCenter->setObserver("FunctionZoneNotification",$this->name,"function_zone");
        }

        public function html_header($notification){
            ?>
            <script src="js/md5.js"></script>
            <script language="javascript">
            <!--
            function login(form){
                form.<?= $this->name ?>_response.value = MD5_hexhash(form.<?= $this->name ?>_challenge.value + MD5_hexhash(form.<?= $this->name ?>_user_id.value + form.<?= $this->name ?>_password.value));
                form.<?= $this->name ?>_challenge.value = "";
                form.<?= $this->name ?>_password.value = "";
                form.submit();
            }
            // -->
            </script>
            <?

        }

        public function function_zone($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $Session = $im->new_once("Session");

            $to = $HTTPQuery->getId();
            ?>
            <script language="javascript">
    document.write('<form action="?id=<?= $HTTPQuery->genURI($to,$this->name . "::login") ?>" name="<?= $this->name ?>" method="POST">');
            document.write('&nbsp;ID<input type=text name="<?= $this->name ?>_user_id">');
            document.write('PASS<input type=password name="<?= $this->name ?>_password">');
            document.write('<input type=submit name="<?= $this->name ?>_login" value="login" onClick="login(this.form)">');
            document.write('<input type=submit name="<?= $this->name ?>_logout" value="logout">');
            document.write('<input type=hidden name="<?= $this->name ?>_challenge" value="<?= $Session->getChallenge() ?>">');
            document.write('<input type=hidden name="<?= $this->name ?>_response" value="">');
            <? if(!is_null($this->error_message)){ ?>
            document.write('&nbsp;<?= $this->error_message ?>');
            <? } ?>
            document.write('</form>');
            </script>
            <?
        }

        private function login($user_id,$response){
            global $im;
            $Session = $im->new_once("Session");
            $UserInfo = DB_DataObject::factory('UserInfo');
            $UserInfo->whereAdd("user_id = '" . $user_id . "'");
            $UserInfo->find();
            $UserInfo->fetch();
            if(isset($UserInfo->password)){
                if((!is_null($Session->getChallengeBefore())) and !is_null($response) and ($response == md5($Session->getChallengeBefore() . $UserInfo->password))){
                        $Session->setUserId($UserInfo->user_id);
                        $NotificationCenter = $im->new_once("NotificationCenter");
                        $Notification = $im->new_new("Notification","LoginNotification",$UserInfo->user_id,$this->name,NULL);
                        $NotificationCenter->postNotification($Notification);
                        $HTTPQuery = $im->new_once("HTTPQuery");
                        $Session->reload();
                } else {
                        $this->error_message  = "Invalid Password ";
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
            return "Login using md5 and Chalenge & Response. require JavaScript. depend on FunctionZone and HTMLHeader.";
        }
    }
?>
