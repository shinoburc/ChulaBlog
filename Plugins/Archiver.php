<?php

    /*
     * $Id: Archiver.php,v 1.11 2005/05/05 14:09:44 shinobu Exp $
     *
     * ChulaBlog Archiver 
     * 
     * state : Archiver::year
     *         Archiver::month
     *         Archiver::day
     */

    class Archiver extends ChulaPlugin{
        private $name = "Archiver";
        private static $static_name = "Archiver";

        /* Constructor */
        function __construct(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);
            $key = $HTTPQuery->getSd(2);

            $viewObj = $im->new_once("ChulaView");

            switch($target){
                  /* my operation */
                case $this->name . "::year_display" :
                    $this->display("year");
                    break;
                case $this->name . "::month_display" :
                    $this->display("month");
                    break;
                case $this->name . "::day_display" :
                    $this->display("day");
                    break;
                default :
                    break;
            }
        }


        public function display($term){
            switch($term){
                case $this->name . "::insert_link" :
                    break;
                case $this->name . "::insert_form" :
                    break;
                case $this->name . "::update" :
                    break;
                default :
                    break;
            }
        }

        public function link($event){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $id = $HTTPQuery->getId();

            switch($event){
                case $this->name . "::year" :
                    print "<p><a href=\"" . $HTTPQuery->genURI($id,"Archiver::display_year") . "\">Archiver year</a>";
                    break;
                case $this->name . "::month" :
                    print "<p><a href=\"" . $HTTPQuery->genURI($id,"Archiver::display_month") . "\">Archiver month</a>";
                    break;
                case $this->name . "::day" :
                    print "<p><a href=\"" . $HTTPQuery->genURI($id,"Archiver::display_day") . "\">Archiver day</a>";
                    break;
                default :
                    break;
            }
        }

        public static function description(){
            return "Entry archiver";
        }

        public static function getState(){
            return array(
                self::$static_name . "::year",
                self::$static_name . "::month",
                self::$static_name . "::day",
                self::$static_name . "::display_year",
                self::$static_name . "::display_month",
                self::$static_name . "::display_day"
            );
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::year",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::month",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::day",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::display_year",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::display_month",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::display_day",$user_id,"MENU");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::year",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::month",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::day",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::display_year",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::display_month",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::display_day",$user_id);
        }
    }
?>
