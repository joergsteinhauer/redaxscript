<?php
namespace Redaxscript;

/**
 * parent class to install the database
 *
 * @since 2.4.0
 *
 * @category Installer
 * @package Redaxscript
 * @author Henry Ruhs
 */

class Installer
{
	/**
	 * instance of the config class
	 *
	 * @var object
	 */

	protected $_config;

	/**
	 * name of the directory
	 *
	 * @var string
	 */

	protected $_directory;

	/**
	 * placeholder for the prefix
	 *
	 * @var string
	 */

	protected $_prefixPlaceholder = '/* {configPrefix} */';

	/**
	 * constructor of the class
	 *
	 * @since 2.6.0
	 *
	 * @param Config $config instance of the config class
	 */

	public function __construct(Config $config)
	{
		$this->_config = $config;
	}

	/**
	 * init the class
	 *
	 * @since 2.6.0
	 *
	 * @param string $directory name of the directory
	 */

	public function init($directory = 'database')
	{
		$this->_directory = $directory;
	}

	/**
	 * create from sql
	 *
	 * @since 2.4.0
	 */

	public function rawCreate()
	{
		$this->_rawExecute('create', $this->_config->get('dbType'));
	}

	/**
	 * drop from sql
	 *
	 * @since 2.4.0
	 */

	public function rawDrop()
	{
		$this->_rawExecute('drop', $this->_config->get('dbType'));
	}

	/**
	 * insert the data
	 *
	 * @since 2.4.0
	 *
	 * @param array $options options of the data
	 */

	public function insertData($options = null)
	{
		$language = Language::getInstance();
		$language->init();

		/* articles */

		Db::forTablePrefix('articles')
			->create()
			->set(array(
				'title' => 'Welcome',
				'alias' => 'welcome',
				'author' => $options['adminUser'],
				'text' => file_get_contents('database/html/articles/welcome.phtml'),
				'language' => '',
				'category' => 1,
				'rank' => 1
			))->save();

		/* categories */

		Db::forTablePrefix('categories')
			->create()
			->set(array(
				'title' => 'Home',
				'alias' => 'home',
				'author' => $options['adminUser'],
				'language' => '',
				'rank' => 1
			))->save();

		/* extras */

		$extrasArray = array(
			'categories' => array(
				'status' => 1,
				'headline' => 1
			),
			'articles' => array(
				'status' => 1,
				'headline' => 1
			),
			'comments' => array(
				'status' => 1,
				'headline' => 1
			),
			'languages' => array(
				'status' => 0,
				'headline' => 1
			),
			'templates' => array(
				'status' => 0,
				'headline' => 1
			),
			'footer' => array(
				'status' => 0,
				'headline' => 0
			)
		);
		$extrasRank = 0;

		/* process extras array */

		foreach ($extrasArray as $key => $value)
		{
			Db::forTablePrefix('extras')
				->create()
				->set(array(
					'title' => ucfirst($key),
					'alias' => $key,
					'author' => $options['adminUser'],
					'text' => file_get_contents('database/html/extras/' . $key . '.phtml'),
					'language' => '',
					'headline' => $value['headline'],
					'status' => $value['status'],
					'rank' => ++$extrasRank
				))->save();
		}

		/* groups */

		Db::forTablePrefix('groups')
			->create()
			->set(array(
				'name' => 'Administrators',
				'alias' => 'administrators',
				'description' => 'Unlimited access',
				'categories' => '1, 2, 3',
				'articles' => '1, 2, 3',
				'extras' => '1, 2, 3',
				'comments' => '1, 2, 3',
				'groups' => '1, 2, 3',
				'users' => '1, 2, 3',
				'modules' => '1, 2, 3',
				'settings' => 1,
				'filter' => 0,
			))->save();
		Db::forTablePrefix('groups')
			->create()
			->set(array(
				'name' => 'Members',
				'alias' => 'members',
				'description' => 'Default members group'
			))->save();

		/* modules */

		if (is_dir('modules/CallHome'))
		{
			$callHome = new Modules\CallHome\CallHome;
			$callHome->install();
		}

		/* settings */

		$settingsArray = array(
			'language' => 'detect',
			'template' => 'default',
			'title' => $language->get('name', '_package'),
			'author' => null,
			'copyright' => null,
			'description' => $language->get('description', '_package'),
			'keywords' => null,
			'robots' => 'all',
			'email' => $options['adminEmail'],
			'subject' => $language->get('name', '_package'),
			'notification' => 0,
			'charset' => 'utf-8',
			'divider' => ' - ',
			'time' => 'H:i',
			'date' => 'd.m.Y',
			'homepage' => '0',
			'limit' => 10,
			'order' => 'asc',
			'pagination' => '1',
			'moderation' => '0',
			'registration' => '1',
			'verification' => '0',
			'reminder' => '1',
			'captcha' => '0',
			'version' => $language->get('version', '_package')
		);

		/* process settings array */

		foreach ($settingsArray as $name => $value)
		{
			Db::forTablePrefix('settings')
				->create()
				->set(array(
					'name' => $name,
					'value' => $value
				))->save();
		}

		/* users */

		$passwordHash = new Hash(Config::getInstance());
		$passwordHash->init($options['adminPassword']);
		Db::forTablePrefix('users')
			->create()
			->set(array(
				'name' => $options['adminName'],
				'user' => $options['adminUser'],
				'password' => $passwordHash->getHash(),
				'email' => $options['adminEmail'],
				'description' => 'God admin',
				'language' => '',
				'groups' => '1'
			))->save();
	}

	/**
	 * execute from sql
	 *
	 * @since 2.4.0
	 *
	 * @param string $action action to process
	 * @param string $type type of the database
	 */

	protected function _rawExecute($action = null, $type = 'mysql')
	{
		$sqlDirectory = new Directory();
		$sqlDirectory->init($this->_directory . '/' . $type . '/' . $action);
		$sqlArray = $sqlDirectory->getArray();

		/* process sql files */

		foreach ($sqlArray as $file)
		{
			$query = file_get_contents($this->_directory . '/' . $type . '/' . $action . '/' . $file);
			if ($query)
			{
				if ($this->_config->get('dbPrefix'))
				{
					$query = str_replace($this->_prefixPlaceholder, $this->_config->get('dbPrefix'), $query);
				}
				Db::rawExecute($query);
			}
		}
	}
}
