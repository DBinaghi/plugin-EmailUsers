<?php
/**
 * Email Users
 *
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Email Users message record class.
 *
 * @package EmailUsers
 */
class EmailUsersMessage extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
	public $sender_id;
	public $subject;
	public $text;
	public $html;
	public $priority;
	public $datetime_sent;

    public function getRecordUrl($action = 'show')
    {
        return array('module' => 'email-users', 'controller' => 'index', 
                     'action' => $action, 'id' => $this->id);
    }

	public function getResourceId()
	{
		return 'EmailUsers_Message';
	}
}
