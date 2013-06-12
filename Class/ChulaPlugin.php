<?php
/**
 * Chula Plugin root class
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: ChulaPlugin.php,v 1.7 2005/02/17 12:11:29 shinobu Exp $
 */
abstract class ChulaPlugin{
    private $name = "ChulaPlugin";
    private static $static_name = "ChulaPlugin";

    public function __construct(){
    }

    /**
     * Plugin description.
     * @param  void
     * @return  string  description string
     */
    public static function description(){
        return "ChulaPlugin";
    }

    /**
     * return states for ChulaView.
     * @param  void
     * @return  array  state array. state_name => description
     */
    public static function getState(){
        return array();
    }
    
    /**
     * Plugin can plug in.
     * @param  void
     * @return  boolean true if this plugin can plug in. default true.
     */
    public static function canPlugIn(){
        return TRUE;
    }

    /**
     * Plugin can plug out.
     * @param  void
     * @return  boolean true if this plugin can plug out. default true.
     */
    public static function canPlugOut(){
        return TRUE;
    }

    /**
     * Run from plugin configurator when plug out.
     * @param  void
     * @return  void
     */
    public static function plugOutTrigger($user_id){
    }

    /**
     * Run from plugin configurator when plug in.
     * @param  void
     * @return  void
     */
    public static function plugInTrigger($user_id){
    }

    /**
     * This plugin is for owner only.
     * @param  void
     * @return  boolean true if owner only. default false;
     */
    public static function ownerOnly(){
        return FALSE;
    }
}
?>
