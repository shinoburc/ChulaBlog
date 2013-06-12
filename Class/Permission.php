<?php
/**
 * Permission
 * @package ChulaBlog
 * @subpackage ChulaClass
 * @author MIYAZATO Shinobu <shinobu@users.sourceforge.jp>
 * @version $Id: Permission.php,v 1.2 2005/02/12 04:52:59 shinobu Exp $
 */
class Permission{
    private $prefix;
    private $u_mask;
    private $g_mask;

    /**
     * Constructor
     *
     * @param string    prefix for permission identified
     * @param integer   u_mask
     * @param integer   g_mask
     * @return void
     * @see Notification
     */
    function __construct($prefix = NULL,$u_mask = 0,$g_mask = 0){
            if(is_null($prefix)){
                    return NULL;
            }
            $this->prefix = $prefix;
            $this->u_mask = $u_mask;
            $this->g_mask = $g_mask;
    }

    /**
     * read pemission status.
     *
     * @param string    target
     * @return boolean  true if permission exists.
     */
    public function read_permission($target){
    }

    /**
     * set user permission
     *
     * @param string    target
     * @param integer   u_mask
     * @return void
     */
    public function add_user_permission($target,$u_mask,){
    }

    /**
     * set group permission
     *
     * @param string    target
     * @param integer   g_mask
     * @return void
     */
    public function add_group_permission($target,$u_mask,){
    }
}
?>
