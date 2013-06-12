<?php
/**
 * FunctionZone Core
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: FunctionZone.php,v 1.9 2005/02/17 12:57:07 shinobu Exp $
 */
class FunctionZone extends ChulaPlugin{
    private $name = "FunctionZone";
    private static $static_name = "FunctionZone";

    function __construct( $_num=NULL ){
        global $im;
        $NotificationCenter = $im->new_once("NotificationCenter");
        $NotificationCenter->setObserver("ChulaViewNotification::$this->name", $this->name,"display");
    }

    public function display($notification){
        global $im;
        $Config = $im->new_once("Config");
        $Session = $im->new_once("Session");

        if($Session->getUserId() != $Config->get_value("ChulaBlog","guest_user_id")){
            echo "Your id : " . $Session->getUserId();
        }
        $NotificationCenter = $im->new_once('NotificationCenter');
        $Notification = $im->new_new('Notification', 'FunctionZoneNotification', NULL, $this->name, NULL);
        $NotificationCenter->postNotification($Notification);
    }

    public static function getState(){
      return array(self::$static_name);
    }

    public static function plugInTrigger($user_id){
        ChulaDBIO::insertState(self::$static_name,$user_id,"FUNCTIONZONE");
    }

    public static function plugOutTrigger($user_id){
        ChulaDBIO::deleteState(self::$static_name,$user_id);
    }

    public static function description(){
        return "Function zone";
    }

  }
?>
