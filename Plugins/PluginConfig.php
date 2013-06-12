<?php
/**
 * Plugin Config 
 * @package ChulaBlog
 * @subpackage ChulaPlugin
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: PluginConfig.php,v 1.18 2005/05/05 14:09:44 shinobu Exp $
 */
class PluginConfig extends ChulaPlugin{
    private $name = "PluginConfig";
    private static $static_name = "PluginConfig";

    function __construct(){
        global $im;
        $NotificationCenter = $im->new_once("NotificationCenter");
        $NotificationCenter->setObserver("AdminNotification", $this->name,"display");

        $HTTPQuery = $im->new_once("HTTPQuery");
        $target = $HTTPQuery->getSd(0);
        $command = $HTTPQuery->getSd(1);
        $key = $HTTPQuery->getSd(2);

        switch($target){
            case $this->name :
                if(!is_null($key)){
                    $this->update_plugin_list($key);
                }
                switch($command){
                    case "config" :
                        $NotificationCenter->setObserver("ChulaViewNotification::Admin::display_form", $this->name,"config");
                        break;
                    default :
                        break;
                    }
                break;
            default :
                break;
       }
    }

    public function display($notification){
        global $im;
        $Session = $im->new_once("Session");
        $HTTPQuery = $im->new_once("HTTPQuery");
        echo "<p><a href=\"" . $HTTPQuery->genURI($Session->getUserId(),$this->name . "::config") . "\">Plugin Config</a>";

        $NotificationCenter = $im->new_once('NotificationCenter');
        $Notification = $im->new_new('Notification', $this->name . 'Notification', NULL, $this->name, NULL);
        $NotificationCenter->postNotification($Notification);
    }

    public function config($notification){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");

        $Session = $im->new_once("Session");
        $Config = $im->new_once("Config");

        $plugin_list = $this->get_plugin_list();

        /* list up not loading plugin */
        /* list up if in plugins_dir & *.php & not loading & canPlugIn */
        $plugins_dir = $Config->get_value("ChulaBlog","plugins_dir");
        $exists_plugins = scandir($plugins_dir);
        echo "<form action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config::add") . "\" method=POST>";
        echo "<table border=1>";
        echo "<tr>";
        echo "<td colspan=\"4\">Not Loading Plugins</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>plugin name</td><td>description</td><td>owner only</td><td></td>";
        echo "</tr>";
        if(is_array($exists_plugins)){
            foreach($exists_plugins as $file){
                if(preg_match("/.php$/i",$file)){
                    $plugin_name = preg_replace("/.php$/i","",$file);
                    if(!in_array($plugin_name,$plugin_list)){
                        require_once($plugins_dir . "/" . $file);
                        $can_plug_in = call_user_func(array($plugin_name,"canPlugIn"));
                        $owner_only = call_user_func(array($plugin_name,"ownerOnly"));
                        if($can_plug_in){
                            echo "<tr>";
                            echo "<td>$plugin_name</td>";
                            echo "<td>" . call_user_func(array($plugin_name,"description")) . "</td>";
                            if($owner_only){
                                echo "<td>TRUE</td>";
                            } else {
                                echo "<td>FALSE</td>";
                            }
                            echo "<td><input type=\"submit\" name=\"" . $plugin_name . "\" value=\"add\"></td>";
                            echo "</tr>";
                        }
                    }
                }
            }
        }
        echo "</table>";
        echo "</form>";

        /* list up loading plugin */
        /* list up if loading & canPlugOut */
        echo "<form action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config::del") . "\" method=POST>";
        echo "<table border=1>";
        echo "<tr>";
        echo "<td colspan=3>Loading Plugins</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>plugin name</td><td>description</td><td>owner only</td><td></td>";
        echo "</tr>";
        foreach($plugin_list as $plugin){
            require_once($plugins_dir . "/" . $plugin . ".php");
            $can_plug_out = call_user_func(array($plugin,"canPlugOut"));
            $owner_only = call_user_func(array($plugin,"ownerOnly"));
            echo "<tr>";
            echo "<td>$plugin</td>";
            echo "<td>" . call_user_func(array($plugin,"description")) . "</td>";
            if($owner_only){
                echo "<td>TRUE</td>";
            } else {
                echo "<td>FALSE</td>";
            }
            if($can_plug_out){
                echo "<td><input type=\"submit\" name=\"" . $plugin . "\" value=\"del\"></td>";
            } else {
                echo "<td>Don't delete</td>";
            }
                echo "</tr>";
        }
        echo "</table>";
        echo "</form>";
    }

    private function update_plugin_list($target){
        if($target = array_search("add",$_POST)){
            $this->add_plugin($target);
        }
        if($target = array_search("del",$_POST)){
            $this->del_plugin($target);
        }
        global $im;
        $Session = $im->new_once("Session");
        $HTTPQuery = $im->new_once("HTTPQuery");

        $Session->reload($HTTPQuery->genURI($HTTPQuery->getId(),$HTTPQuery->getSd(0) . "::" . $HTTPQuery->getSd(1)));
    }

    private function add_plugin($target){
        global $im;
        $Session = $im->new_once("Session");
        $Config = $im->new_once("Config");

        $UserInfo = DB_DataObject::factory('UserInfo');
        $UserInfo->selectAdd("role_id");
        $UserInfo->whereAdd("user_id = '" . $Session->getUserId() . "'");
        $UserInfo->find();
        $UserInfo->fetch();

        $Role = DB_DataObject::factory('Role');
        $Role->whereAdd("role_id = " . $UserInfo->role_id);
        $Role->find();
        $Role->fetch();

        $plugin_list = $Role->plugin_list;
        $plugin_list_array = explode(",",$plugin_list);
        if(!in_array($target,$plugin_list_array)){
            /* update */
            $role_id = $Role->role_id;
            $role_name = $Role->role_name;
            $user_permissions = $Role->user_permissions;
            $group_permissions = $Role->group_permissions;

            $Role_delete = DB_DataObject::factory('Role');
            $Role_delete->role_id = $role_id;
            $Role_delete->plugin_list = $plugin_list;
            $Role_delete->delete();

            $Role_insert = DB_DataObject::factory('Role');
            $Role_insert->role_id = $role_id;
            $Role_insert->role_name = $role_name;
            $Role_insert->user_permissions = $user_permissions;
            $Role_insert->group_permissions = $group_permissions;
            $Role_insert->plugin_list = $plugin_list . "," . $target;
            $Role_insert->insert();

            require_once($Config->get_value("ChulaBlog","plugins_dir") . "/" . $target . ".php");
            call_user_func_array(array($target,"plugInTrigger"),$Session->getUserId());
        }
    }

    private function del_plugin($target){
        global $im;
        $Session = $im->new_once("Session");
        $Config = $im->new_once("Config");

        $UserInfo = DB_DataObject::factory('UserInfo');
        $UserInfo->selectAdd("role_id");
        $UserInfo->whereAdd("user_id = '" . $Session->getUserId() . "'");
        $UserInfo->find();
        $UserInfo->fetch();

        $Role = DB_DataObject::factory('Role');
        $Role->whereAdd("role_id = " . $UserInfo->role_id);
        $Role->find();
        $Role->fetch();

        $plugin_list_array = explode(",",$Role->plugin_list);
        $pos = array_search($target,$plugin_list_array);
        if(!is_null($pos)){
            unset($plugin_list_array[$pos]);

            /* update */
            $role_id = $Role->role_id;
            $role_name = $Role->role_name;
            $user_permissions = $Role->user_permissions;
            $group_permissions = $Role->group_permissions;

            $Role_delete = DB_DataObject::factory('Role');
            $Role_delete->role_id = $role_id;
            $Role_delete->delete();

            $Role_insert = DB_DataObject::factory('Role');
            $Role_insert->role_id = $role_id;
            $Role_insert->role_name = $role_name;
            $Role_insert->user_permissions = $user_permissions;
            $Role_insert->group_permissions = $group_permissions;
            $Role_insert->plugin_list = implode(",",$plugin_list_array);
            $Role_insert->insert();

            require_once($Config->get_value("ChulaBlog","plugins_dir") . "/" . $target . ".php");
            call_user_func_array(array($target,"plugOutTrigger"),$Session->getUserId());
        }
    }

    private function get_plugin_list(){
        global $im;
        $Session = $im->new_once("Session");
        $Config = $im->new_once("Config");

        $UserInfo = DB_DataObject::factory('UserInfo');
        $UserInfo->selectAdd("role_id");
        $UserInfo->whereAdd("user_id = '" . $Session->getUserId() . "'");
        $UserInfo->find();
        $UserInfo->fetch();

        $Role = DB_DataObject::factory('Role');
        $Role->whereAdd("role_id = " . $UserInfo->role_id);
        $Role->find();
        $Role->fetch();

        return explode(",",$Role->plugin_list);
    }

    public static function canPlugIn(){
        return TRUE;
    }

    public static function canPlugOut(){
        return FALSE;
    }

    public static function getState(){
      return array();
    }

    public static function description(){
        return "ChulaPlugin configurator";
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
