<?php

/**
 * Session
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @authors MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: Session.php,v 1.12 2005/02/21 03:38:57 shinobu Exp $
 */
class Session{
    private $session_id;
    private $session_life_time;

    /**
     * Constructor
     *
     * @param string    cache save dir
     * @param integer   session life time
     */
    function __construct($cache_dir = NULL,$session_life_time = 3600){
        if(!is_null($cache_dir)){
            session_save_path($cache_dir);
        }
        $this->session_life_time = $session_life_time;

        $this->sessionStart();

        if(is_null($this->getUserId())){
            $this->setGuestUserId();
        } else if($_SESSION["session_time_limit"] < time()) {
            $this->sessionRestart();
            $this->setGuestUserId();
        }
        $_SESSION["session_time_limit"] = time() + $this->session_life_time;

        /* gc */
        global $im;
        $Config = $im->new_once("Config");
        if($this->gcProcessStarts($Config->get_value("ChulaBlog","session_gc_probability")
                              ,$Config->get_value("ChulaBlog","session_gc_divisor"))){
            $this->gc($Config->get_value("ChulaBlog","session_expire_time"));
        }
    }

    /**
     * set user_id to guest user id
     *
     * @param void
     * @param void
     */
    private function setGuestUserId(){
        global $im;
        $Config = $im->new_once("Config");
        $this->setUserId($Config->get_value("ChulaBlog","guest_user_id"));
    }

    /**
     * start session and set session_id
     *
     * @param void
     * @param void
     */
    private function sessionStart(){
        session_start();
        $this->session_id = session_id();
    }

    /**
     * stop session and unset session valiables.
     *
     * @param void
     * @param void
     */
    private function sessionStop(){
        session_unset();
        session_destroy();
    }

    /**
     * stop and start session
     *
     * @param void
     * @param void
     */
    private function sessionRestart(){
        $this->sessionStop();
        $this->sessionStart();
    }

    /**
     * garbage collection for session cache
     *
     * @param integer   session expire time
     * @param void
     */
    private function gc($session_expire_time = 3600){
        $dir = "./Data";
        $dir_info = scandir($dir);

        foreach($dir_info as $file){
            if(preg_match("/^sess_/i",$file)){
                $file_info = stat($dir . "/" . $file);
                if($session_expire_time < (time() - $file_info[10])){
                    unlink("$dir/$file");
                }
            }
        }
    }

    /**
     * garbage collection probability
     * gc starts session_gc_probability/session_gc_divisor
     *
     * @param integer   session gc probability
     * @param integer   session gc divisor
     * @param boolean   true if gc have to start
     */
    private function gcProcessStarts($session_gc_probability = 1,$session_gc_divisor = 100){
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));
        if(rand(1,$session_gc_divisor) <= $session_gc_probability){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * reload using http header location.
     * use be careful. 
     * There is a possibility of causing the redirection loop.
     *
     * @param   void
     * @return  void
     */
    public function reload($query = NULL){
        global $im;
        unset($_POST);
        if(is_null($query)){
            $HTTPQuery = $im->new_once("HTTPQuery");
            header("Location: ?id=" . $HTTPQuery->getId() . "&sd=" . $HTTPQuery->getSd());
        } else {
            header("Location: $query");
        }
    }

    /**
     *  setter / getter for session variable
     *  setter : $Session->setVarName($args); // $_SESSION["VarName"] = $args;
     *  getter : $Session->getVarName(); // return $_SESSION["VarName"];
     *  unsetter : $Session->delVarName(); // unset($_SESSION["VarName"]);
     *
     * @param string    setVarName or getName or delName. 
     * @param mixed     set variable
     * @return  mixed   return variable when getter
     */
    public function __call($method,$args){
        switch(substr($method,0,3)){
            case "set" :
                $_SESSION[substr($method,3)] = $args[0];
                break;
            case "get" :
                if(isset($_SESSION[substr($method,3)])){
                    return $_SESSION[substr($method,3)];
                } else {
                    return NULL;
                }
                break;
            case "del" :
                unset($_SESSION[substr($method,3)]);
                break;
            default :
                break;
        }
    }
}
?>
