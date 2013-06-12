<?php

/* 
    ChulaView
*/
class ChulaView extends ChulaPlugin{
    private $name = "ChulaView";
    private static $static_name = "ChulaView";

    public static function getState(){
        return array(self::$static_name);
    }

    public static function plugInTrigger($user_id){
    }

    public static function plugOutTrigger($user_id){
    }

    public function run(){
        /* InstanceManager */
        global $im;

        /* NotificationCenter */
        $NotificationCenter = $im->new_once("NotificationCenter");

        /* template */
        $Config = $im->new_once("Config");
        $HTTPQuery = $im->new_once("HTTPQuery");

        if(is_null($HTTPQuery->getId())){
            $id = $Config->get_value("ChulaBlog","guest_user_id");
        } else {
            $id = $HTTPQuery->getId();
        }
        $template = ChulaDBIO::getTemplate("$id");

        $fh = fopen($template,"r");
        if( $fh==NULL ){
            print("File $template read mode error.");
            return;
        }
        $Notification = $im->new_new('Notification', NULL, NULL, $this->name, NULL);
        while( !feof($fh) ){
            $line = fgets($fh);
            if(ereg("##(.+)##",$line)){
                preg_match_all("|([^##]*)##([^##]*)##([^##]*)|",$line,$matches);
                if(is_array($matches[2])){
                    foreach($matches[2] as $key => $match){
                        print($matches[1][$key]);
                        $state_array = ChulaDBIO::getStateArray($match);
                        if(is_array($state_array)){
                            foreach($state_array as $state_name){
                                $Notification->setMessage("ChulaViewNotification::$state_name");
                                $NotificationCenter->postNotification($Notification);
                            }
                        }
                        print($matches[3][$key]);
                    }
                }
            } else {
                print($line);
            }
        }
    }

    public static function canPlugIn(){
        return FALSE;
    }

    public static function canPlugOut(){
        return FALSE;
    }

    private function state_array(){
        /* get $plugin_list somewhere */
        global $im;
        $state_array = array();

        /*
        foreach($plugin_list as $plugin_name){
            $plugin = $im->new_once($plugin_name);
            foreach($plugin->getState() as $plugin_state){
                $state_array["$plugin_state"] = $plugin_name;
            }
        }
        */
        return $state_array;
    }

    public static function description(){
        return "ChulaBlog View Class plugin";
    }
}

?>
