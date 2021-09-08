<?php
/**
 * Email Users
 *
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Email Users plugin.
 */
class EmailUsersPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
	protected $_hooks = array(
		'install',
		'uninstall',
		'initialize',
		'define_acl',
		'config',
		'config_form'
	);

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * Install the plugin.
     */
	public function hookInstall()
	{
        $db = $this->_db;
        $sql = "
            CREATE TABLE IF NOT EXISTS {$db->EmailUsersMessage} (
				`id` INT NOT NULL AUTO_INCREMENT,
				`sender_id` INT NOT NULL,
				`subject` VARCHAR(255) NOT NULL,
				`text` BLOB NOT NULL,
				`html` TINYINT(1),
				`priority` TINYINT(1),
				`datetime_sent` DATETIME NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
        ";
        $db->query($sql);

        $sql = "
            CREATE TABLE IF NOT EXISTS {$db->EmailUsersRecipient} (
				`message_id` INT NOT NULL,
				`user_id` INT NOT NULL,
				`role` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`message_id`,`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
        ";
        $db->query($sql);

		set_option('email_users_composition_subject_prefix', '');	
		set_option('email_users_composition_header', '');
		set_option('email_users_composition_footer', '');
		set_option('email_users_composition_use_html', '1');
	}

    /**
     * Uninstall the plugin.
     */
	public function hookUninstall()
	{
        $db = $this->_db;
        $db->query("DROP TABLE IF EXISTS {$db->EmailUsersMessage}");
        $db->query("DROP TABLE IF EXISTS {$db->EmailUsersRecipient}");

		delete_option('email_users_composition_subject_prefix');
		delete_option('email_users_composition_header');
		delete_option('email_users_composition_footer');
		delete_option('email_users_composition_use_html');
	 }

    /**
     * Add the translations.
     */
	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');
	}

    /**
     * Define the ACL.
     * 
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        
        $indexResource = new Zend_Acl_Resource('EmailUsers_Index');
        $acl->add($indexResource);

        $acl->allow(array('super', 'admin'), array('EmailUsers_Index'));
    }

	/**
     * Shows plugin configuration page.
     */
	public function hookConfigForm()
	{
		include 'config_form.php';
	}

    /**
     * Handle the config form.
     */
	public function hookConfig($args)
	{
		$post = $args['post'];
		set_option('email_users_composition_subject_prefix', $post['email_users_composition_subject_prefix']);
		set_option('email_users_composition_header', $post['email_users_composition_header']);
		set_option('email_users_composition_footer', $post['email_users_composition_footer']);
		set_option('email_users_composition_use_html', $post['email_users_composition_use_html']);
	}
	
    /**
     * Add the Email Users link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('E-mail Users'),
            'uri' => url('email-users'),
            'resource' => 'EmailUsers_Index',
            'privilege' => 'browse'
        );
        return $nav;
    }
}
