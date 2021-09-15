<?php
/**
 * E-mail Users
 *
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The E-mail Users index controller class.
 *
 * @package EmailUsers
 */
class EmailUsers_IndexController extends Omeka_Controller_AbstractActionController
{	
	const RECORDS_PER_PAGE_SETTING = 'records_per_page_setting';

	/**
	 * The number of records to browse per page.
	 * 
	 * If this is left null, then results will not paginate. This is partially 
	 * because not every controller will want to paginate records and also to 
	 * avoid BC breaks for plugins.
	 *
	 * Setting this to self::RECORDS_PER_PAGE_SETTING will cause the
	 * admin-configured page limits to be used (which is often what you want).
	 *
	 * @var string
	 */
	protected $_browseRecordsPerPage = self::RECORDS_PER_PAGE_SETTING;

	/**
	 * Controller-wide initialization. Sets the underlying model to use.
	 */
	public function init()
	{
		// Set the model class so this controller can perform some functions, 
		// such as $this->findById()
		$this->_helper->db->setDefaultModelName('EmailUsersMessage');
	}

	/**
	 * Return the default sorting parameters to use when none are specified.
	 *
	 * @return array|null Array of parameters, with the first element being the
	 *  sort_field parameter, and the second (optionally) the sort_dir.
	 */
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

	public function browseAction()
	{
		// Respect only GET parameters when browsing.
		$this->getRequest()->setParamSources(array('_GET'));
		
		// Apply controller-provided default sort parameters
		if (!$this->_getParam('sort_field')) {
			$defaultSort = apply_filters(
				'email_users_messages_browse_default_sort',
				$this->_getBrowseDefaultSort(),
				array('params' => $this->getAllParams())
			);
			if (is_array($defaultSort) && isset($defaultSort[0])) {
				$this->setParam('sort_field', $defaultSort[0]);

				if (isset($defaultSort[1])) {
					$this->setParam('sort_dir', $defaultSort[1]);
				}
			}
		}
		
		$params = $this->getAllParams();
		$recordsPerPage = $this->_getBrowseRecordsPerPage('email_users_messages');
		$currentPage = $this->getParam('page', 1);

		// Get the records filtered to Omeka_Db_Table::applySearchFilters().
		$records = $this->_helper->db->findBy($params, $recordsPerPage, $currentPage);
		$totalRecords = $this->_helper->db->count($params);

		// Add pagination data to the registry. Used by pagination_links().
		if ($recordsPerPage) {
			Zend_Registry::set('pagination', array(
				'page' => $currentPage,
				'per_page' => $recordsPerPage,
				'total_results' => $totalRecords,
			));
		}

		$this->view->assign(array('email_users_messages' => $records, 'total_results' => $totalRecords));
	}
	
	/**
	 * Return the number of records to display per page.
	 *
	 * By default this will read from the _browseRecordsPerPage property, which
	 * in turn defaults to null, disabling pagination. This can be 
	 * overridden in subclasses by redefining the property or this method.
	 *
	 * Setting the property to self::RECORDS_PER_PAGE_SETTING will enable
	 * pagination using the admin-configued page limits.
	 *
	 * @param string|null $pluralName
	 * @return int|null
	 */
	protected function _getBrowseRecordsPerPage($pluralName = null)
	{
		$perPage = $this->_browseRecordsPerPage;

		// Use the user-configured page
		if ($perPage === self::RECORDS_PER_PAGE_SETTING) {
			$options = $this->getFrontController()->getParam('bootstrap')
				->getResource('Options');

			if (is_admin_theme()) {
				$perPage = (int) $options['per_page_admin'];
			} else {
				$perPage = (int) $options['per_page_public'];
			}
		}

		// If users are allowed to modify the # of items displayed per page,
		// then they can pass the 'per_page' query parameter to change that.
		if ($this->_helper->acl->isAllowed('modifyPerPage')
			&& ($queryPerPage = $this->getRequest()->get('per_page'))
		) {
			$perPage = (int) $queryPerPage;
		}

		// Any integer zero or below disables pagination.
		if ($perPage < 1) {
			$perPage = null;
		}

		if ($pluralName) {
			$perPage = apply_filters("{$pluralName}_browse_per_page", $perPage,
				array('controller' => $this));
		}
		return $perPage;
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
			$error = $this->_validateMessage($message);
			if ($error != '') {
				$this->_helper->flashMessenger($error);
				return;
			}

			// Save message to db
			if ($this->getRequest()->getParam('email-users-id') != '') {
				// Message is a draft being edited
				$message->id = $this->getRequest()->getParam('email-users-id');
				$message->save();
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
					// $email->addTo($recipient->email, $recipient->name);
					$email->addTo('admin@bitoteko.it', $recipient->name);
					$email->send();
					$email->clearRecipients();
					$this->_saveMessageRecipient($message->id, $recipient->id, $recipient->role);
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
	
	private function _saveMessageRecipient($message_id, $recipient_id, $role)
	{
		if ($message_id == '' || $recipient_id == '' || $role == '') return false;
		$db = get_db();
		$sql = "INSERT INTO {$db->EmailUsersRecipient} VALUES (" . $message_id . ", " . $recipient_id . ", '" . $role . "')";
		$db->query($sql);
	}
	
	private function _validateMessage($message)
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
