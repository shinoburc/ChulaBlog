<?php

  /*
   * state : 
   *  Category::listup  -> display category
   *  Category::display -> display entry
   *  Category::display_using_date -> display target date entry
   */
  class Category extends ChulaPlugin
  {
    private $name = "Category";
    private static $static_name = "Category";
    private $objNum;

    function __construct( $_num=NULL ){
      $this->objNum  = $_num;
      $this->register_view();
    }

    private function register_view(){
      global $im;
      $NotificationCenter = $im->new_once("NotificationCenter");

      $HTTPQuery = $im->new_once("HTTPQuery");
      $target = $HTTPQuery->getSd(0);
      $command = $HTTPQuery->getSd(1);
      $key = $HTTPQuery->getSd(2);

      $NotificationCenter->setObserver("ChulaViewNotification::$this->name::listup", $this->name,"run");
      if($target == $this->name){
        switch($command){
          case "display" :
            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::display", $this->name,"run");
            break;
          case "display_using_date" :
            $NotificationCenter->setObserver("ChulaViewNotification::$this->name::display_using_date", $this->name,"run");
            break;
          default :
            break;
        }
      }
    }

    public static function getState(){
        return array(self::$static_name . "::listup" => "Category listup"
                    ,self::$static_name . "::display" => "Category display"
                    ,self::$static_name . "::display_using_date" => "Category display using date");
    }

    public static function plugInTrigger($user_id){
        ChulaDBIO::insertState(self::$static_name . "::listup",$user_id,"MENU");
        ChulaDBIO::insertState(self::$static_name . "::display",$user_id,"MAINPANE");
        ChulaDBIO::insertState(self::$static_name . "::display_using_date",$user_id,"MAINPANE");
    }

    public static function plugOutTrigger($user_id){
        ChulaDBIO::deleteState(self::$static_name . "::listup",$user_id);
        ChulaDBIO::deleteState(self::$static_name . "::display",$user_id);
        ChulaDBIO::deleteState(self::$static_name . "::display_using_date",$user_id);
    }

    public function run($notification){
        global $im;
        $HTTPQuery = $im->new_once("HTTPQuery");
        $target = $HTTPQuery->getSd(0);
        $command = $HTTPQuery->getSd(1);
        $key1 = $HTTPQuery->getSd(2);
        $key2 = $HTTPQuery->getSd(3);

      switch( $notification->getMessage()){
        case "ChulaViewNotification::$this->name::display" :
          print("<TABLE BORDER=1 BGCOLOR=\"#ffffff\" WIDTH=\"100%\">\n");
          $this->display( $key1, $key2);
          print("</TABLE>\n");
          break;
        case "ChulaViewNotification::$this->name::display_using_date" :
          print("<TABLE BORDER=1 BGCOLOR=\"#ffffff\" WIDTH=\"100%\">\n");
          $this->display_using_date( $key1 );
          print("</TABLE>\n");
          break;
        case "ChulaViewNotification::$this->name::listup" :
        default :
          print("<TABLE BORDER=1 BGCOLOR=\"#ccffcc\" WIDTH=\"100%\">\n");
          $this->listup();
          print("</TABLE>\n");
          break;
      }

    }

    private function display( $_categoryID = NULL, $_entryID = NULL){
      if(is_null($_categoryID)){
        return FALSE;
      }
      /* Category */
      $Category = DB_DataObject::factory('Category');
      $Category->whereAdd("categoryID = $_categoryID");
      $Category->find();
      $Category->fetch();

      /* ENtry */
      $Entry = DB_DataObject::factory('Entry');
      $Entry->whereAdd("categoryID = $_categoryID");
      if(!is_null($_entryID)){
        $Entry->whereAdd("entryID = $_entryID");
      }
      $Entry->orderBy("date DESC");
      $Entry->find();
      
      /* NotificationCenter */
      global $im;
      $NotificationCenter = $im->new_once('NotificationCenter');
      $Notification = $im->new_new('Notification', 'ForEachEntryNotification', NULL, $this->name, NULL);

      print("<p>Entry : " . htmlspecialchars($Category->name) );
      while( $Entry->fetch() ){
        $cal = date('Y/m/d H:i',$Entry->date);
 
        print("<TR>\n");
        print("  <TD>$cal</TD>\n");
        print("  <TD>" . htmlspecialchars($Entry->name) . "</TD>\n");
        print("</TR>\n"); 
        print("<TR>\n");
        print("  <TD COLSPAN=2>" . htmlspecialchars($Entry->content) . " <BR><BR></TD>\n");
        print("</TR>\n"); 

        print("<TR>\n");
        print("<TD COLSPAN=2>");
        /* Notification */
        $Notification->setObject(array($Entry->categoryID,$Entry->entryID), $this->name, NULL);
        $NotificationCenter->postNotification($Notification);
        print("</TD>");
        print("</TR>\n"); 
      }
    }

    private function display_using_date( $_date = NULL ){
      if(is_null($_date)){
        return FALSE;
      }
      $Entry= DB_DataObject::factory('Entry');
      $Entry->whereAdd("date >= $_date");
      $Entry->whereAdd("date < " . ($_date + (60 * 60 * 24)),"AND");
      $Entry->orderBy("date DESC");
      $Entry->find();

      /* NotificationCenter */
      global $im;
      $NotificationCenter = $im->new_once('NotificationCenter');
      $Notification = $im->new_new('Notification', 'ForEachEntryNotification', NULL, $this->name, NULL);

      while( $Entry->fetch() ){
        $cal = date('Y/m/d H:i',$Entry->date);
        print("<TR>\n");
        print("  <TD>$cal</TD>\n");
        print("  <TD>" . htmlspecialchars($Entry->name) . "</TD>\n");
        print("</TR>\n"); 
        print("<TR>\n");
        print("  <TD COLSPAN=2>" . htmlspecialchars($Entry->content) . " <BR><BR></TD>\n");
        print("</TR>\n"); 

        print("<TR>\n");
        print("<TD COLSPAN=2>");
        /* Notification */
        $Notification->setObject(array($Entry->categoryID,$Entry->entryID));
        $NotificationCenter->postNotification($Notification);
        print("</TD>");
        print("</TR>\n"); 
      }
    }

    private function listup(){
      // Get destination ID
      global $im;
      $HTTPQuery = $im->new_once("HTTPQuery");
      $to = $HTTPQuery->getId();

      $Category = DB_DataObject::factory('Category');
      $Category->whereAdd("user_id = '" . $to . "'");
      $Category->orderBy("date DESC");
      $Category->find();

      while( $Category->fetch() ){
        $ID   = htmlspecialchars($Category->categoryID);
        $date = date('Y/m/d',$Category->date);
        $name = mb_substr(htmlspecialchars($Category->name),0,8,'euc-jp');

        print("<TR><TD>\n");
        print("[$date]");
        print("<A HREF=\"" . $HTTPQuery->genURI($to,"Category::display::$ID") . "\"><FONT SIZE=2>$name...</FONT></A>");
        print("</TD></TR>\n"); 
      }
    }
    public static function description(){
      return "Category and Entry viewer";
    }
  }
?>
