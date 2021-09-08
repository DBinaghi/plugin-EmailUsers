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
	
	public function getRecipientsCount($message_id)
	{
		if ($message_id == '') return false;
		$db = get_db();
		$sql = "SELECT * FROM {$db->EmailUsersRecipient} WHERE message_id = " . $message_id;
		return $db->query($sql)->rowCount();		
	}
	
	public function getRecipientRolesCount($message_id)
	{
		if ($message_id == '') return false;
		$db = get_db();
		$recipientRolesCount = array();
		$sql = "SELECT role, COUNT(*) as total FROM {$db->EmailUsersRecipient} WHERE message_id = " . $message_id . " GROUP BY role";
		$rows = $db->fetchAll($sql);
		foreach ($rows as $row) {
			$recipientRolesCount[] = __(ucfirst($row['role'])) . " (" . $row['total'] . ")";
		}
		return implode(', ', $recipientRolesCount);
	}
}
