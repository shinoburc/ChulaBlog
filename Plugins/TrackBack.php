<?php

class TrackBack extends ChulaPlugin{
    private $name = "TrackBack";
    private static $static_name = "TrackBack";

    /* Constructor */
    function __construct(){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");
        $target = $HTTPQuery->getSd(0);
        $command = $HTTPQuery->getSd(1);

        $NotificationCenter = $im->new_once("NotificationCenter");
        $NotificationCenter->setObserver("ForEachEntryNotification", $this->name,"each_entry");
        $NotificationCenter->setObserver("EntryEditFormNotification", $this->name,"entry_edit_form");
        $NotificationCenter->setObserver("EntryInsertNotification", $this->name,"entry_insert");
        $NotificationCenter->setObserver("EntryUpdateNotification", $this->name,"entry_update");
        $NotificationCenter->setObserver("EntryDeleteNotification", $this->name,"entry_delete");
        switch($target){
            case "Category" :
                break;
            case "TrackBack" :
                switch($command){
                    case "display" :
                        $NotificationCenter = $im->new_once("NotificationCenter");
                        $NotificationCenter->setObserver("ChulaViewNotification::$this->name::display", $this->name,"display");
                        break;
                    case "ping" :
                        $this->ping();
                        break;
                    default :
                        break;
                }
                break;
            default :
                break;
        }
    }

    public static function getState(){
        return array(
                    self::$static_name . "::display"
                    ,self::$static_name . "::ping"
                    );
    }

    public static function plugInTrigger($user_id){
        ChulaDBIO::insertState(self::$static_name . "::display",$user_id,"MAINPANE");
        ChulaDBIO::insertState(self::$static_name . "::ping",$user_id,"MAINPANE");
    }

    public static function plugOutTrigger($user_id){
        ChulaDBIO::deleteState(self::$static_name . "::display",$user_id);
        ChulaDBIO::deleteState(self::$static_name . "::ping",$user_id);
    }

    public function each_entry($notification){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");
        $id = $HTTPQuery->getId();
        if($notification->getObjectType() == "array"){
            $args = $notification->getObject();
            if(ChulaDBIO::selectAcceptTrackBack($args[0],$args[1])){
                    echo "&nbsp;<A HREF=\"" . $HTTPQuery->genURI($id,"TrackBack::display::" . $args[0] . "::" . $args[1]) . "\">TrackBack</A>";
            }
        }
    }

    public function entry_edit_form($notification){
        echo "TrackBack Ping URL<input type=\"text\" name=\"" . $this->name . "_trackback_ping_url\">";
        echo "Accept TrackBack <input type=\"checkbox\" name=\"" . $this->name . "_accept_trackback\" CHECKED>";
    }

    public function entry_insert($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            $entry_info = $notification->getObject();
            $categoryID = $entry_info[0];
            $entryID = $entry_info[1];
            if(!is_null($HTTPQuery->getSafePost($this->name . "_accept_trackback"))){
                ChulaDBIO::insertAcceptTrackBack($categoryID,$entryID,1);
            } else {
                ChulaDBIO::insertAcceptTrackBack($categoryID,$entryID,0);
            }

            $trackback_ping_url = $HTTPQuery->getSafePost($this->name . "_trackback_ping_url");
            if(!empty($trackback_ping_url)){
                $this->trackback($trackback_ping_url,$categoryID,$entryID);
            }
    }

    private function trackback($url = NULL,$categoryID = NULL,$entryID = NULL){
        if(empty($url)){
            return;
        }
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");

        $Entry_select = DB_DataObject::factory('Entry');
        $Entry_select->whereAdd("categoryID = " . $categoryID);
        $Entry_select->whereAdd("entryID = " . $entryID);
        $Entry_select->find();
        $Entry_select->fetch();

        /* XXX */
        $entry_url = $HTTPQuery->getURL() . "?" 
                    . "id=" . $HTTPQuery->getId()
                    . "&sd=" . $HTTPQuery->getSd(0)
                    . "::" . $HTTPQuery->getSd(1)
                    . "::" . $categoryID
                    . "::" . $entryID;

        $url = preg_replace("/&/","%26amp%3B",$url);
        $POST  = "title=" . urlencode($Entry_select->name);
        $POST .= "&excerpt=" . urlencode($Entry_select->content);
        $POST .= "&url=" . urlencode($entry_url);
        $POST .= "&blog_name=ChulaBlog";
        $context_opts = array('http' => 
                            array('method'=>"POST"
                                 ,'content' => $POST
                                 ,'header' => 'Content-type: application/x-www-form-urlencoded'));
        $context = stream_context_create($context_opts);
        /* $result = file_get_contents($url,NULL,$context); */
    }

    public function entry_update($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            $entry_info = $notification->getObject();
            $categoryID = $entry_info[0];
            $entryID = $entry_info[1];

            if(ChulaDBIO::deleteAcceptTrackBack($categoryID,$entryID)){
                if(!is_null($HTTPQuery->getSafePost($this->name . "_accept_trackback"))){
                    ChulaDBIO::insertAcceptTrackBack($categoryID,$entryID,1);
                } else {
                    ChulaDBIO::insertAcceptTrackBack($categoryID,$entryID,0);
                }
            }
    }

    public function entry_delete($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            $entry_info = $notification->getObject();
            $categoryID = $entry_info[0];
            $entryID = $entry_info[1];

            ChulaDBIO::deleteAcceptTrackBack($categoryID,$entryID);
    }

    public function display($notification){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");
        $id = $HTTPQuery->getId();
        echo "<p>TrackBack URL is <a href=\"" . $HTTPQuery->genURI($id,"TrackBack::ping::" . $HTTPQuery->getSd(2) . "::" . $HTTPQuery->getSd(3)) . "\">" . $HTTPQuery->genURI($id,"TrackBack::ping::" . $HTTPQuery->getSd(2) . "::" . $HTTPQuery->getSd(3)) . "</a>";
    }

    private function ping(){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");

        $title = $HTTPQuery->getSafePost("title");
        $excerpt = $HTTPQuery->getSafePost("excerpt");
        $url = $HTTPQuery->getSafePost("url");
        $blog_name = $HTTPQuery->getSafePost("blog_name");

        echo "<p>ping";
        echo "<p>$title";
        echo "<p>$excerpt";
        echo "<p>$url";
        echo "<p>$blog_name";
        exit();
    }

    public static function description(){
        return "TrackBack";
    }
}
?>
