<?php

define('CHULABLOG_INIT_SUCCESS',1);
define('CHULABLOG_DIRECTORY_OPEN_ERROR',2);
define('CHULABLOG_CREATE_DATABASE_ERROR',3);

/**
 * ChulaBlog SystemInit
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: SystemInit.php,v 1.4 2005/02/12 04:52:59 shinobu Exp $
 */
class SystemInit{
    /**
     * ChulaBlog DB init
     *
     * @param void
     * @return void
     */
    function init(){
        if(!is_dir("Data")){
            $mkdir = mkdir("Data",0777);
            if(!$mkdir){
                return CHULABLOG_DIRECTORY_OPEN_ERROR;
            }
        }
        /* create database and table */
        if(!is_file("Data/chulablog.db")){
            if($db = sqlite_open("Data/chulablog.db",0666,$db_error)){
                $dir_info = scandir("Schema");
                /* create table */
                foreach($dir_info as $file){
                    $query = "";
                    if(preg_match("/table.sql$/i",$file)){
                        $query = file_get_contents("Schema/$file");
                        sqlite_query($db,$query);
                    }
                }
                /* insert data */
                foreach($dir_info as $file){
                    $query = "";
                    if(preg_match("/data.sql$/i",$file)){
                        $query = file_get_contents("Schema/$file");
                        sqlite_query($db,$query);
                    }
                }
            } else {
                return CHULABLOG_CREATE_DATABASE_ERROR;
            }
        }
        return CHULABLOG_INIT_SUCCESS;
    }
}

?>
