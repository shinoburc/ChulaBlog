<?php

/**
 * NotificationCenter
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: NotificationCenter.php,v 1.2 2005/02/12 04:52:59 shinobu Exp $
 */
class NotificationCenter{
    /**
     * @var array observer object and method name array
     */
    private $observer = Array();

    /**
     * set observer
     *
     * @param String set notification message
     * @param String set notification object
     * @param String set notification method
     * @return boolean setObserver status
     */
    function setObserver($message = NULL,$object = NULL,$method = NULL){
        if(is_null($message) or is_null($object) or is_null($method)){
            return FALSE;
        }
        $this->observer[$message][] = array($object,$method);
        return TRUE;
    }

    /**
     * post notification
     *
     * @param Object notification object
     * @return integer number of post notification
     * @see Notification
     */
    function postNotification($notification = NULL){
        $post_count = 0;
        if(is_null($notification)){
            return $post_count;
        }
        $message = $notification->getMessage();
        if(isset($this->observer["$message"])){
            global $im;
            foreach($this->observer["$message"] as $observer_info){
                if(is_null($observer_info[0]) or is_null($observer_info[1])){
                    continue;
                }
                $class  = $observer_info[0];
                $method = $observer_info[1];
                if(is_null($notification->getTo()) or $notification->getTo() == $class){
                    if(class_exists($class)){
                        $obj = $im->new_once($class);
                        if(method_exists($obj, $method)){
                            $obj->$method($notification);
                            $post_count++;
                        }
                    }
                }
            }
        }
        return $post_count;
    }
}

?>
