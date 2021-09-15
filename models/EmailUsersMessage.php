<?php
/**
 * E-mail Users
 *
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The E-mail Users message record class.
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
	public $created;
	public $sent;

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
	
	public function getRecipientRolesCount($message_id, $verbose=false)
	{
		if ($message_id == '') return false;
		$db = get_db();
		$recipientRolesCount = array();
		if ($verbose) {
			$sql = "SELECT recipients.role, users.name FROM {$db->EmailUsersRecipient} AS recipients RIGHT OUTER JOIN {$db->User} AS users ON recipients.user_id = users.id WHERE message_id = " . $message_id . " ORDER BY recipients.role, users.name ASC";
		} else {
			$sql = "SELECT role, COUNT(*) as total FROM {$db->EmailUsersRecipient} WHERE message_id = " . $message_id . " GROUP BY role";
		}
		$rows = $db->fetchAll($sql);
		if ($verbose) {
			$role = '';
			foreach ($rows as $row) {
				if ($row['role'] == '') {
					$role = $row['role'];
					$users = array();
				} elseif ($row['role'] != $role) {
					if (count($users) > 0) $recipientRolesCount[] = __(ucfirst($role)) . ' (' . count($users) . '): ' . implode(', ', $users);
					$role = $row['role'];
					$users = array();
				}
				$users[] = $row['name'];
			}
			if (count($users) > 0) $recipientRolesCount[] = __(ucfirst($role)) . ' (' . count($users) . '): ' . implode(', ', $users);
			return implode('<br>', $recipientRolesCount);
		} else {
			foreach ($rows as $row) {
				$recipientRolesCount[] = __(ucfirst($row['role'])) . " (" . $row['total'] . ")";
			}
			return implode('<br>', $recipientRolesCount);
		}
	}
}
