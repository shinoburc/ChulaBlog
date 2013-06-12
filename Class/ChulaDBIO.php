<?php
/**
 * ChulaBlog Database IO
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: ChulaDBIO.php,v 1.6 2005/03/01 14:12:28 shinobu Exp $
 */
class ChulaDBIO{
    private $name = "ChulaDBIO";
    private static $static_name = "ChulaDBIO";

    /**
     * insert into state table
     * @param  string   state name
     * @param  string   user id
     * @param  string   position
     * @return  void
     */
    public static function insertState($state_name,$user_id,$position){
        /* if $user_id exists */
        /* XXX */

        /* if $State->state_name and $State->user_id exists */
        /* XXX */
        $State = DB_DataObject::factory('State');
        $State->state_name = $state_name;
        $State->user_id = $user_id;
        $State->position = $position;
        $State->insert();
    }

    /**
     * delete from state table
     * @param  string   state name
     * @param  string   user id
     * @return  void
     */
    public static function deleteState($state_name,$user_id){
        $State = DB_DataObject::factory('State');
        $State->state_name = $state_name;
        $State->user_id = $user_id;
        $State->delete();
    }

    /**
     * get state array to display position
     * @param  string   position name
     * @return  array   state array to display $position
     */
    public static function getStateArray($position){
        $State = DB_DataObject::factory('State');
        $State->selectAdd("state_name");
        $State->whereAdd("position = '" . $position . "'");
        $State->find();
        $state_array = array();
        while($State->fetch()){
            $state_array[] = $State->state_name;
        }
        return $state_array;
    }

    /**
     * get template file for $user_id
     * @param  string   user_id
     * @return  string  template file 
     */
    public static function getTemplate($user_id){
        global $im;

        /* template */
        $Config = $im->new_once("Config");

        $template_name = ChulaDBIO::getTemplateName($user_id);
        $templates_dir = $Config->get_value("ChulaBlog","templates_dir");
        if(is_null($template_name)){
            return $templates_dir . "/" . "default.tpl";
        } else {
            return $templates_dir . "/" . $template_name;
        }
    }

    /**
     * get template name for $user_id
     * @param  string   user_id
     * @return  string  template name
     */
    public static function getTemplateName($user_id){
        global $im;

        /* template */
        $Config = $im->new_once("Config");

        $Template = DB_DataObject::factory('UserConfig');
        $Template->whereAdd("config_name = 'template'");
        $Template->whereAdd("user_id = '" . $user_id . "'","AND");
        $Template->find();
        $Template->fetch();

        return $Template->config_value;
    }

    /**
     * set template file for $user_id
     * @param  string   user_id
     * @param  string   template file name
     * @return void
     */
    public static function setTemplate($user_id,$template_file){
        global $im;
        /* need transaction */

        /* update template */
        $Config = $im->new_once("Config");
        $templates_dir = $Config->get_value("ChulaBlog","templates_dir");

        if(!file_exists($templates_dir . "/" . $template_file)){
            return;
        }
        $Template_delete = DB_DataObject::factory('UserConfig');
        $Template_delete->config_name = "template";
        $Template_delete->user_id = $user_id;
        $Template_delete->delete();

        $Template_insert = DB_DataObject::factory('UserConfig');
        $Template_insert->config_name = "template";
        $Template_insert->user_id = $user_id;
        $Template_insert->config_value = $template_file;
        $Template_insert->optionale_config_value = $user_id;
        $Template_insert->insert();
    }

    /**
     * insert accepttrackback
     * @param  integer  category id
     * @param  integer  entry id
     * @param  integer  1 if accept. 0 if not accept.
     * @return boolean  true if insert success
     */
    public static function insertAcceptTrackBack($categoryID = NULL,$entryID = NULL,$accept = 1){
        if(is_null($categoryID) or is_null($entryID)){
            return FALSE;
        }
        $AcceptTrackBack_insert = DB_DataObject::factory('AcceptTrackBack');
        $AcceptTrackBack_insert->categoryID = $categoryID;
        $AcceptTrackBack_insert->entryID = $entryID;
        $AcceptTrackBack_insert->accept = $accept;
        $AcceptTrackBack_insert->insert();
        return TRUE;
    }

    /**
     * delete accepttrackback
     * @param  integer  category id
     * @param  integer  entry id
     * @return boolean  true if delete success
     */
    public static function deleteAcceptTrackBack($categoryID = NULL,$entryID = NULL){
        if(is_null($categoryID) or is_null($entryID)){
            return FALSE;
        }
        $AcceptTrackBack_delete = DB_DataObject::factory('AcceptTrackBack');
        $AcceptTrackBack_delete->categoryID = $categoryID;
        $AcceptTrackBack_delete->entryID = $entryID;
        $AcceptTrackBack_delete->delete();
        return TRUE;
    }

    /**
     * select accepttrackback
     * @param  integer  category id
     * @param  integer  entry id
     * @return integer  1 if accept. 0 if not accept.
     */
    public static function selectAcceptTrackBack($categoryID = NULL,$entryID = NULL,$mask = 1){
        if(is_null($categoryID) or is_null($entryID)){
            return $mask;
        }
        $AcceptTrackBack_select = DB_DataObject::factory('AcceptTrackBack');
        $AcceptTrackBack_select->whereAdd("categoryID =  $categoryID");
        $AcceptTrackBack_select->whereAdd("entryID = $entryID");
        $AcceptTrackBack_select->find();
        $AcceptTrackBack_select->fetch();
        return $AcceptTrackBack_select->accept;
    }
}
?>
