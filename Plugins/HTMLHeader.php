<?php
/**
 * HTMLHeader Core
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: HTMLHeader.php,v 1.1 2005/02/21 16:33:55 shinobu Exp $
 */
class HTMLHeader extends ChulaPlugin{
    private $name = "HTMLHeader";
    private static $static_name = "HTMLHeader";

    function __construct(){
        global $im;
        $NotificationCenter = $im->new_once("NotificationCenter");
        $NotificationCenter->setObserver("ChulaViewNotification::$this->name", $this->name,"display");
    }

    public function display($notification){
        global $im;
        $Config = $im->new_once("Config");
        $Session = $im->new_once("Session");

        echo "<title>ChulaBlog</title>";
        echo "<META HTTP-EQUIV=\"Content-type\" CONTENT=\"text/html; charset=EUC-JP\">";

        $NotificationCenter = $im->new_once('NotificationCenter');
        $Notification = $im->new_new('Notification', 'HTMLHeaderNotification', NULL, $this->name, NULL);
        $NotificationCenter->postNotification($Notification);
    }

    public static function getState(){
      return array(self::$static_name);
    }

    public static function plugInTrigger($user_id){
        ChulaDBIO::insertState(self::$static_name,$user_id,"HEAD");
    }

    public static function plugOutTrigger($user_id){
        ChulaDBIO::deleteState(self::$static_name,$user_id);
    }

    public static function description(){
        return "set HTML header information";
    }
  }
?>
