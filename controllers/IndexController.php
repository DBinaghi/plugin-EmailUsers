<?php
/**
 * Email Users
 *
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Email Users index controller class.
 *
 * @package EmailUsers
 */
class EmailUsers_IndexController extends Omeka_Controller_AbstractActionController
{	
	public function init()
	{
		// Set the model class so this controller can perform some functions, 
		// such as $this->findById()
		$this->_helper->db->setDefaultModelName('EmailUsersMessage');
	}

	protected function _getBrowseDefaultSort()
	{
		return array('created', 'd');
	}
	
	public function indexAction()
	{
		// Always go to browse.
		$this->_helper->redirector('browse');
		return;
	}
	
	public function editAction()
	{
		// Check if message was submitted
		if ($this->getRequest()->isPost()) {
			if ($this->getRequest()->getParam('email-users-id') != '') {
				// Retrieve existing message
				$message = $this->_helper->db->getTable('EmailUsersMessage')->find($this->getRequest()->getParam('email-users-id'));
			}
			
			// If message already sent, then no other action is possible
			if ($message->sent != '') $this->_helper->redirector('browse');
			
			// If not set, create a new message
			if (!isset($message)) $message = new EmailUsersMessage;

			// Set the created by user ID.
			$message->sender_id = current_user()->id;

			// Get form values
			$recipient_roles = $this->getRequest()->getParam('email-users-recipients');
			$message->subject = $this->getRequest()->getParam('email-users-subject');
			$message->text = $this->getRequest()->getParam('email-users-text');
			$message->priority = $this->getRequest()->getParam('email-users-priority');
			$message->html = ($email_header != '' || $email_footer != '' || get_option('email_users_composition_use_html'));
			$action = ($this->getRequest()->getParam('email-users-send') != '' ? 'send' : 'save');
			
			// Validate message
			$error = $this->validateMessage($message);
			if ($error != '') {
				$this->_helper->flashMessenger($error);
				return;
			}

			// Save message to db
			if ($this->getRequest()->getParam('email-users-id') != '') {
				// Message is a draft being edited
				$message->id = $this->getRequest()->getParam('email-users-id');
				$message->save();
				$message_id = $message->id;
			} else {
				// Message is a new one
				$message->created = date('Y-m-d H:i:s');
				$message->save();
			
				// Retrieve id of message just saved
				$message->id = $this->getMessageIdByDatetime($message->created);
			}	

			// If draft, return to browse
			if ($action == 'save') {
				// Redirect to browse
				$this->_helper->flashMessenger(__('The draft was succesfully saved. You\'ll be able to edit or send it at any time.'), 'success');
				$this->_helper->redirector('browse');
			} else {
				// Add default recipient roles
				$recipient_roles[] = 'super';
				$recipient_roles[] = 'admin';
				
				// Retrieve recipients information
				$recipients = array();
				$users = get_db()->getTable('User')->findAll();
				foreach ($users as $user) {
					if (in_array($user->role, $recipient_roles)) {
						$recipients[] = $user;
					}
				}
				
				// Compose message
				$email = new Zend_Mail('utf-8');
				// Set priority
				if ($message->priority) {
					$email->addHeader('X-Priority', '1');
					$email->addHeader('X-MSMail-Priority', 'High');
					$email->addHeader('Importance', 'High');				
				}
				// Set sender
				$email->setFrom(get_option('administrator_email'), get_option('site_title'));
				// Set subject
				$email_prefix = get_option('email_users_composition_subject_prefix');
				if ($email_prefix != '') {
					$message->subject = $email_prefix . $message->subject;
				}
				$email->setSubject($message->subject);
				// Set text
				$email_header = get_option('email_users_composition_header');
				$email_footer = get_option('email_users_composition_footer');
				if ($email_header != '' || $email_footer != '') {
					$message->text = $email_header . $message->text . $email_footer;
					$email->setBodyHtml($message->text);
					$message->html = true;
				} elseif (get_option('email_users_composition_use_html')) {
					$email->setBodyHtml($message->text);
					$message->html = true;
				} else {
					$email->setBodyText($message->text);
					$message->html = false;
				}
				// Set datetime
				$message->sent = date('Y-m-d H:i:s');
				
				// Update message on db
				$message->save();

				// Send message
				foreach ($recipients as $recipient) {
					$email->addTo($recipient->email, $recipient->name);
					$email->send();
					$email->clearRecipients();
					$this->saveMessageRecipient($message_id, $recipient->id, $recipient->role);
				}

				// Redirect to browse
				$this->_helper->flashMessenger(__(plural('The new e-mail was succesfully sent to one recipient.', 'The new e-mail was succesfully sent to %s recipients.', count($recipients)), count($recipients)), 'success');
				$this->_helper->redirector('browse');
			}
		} else {
			// Get the page object from the passed ID.
			$messageId = $this->_getParam('id');
			if ($messageId != '') {
				$message = $this->_helper->db->getTable('EmailUsersMessage')->find($messageId);
			}
			
			if (!isset($message)) $message = new EmailUsersMessage;
			
			// If message already sent, then no other action is possible
			if ($message->sent != '') $this->_helper->redirector('browse');

			// Set the message object to the view.
			$this->view->email_users_message = $message;
		}
	}

	public function showAction()
	{
		// Get the page object from the passed ID.
		$messageId = $this->_getParam('id');
		$message = $this->_helper->db->getTable('EmailUsersMessage')->find($messageId);

		// Set the message object to the view.
		$this->view->email_users_message = $message;
	}
	
	public function getMessageIdByDatetime($datetime)
	{
		$message = $this->_helper->db->getTable('EmailUsersMessage')->findBy(array('created' => $datetime));
		if (!is_null($message)) return $message[0]['id'];
	}
	
	public function saveMessageRecipient($message_id, $recipient_id, $role)
	{
		if ($message_id == '' || $recipient_id == '' || $role == '') return false;
		$db = get_db();
		$sql = "INSERT INTO {$db->EmailUsersRecipient} VALUES (" . $message_id . ", " . $recipient_id . ", '" . $role . "')";
		$db->query($sql);
		return true;
	}
	
	public function validateMessage($message)
	{
		if (empty($message->subject)) {
			return __('A subject must be supplied for the e-mail.');
		} elseif (strlen($message->subject) > 255) {
			return __('The e-mail subject must be no longer than 255 characters.');
		}
		if (empty($message->text)) {
			return __('A text must be supplied for the e-mail.');
		} elseif (strlen($message->text) < 10) {
			return __('The e-mail text must be at least 10 characters long.');
		}
		
		return null;
	}
}
