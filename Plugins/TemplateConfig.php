<?php

    /**
     * Template Config
     * state : TemplateConfig::link
     *         TemplateConfig::config
     *
     * @package ChulaBlog
     * @subpackage ChulaPlugin
     * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
     * @version $Id: TemplateConfig.php,v 1.6 2005/05/05 14:09:44 shinobu Exp $
     */
    class TemplateConfig extends ChulaPlugin{
        private $name = "TemplateConfig";
        private static $static_name = "TemplateConfig";

        /* Constructor */
        function __construct(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $target = $HTTPQuery->getSd(0);
            $command = $HTTPQuery->getSd(1);
            $key = $HTTPQuery->getSd(2);

            $NotificationCenter = $im->new_once("NotificationCenter");
            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::link", $this->name,"link");
            $NotificationCenter->setObserver("HTMLHeaderNotification",$this->name,"html_header");

            switch($target){
                case $this->name :
                    switch($command){
                        case "config" :
                            $NotificationCenter = $im->new_once("NotificationCenter");
                            $NotificationCenter->setObserver("ChulaViewNotification::" . $this->name . "::config", $this->name,"config");
                            break;
                        default :
                            break;
                    }
                    break;
                default :
                    break;
            }
        }

        public function config($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            switch($HTTPQuery->getSafePost($this->name . "_mode")){
                case "display_select_template_form" :
                    $this->display_select_template_form();
                    break;
                case "display_text_edit_form" :
                    $this->display_text_edit_form();
                    break;
                case "display_graphical_edit_form" :
                    $this->display_graphical_edit_form();
                    break;
                case "select_template" :
                    $this->select_template();
                    break;
                case "text_edit" :
                    $this->text_edit();
                    break;
                case "graphical_edit" :
                    $this->text_edit();
                    break;
                default :
                    $this->display_menu();
                    break;
            }
        }

        public function display_menu(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"select template\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"display_select_template_form\">";
            echo "</form>";

            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"edit template(text)\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"display_text_edit_form\">";
            echo "</form>";

            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"edit template(graphical)\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"display_graphical_edit_form\">";
            echo "</form>";
        }

        public function display_select_template_form(){
            global $im;
            $Config = $im->new_once("Config");
            $HTTPQuery = $im->new_once("HTTPQuery");
            $Session = $im->new_once("Session");

            $selected_template_name = ChulaDBIO::getTemplateName($Session->getUserId());
            $template_dir_info = scandir($Config->get_value("ChulaBlog","templates_dir"));

            echo "<p>current template : $selected_template_name";

            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"select_template\">";
            echo "<select name=\"" . $this->name . "_template_file_name\">";
            foreach($template_dir_info as $file){
                if(!preg_match("/.tpl$/",$file)){
                    continue;
                }
                if($file == $selected_template_name){
                    echo "<option value=\"" . $file . "\" selected>" . $file;
                } else {
                    echo "<option value=\"" . $file . "\">" . $file;
                }
            }
            echo "</select>";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"update\" >";
            echo "</form>";
        }

        public function display_text_edit_form(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            if(is_null($HTTPQuery->getId())){
                $Config = $im->new_once("Config");
                $id = $Config->get_value("ChulaBlog","guest_user_id");
            } else {
                $id = $HTTPQuery->getId();
            }
            $template_name = ChulaDBIO::getTemplateName("$id");
            $template_file = ChulaDBIO::getTemplate("$id");
            $file = file_get_contents($template_file);

            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "Template Name : <input type=\"text\" name=\"" . $this->name . "_template_file_name\" value=\"" . $template_name . "\">";
            echo "<textarea cols=\"60\" rows=\"20\" name=\"" . $this->name . "_template_string\">";
            echo $file;
            echo "</textarea>";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"add\" >";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"text_edit\">";
            echo "</form>";
        }

        public function display_graphical_edit_form(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");

            if(is_null($HTTPQuery->getId())){
                $Config = $im->new_once("Config");
                $id = $Config->get_value("ChulaBlog","guest_user_id");
            } else {
                $id = $HTTPQuery->getId();
            }
            $template_name = ChulaDBIO::getTemplateName("$id");
            $template_file = ChulaDBIO::getTemplate("$id");
            $file = file_get_contents($template_file);

            echo "<iframe id=\"f\" onload=\"init()\">";
            echo "<textarea rows=30 cols=80>" . $file . "</textarea>";
            echo "</iframe>";
            echo "comming soon";
        }

        public function select_template(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $Session = $im->new_once("Session");
            $template_file = $HTTPQuery->getPost($this->name . "_template_file_name");
            if(!$this->template_file_name_check($template_file)){
                echo "<p>invalid template name<p>";
                $this->display_text_edit_form();
                return;
            }
            ChulaDBIO::setTemplate($Session->getUserId(),$template_file);
            echo "<p>Change Template.The setting becomes effective from the next access.";
            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"reload now\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"display_select_template_form\">";
            echo "</form>";
        }

        public function text_edit(){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            $Config = $im->new_once("Config");
            $Session = $im->new_once("Session");
            $template_file = $HTTPQuery->getPost($this->name . "_template_file_name");
            $template_dir = $Config->get_value("ChulaBlog","templates_dir");

            if(!$this->template_file_name_check($template_file)){
                echo "<p>invalid template name<p>";
                $this->display_text_edit_form();
                return;
            }

            if(file_exists($template_dir . "/" . $template_file)){
                echo "<p>template exits<p>";
                $this->display_text_edit_form();
                return;
            }

            file_put_contents($template_dir . "/" . $template_file,$HTTPQuery->getPost($this->name . "_template_string"));

            if(is_null($HTTPQuery->getId())){
                $id = $Config->get_value("ChulaBlog","guest_user_id");
            } else {
                $id = $HTTPQuery->getId();
            }
            ChulaDBIO::setTemplate($id,$template_file);

            echo "<p>Change Template.The setting becomes effective from the next access.";
            echo "<p>";
            echo "<form method=\"POST\" action=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">";
            echo "<input type=\"submit\" name=\"" . $this->name . "_submit\" value=\"reload now\">";
            echo "<input type=\"hidden\" name=\"" . $this->name . "_mode\" value=\"display_text_edit_form\">";
            echo "</form>";
        }

        /**
         * check template file name
         *
         * @param   string      template file name
         * @return  boolean     true if valid template file name
         */
            function template_file_name_check($template_file_name)
            {
                    if(is_null($template_file_name)){
                        return FALSE;
                    } else if(preg_match("/^\\.+$/",$template_file_name) or preg_match("/^\\.+\\" . "/" . "/",$template_file_name)){
                        return FALSE;
                    } else if ((substr($template_file_name, 0, 1) == "/") or preg_match("/[A-Za-z]:\\" . "/" . "/",$template_file_name)){
                        return FALSE;
                    } else {
                        return TRUE;
                    }
            }

        public function link($notification){
            global $im;
            $HTTPQuery = $im->new_once("HTTPQuery");
            echo "<a href=\"" . $HTTPQuery->genURI($HTTPQuery->getId(),$this->name . "::config") . "\">" . $this->name . "</a>";
        }

        private function template_file_name_validation($template_file_name){
        }

        public function html_header($notification){
            echo "<script src=\"js/design_mode.js\"></script>";
        }

        public static function description(){
            return "Template Configurator";
        }

        public static function getState(){
            return array(
                self::$static_name . "::link",
                self::$static_name . "::config"
            );
        }

        public static function plugInTrigger($user_id){
            ChulaDBIO::insertState(self::$static_name . "::link",$user_id,"MENU");
            ChulaDBIO::insertState(self::$static_name . "::config",$user_id,"MAINPANE");
        }

        public static function plugOutTrigger($user_id){
            ChulaDBIO::deleteState(self::$static_name . "::link",$user_id);
            ChulaDBIO::deleteState(self::$static_name . "::config",$user_id);
        }

       /**
        * This plugin is for owner only.
        * @param  void
        * @return  boolean true
        */
        public static function ownerOnly(){
            return TRUE;
        }
    }
?>
