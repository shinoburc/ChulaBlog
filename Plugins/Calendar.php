<?php

    /*
     * $Id: Calendar.php,v 1.18 2005/05/05 14:09:44 shinobu Exp $
     *
     * Calendar class.
     * 
     * Usage:
     *        $obj = new Calendar($arg1,$arg2);
     *        ($arg1 = mktime())
     *        ($arg2 = "m" or "w" or "d" or "c")
     *        ("m" month)
     *        ("w" week)
     *        ("d" day)
     *        ("c" custom) <- there is no code yet
     * 
     *        (case 1)
     *        $cal = new Calendar();
     *        $date_info = $cal->GetInfo();
     *        $date = $cal->GetDateArray();
     *        (display code using $date_info and $date)
     * 
     *        (case 2)
     *        $cal = new Calendar( mktime(0,0,0,7,30,1979) );
     *        $date_info = $cal->GetInfo();
     *        $date = $cal->GetDateArray();
     *        (display code using $date_info and $date)
     * 
     *        (case 3)
     *        $cal = new Calendar( mktime(0,0,0,7,30,1979), "w" );
     *        $cal->run();
     * 
     *        (case 4)
     *        $cal = new Calendar( mktime(0,0,0,7,30,1979), "w" );
     *        $cal->SetAnchorFormat("goto::%s");
     *        $cal->run();
     *        $cal->SetAnchorFormat(NULL);
     *        $cal->SetTarget( mktime(0,0,0,8,1,1979));
     *        $cal->SetTerm("w");
     *        $cal->run();
     */

    class Calendar extends ChulaPlugin{
        private $name = "Calendar";
        private static $static_name = "Calendar";
        private $anchor_format = NULL;
        private $target;
        private $term;
        private $start;
        private $end;
        private $border = 0;

        /* Constructor */
        function __construct($_target = NULL,$_term = NULL){
            global $im;
            $Session = $im->new_once("Session"); 

            if(!is_null($Session->getTargetDate())){
                $_target = $Session->getTargetDate();
            }
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);
            $key = $HTTPQuery->getSd(2);

            switch($target){
                case $this->name :
                    switch($command){
                        case "ChangeTarget" :
                            $_target = $key;
                            break;
                        default :
                            break;
                    }
                    break;
                default :
                    break;
            }

            $this->SetTerm($_term);
            $this->SetTarget($_target);
            $this->Reconstruct();
            $this->register_view();
            $id= $HTTPQuery->getId();
            if(!is_null($id)){
                $this->SetAnchorFormat($HTTPQuery->genURI($id,"Category::display_using_date::%s"));
            } else {
                $this->SetAnchorFormat(NULL);
            }
        }

        /* Reconstruct */
        public function Reconstruct(){
            $this->SetStart($this->GetStartDate());
            $this->SetEnd($this->GetEndDate());
        }

        private function register_view(){
            global $im;
            $NotificationCenter = $im->new_once("NotificationCenter");
            $NotificationCenter->setObserver("ChulaViewNotification::$this->name", $this->name,"run");
        }

        public static function getState(){
            return array(self::$static_name);
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name,$user_id,"MENU");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name,$user_id);
        }

        public function run($notification){
            global $im;

            $_date_info = $this->GetInfo();
            $_date = $this->GetDateArray();

            $HTTPQuery = $im->new_once("HTTPQuery");

            if(!is_null($HTTPQuery->getId())){
                $to = $HTTPQuery->getId();
                $Entry = DB_DataObject::factory('Entry');
                $Entry->selectAdd("date");
                $Entry->whereAdd("user_id = '" . $to . "'");
                $Entry->whereAdd("date >= $this->start");
                $Entry->whereAdd("date < $this->end","AND");
                $Entry->find();
                $entry_exists = array();
                while( $Entry->fetch() ){
                    $beginningOfDate = explode("/",date("m/d/Y",$Entry->date));
                    $working_index = mktime(0,0,0,$beginningOfDate[0],$beginningOfDate[1],$beginningOfDate[2]);
                    if(!isset($entry_exists[$working_index])){
                        $entry_exists[$working_index] = 1;
                    } else {
                        $entry_exists[$working_index]++;
                    }
                }
            } else {
                $to = "";
            }

            echo "<table><tr>";
            echo "<td colspan=7><center>" . date("Y/m",$this->GetStart()) . "</center></td>";
            echo "<tr>";
            echo "<td><a href=\"" . $HTTPQuery->genURI($to,"Calendar::ChangeTarget::" . $this->AddDate($this->GetStart(),"y",FALSE)) . "\" title=\"prev year\">&lt;&lt;</a></td>";
            echo "<td><a href=\"" . $HTTPQuery->genURI($to,"Calendar::ChangeTarget::" . $this->AddDate($this->GetStart(),"m",FALSE)) . "\" title=\"prev month\">&lt;</a></td>";
            echo "<td></td>";
            echo "<td><a href=\"" . $HTTPQuery->genURI($to,"Calendar::ChangeTarget::" . time()) . "\" title=\"today\">*</a></td>";
            echo "<td></td>";
            echo "<td><a href=\"" . $HTTPQuery->genURI($to,"Calendar::ChangeTarget::" . $this->AddDate($this->GetStart(),"m",TRUE)) . "\" title=\"next month\">&gt;</a></td>";
            echo "<td><a href=\"" . $HTTPQuery->genURI($to,"Calendar::ChangeTarget::" . $this->AddDate($this->GetStart(),"y",TRUE)) . "\" title=\"next year\">&gt;&gt;</a></td>";
            echo "<tr>";
            echo str_repeat("<td></td>", $_date_info["wday"]);
            foreach($_date as $working_date => $info){
                echo "<td>";
                if(!is_null($this->anchor_format) and isset($entry_exists["$working_date"])){
                    printf("<A href=\"" 
                        . $this->anchor_format 
                        . "\" title=\""
                        . $entry_exists["$working_date"]
                        . " entry\">" 
                        . $info["day"] 
                        . "</A>",$working_date);
                } else {
                    echo $info["day"];
                }
                echo "</td>";
                if($_date[$working_date]["wday"] == 6){
                    echo "<tr>";
                }
            }
            echo str_repeat("<td></td>", (6 - $_date[$working_date]["wday"]));
            echo "</table>";
        }

        public function GetInfo(){
            $_date_info = array();
            $_date_info["wday"] = date("w",$this->start); 
            $_date_info["start"] = $this->start;
            $_date_info["end"] = $this->end;
            return $_date_info;
        }

        public function GetDateArray(){
            $_date = array();
            $working_date = $this->start;
            while($working_date <= $this->end){
                list
                (
                    $_date[$working_date]["year"],
                    $_date[$working_date]["month"],
                    $_date[$working_date]["day"],
                    $_date[$working_date]["wday"]
                ) = explode(",",date("Y,m,d,w",$working_date));
                $working_date = $this->AddDate($working_date);
            }
            return $_date;
        }

        private function AddDate($_date,$_term = "d",$_plus = TRUE){
            switch($_term){
                case "y" :
                    if($_plus){
                        $_date_array = explode("/",date("H/i/s/m/d/Y",$_date));
                        $_ret = mktime($_date_array[0],$_date_array[1],$_date_array[2],$_date_array[3],$_date_array[4],$_date_array[5] + 1);
                    } else {
                        $_date_array = explode("/",date("H/i/s/m/d/Y",$_date));
                        $_ret = mktime($_date_array[0],$_date_array[1],$_date_array[2],$_date_array[3],$_date_array[4],$_date_array[5] - 1);
                    }
                    break;
                case "m" :
                    if($_plus){
                        $_date_array = explode("/",date("H/i/s/m/d/Y",$_date));
                        $_ret = mktime($_date_array[0],$_date_array[1],$_date_array[2],$_date_array[3] + 1,$_date_array[4],$_date_array[5]);
                    } else {
                        $_date_array = explode("/",date("H/i/s/m/d/Y",$_date));
                        $_ret = mktime($_date_array[0],$_date_array[1],$_date_array[2],$_date_array[3] - 1,$_date_array[4],$_date_array[5]);
                    }
                    break;
                case "c" :
                case "d" :
                default :
                    if($_plus){
                        $_ret = $_date + (60 * 60 * 24);
                    } else {
                        $_ret = $_date - (60 * 60 * 24);
                    }
            }
            return $_ret;
        }

        public function GetStartDate(){
            $_date_ar = getdate($this->target);
            switch($this->term){
                case "d":
                    return $this->target;
                    break;
                case "w":
                    return mktime(0,0,0,$_date_ar["mon"],$_date_ar["mday"] - date("w",$this->target),$_date_ar["year"]);
                    break;
                case "c":
                case "m":
                default :
                    return mktime(0,0,0,$_date_ar["mon"],1,$_date_ar["year"]);
            }
        }

        public function GetEndDate(){
            $_date_ar = getdate($this->target);
            switch($this->term){
                case "d":
                    return $this->target;
                    break;
                case "w":
                    return mktime(23,59,59,$_date_ar["mon"],$_date_ar["mday"] + 6 - date("w",$this->target),$_date_ar["year"]);
                    break;
                case "c":
                case "m":
                default :
                    return mktime(23,59,59, $_date_ar["mon"], date("t",$this->target), $_date_ar["year"]);
            }
        }

        public function SetTarget($_target){
            global $im;
            $Session = $im->new_once("Session");
            if(!is_null($_target)){
                $this->target = $_target;
            } else {
                $this->target = time();
            }
            $Session->setTargetDate($this->target);
        }

        public function GetAnchorFormat(){
            return $this->anchor_format;
        }

        public function SetAnchorFormat($_anchor_format = NULL){
            $this->anchor_format = $_anchor_format;
        }

        public function GetTarget(){
            return $this->target;
        }

        public function SetStart($_start){
            $this->start = $_start;
        }

        public function GetStart(){
            return $this->start;
        }

        public function SetEnd($_end){
            $this->end = $_end;
        }

        public function GetEnd(){
            return $this->end;
        }

        public function SetTerm($_term){
            if($_term == "m" or $_term == "w" or $_term == "d" or $_term == "c"){
                $this->term = $_term;
            } else {
                $this->term = "m";
            }
        }

        public function GetTerm(){
            return $this->term;
        }

        public function SetBorder($_border){
            $this->border = $_border;
        }

        public function GetBorder(){
            return $this->border;
        }

        public static function description(){
            return "Calendar for ChulaBlog";
        }
    }
?>
