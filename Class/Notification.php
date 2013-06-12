<?php

/*
 * Notification
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @authors MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: Notification.php,v 1.4 2005/02/12 04:52:59 shinobu Exp $
 */

class Notification{
    private $message;
    private $object;
    private $objectType;
    private $from;
    private $to;

    /**
     * Constructor
     *
     * @param  string   message name
     * @param  mixed    object
     * @param  string   message from
     * @param  string   message to
     */
    function __construct($message = NULL,$object = NULL,$from = NULL,$to = NULL){
            $this->setMessage($message);
            $this->setObject($object);
            $this->setFrom($from);
            $this->setTo($to);
    }

    /**
     * get message name
     *
     * @param  void
     * @return  string  message name
     */
    public function getMessage(){
            return $this->message;
    }

    /**
     * set message name
     *
     * @param  string   message name
     * @return  void
     */
    public function setMessage($message = NULL){
            $this->message = $message;
    }

    /**
     * get object type
     *
     * @param  void
     * @return string "null","object","array","float","int","string","unknown"
     */
    public function getObjectType(){
            return $this->objectType;
    }

    /**
     * set object type
     *
     * @param mixed set this object type.
     * @return void
     */
    public function setObjectType($object = NULL){
        if(is_null($object)){
            $this->objectType = "null";
        } else if(is_object($object)){
            $this->objectType = "object";
        } else if(is_array($object)){
            $this->objectType = "array";
        } else if(is_float($object)){
            $this->objectType = "float";
        } else if(is_int($object)){
            $this->objectType = "int";
        } else if(is_string($object)){
            $this->objectType = "string";
        } else {
            $this->objectType = "unknown";
        }
    }

    /**
     * get object
     *
     * @param void
     * @return mixed    message object
     */
    public function getObject(){
        return $this->object;
    }

    /**
     * set object
     *
     * @param mixed set object
     * @return void
     */
    public function setObject($object = NULL){
        $this->setObjectType($object);
        $this->object = $object;
    }

    /**
     * get message from
     *
     * @param void
     * @return string   message from
     */
    public function getFrom(){
        return $this->from;
    }

    /**
     * set message from
     *
     * @param string    message from
     * @return void
     */
    public function setFrom($from){
        $this->from = $from;
    }

    /**
     * get message to
     *
     * @param void
     * @return string   message to
     */
    public function getTo(){
        return $this->to;
    }

    /**
     * set message to
     *
     * @param string    message to
     * @return void
     */
    public function setTo($to){
        $this->to = $to;
    }
}

?>
