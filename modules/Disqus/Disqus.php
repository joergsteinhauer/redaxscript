<?php
namespace Redaxscript\Modules\Disqus;

use Redaxscript\Html;
use Redaxscript\Registry;

/**
 * replace comments with disqus
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

class Disqus extends Config
{
	/**
	 * array of the module
	 *
	 * @var array
	 */

	protected static $_moduleArray = array(
		'name' => 'Disqus',
		'alias' => 'Disqus',
		'author' => 'Redaxmedia',
		'description' => 'Replace comments with disqus',
		'version' => '2.6.0'
	);

	/**
	 * loaderStart
	 *
	 * @since 2.2.0
	 */

	public static function loaderStart()
	{
		if (Registry::get('article'))
		{
			global $loader_modules_scripts;
			$loader_modules_scripts[] = 'modules/Disqus/scripts/init.js';
		}
	}

	/**
	 * renderStart
	 *
	 * @since 2.2.0
	 */

	public static function renderStart()
	{
		if (Registry::get('article'))
		{
			Registry::set('commentReplace', true);
		}
	}

	/**
	 * commentReplace
	 *
	 * @since 2.2.0
	*/

	public static function commentReplace()
	{
		$boxElement = new Html\Element();
		$boxElement->init('div', array(
			'id' => self::$_config['id']
		));
		$scriptElement = new Html\Element();
		$scriptElement->init('script', array(
			'src' => self::$_config['url']
		));

		/* collect output */

		$output = $boxElement . $scriptElement;
		echo $output;
	}
}
