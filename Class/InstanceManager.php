<?php
/**
 * InstanceManager
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: InstanceManager.php,v 1.4 2005/02/17 12:57:07 shinobu Exp $
 */
class InstanceManager{
    private $instance_ary;

    /**
     * Wrapper for PHP new operation.
     * @param  string   class name
     * @param  mixed    class argument
     * @return  instance    instance
     */
    public function new_new(){
        $_args = func_get_args();
        if(!is_array($_args)){
            return NULL;
        }
        $_target = array_shift($_args);

        if(is_null($_target) or !class_exists($_target)){
            return NULL;
        }

          /* Argument Processing No1. */
          /* slow in eval */
        /*
        if(!is_null($_args) or is_array($_args)){
            foreach ($_args as $key => $value){
                $tmp_args[] = "\"\$_args[$key]\"";
            }
            $args_string = implode(",",$tmp_args);
            eval(" \$_obj = new \$_target(" . $args_string . ");");
        } else {
              $_obj = new $_target();
        }
        */

          /* Argument Processing No2. */
        /* This code is fast but very verbose and restrictive... HELP ME! */
        switch(count($_args)){
            case 1 :
                $_obj = new $_target($_args[0]);
                break;
            case 2 :
                $_obj = new $_target($_args[0],$_args[1]);
                break;
            case 3 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2]);
                break;
            case 4 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3]);
                break;
            case 5 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4]);
                break;
            case 6 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5]);
                break;
            case 7 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5],$_args[6]);
                break;
            case 8 :
                $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5],$_args[6],$_args[7]);
                break;
            default :
                $_obj = new $_target();
                break;
        }

        $_uniqid = uniqid();
        $this->instance_ary["$_target" . "_" . "$_uniqid"]["instance"] = $_obj;
        $this->instance_ary["$_target" . "_" . "$_uniqid"]["args"] = $_args;

        return $_obj;
    }

    /**
     * Wrapper for PHP new operation.
     * reuse instance if instance already exists.
     *
     * @param  string   class name
     * @param  mixed    class argument
     * @return  instance    instance
     */
    public function new_once(){
        $_args = func_get_args();
        if(!is_array($_args)){
            return NULL;
        }
        $_target = array_shift($_args);

        if(is_null($_target) or !class_exists($_target)){
            return NULL;
        }

        if($this->is_exists($_target)){
            return $this->instance_ary["$_target"]["instance"];
        } else {
            switch(count($_args)){
                case 1 :
                    $_obj = new $_target($_args[0]);
                    break;
                case 2 :
                    $_obj = new $_target($_args[0],$_args[1]);
                    break;
                case 3 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2]);
                    break;
                case 4 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3]);
                    break;
                case 5 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4]);
                    break;
                case 6 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5]);
                    break;
                case 7 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5],$_args[6]);
                    break;
                case 8 :
                    $_obj = new $_target($_args[0],$_args[1],$_args[2],$_args[3],$_args[4],$_args[5],$_args[6],$_args[7]);
                    break;
                default :
                    $_obj = new $_target();
                    break;
            }
            $this->instance_ary["$_target"]["instance"] = $_obj;
            $this->instance_ary["$_target"]["args"] = $_args;
            return $_obj;
        }
    }

    /**
     * get arguments
     *
     * @param  string   class name
     * @return  array   arguments
     */
    public function get_args($_target = NULL){
        if(is_null($_target) or !$this->is_exists($_target)){
            return NULL;
        }
        return $this->instance_ary["$_target"]["args"];
    }
    
    /**
     * true if instance already exists
     *
     * @param  string   class name
     * @return  boolean true if instance already exists
     */
    public function is_exists($_target = NULL){
        if(is_array($this->instance_ary) and array_key_exists($_target,$this->instance_ary) and is_object($this->instance_ary["$_target"]["instance"])){
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
?>
