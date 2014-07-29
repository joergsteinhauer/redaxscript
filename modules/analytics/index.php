<?php

/**
 * analytics loader start
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Module
 * @author Henry Ruhs
 */

function analytics_loader_start()
{
	if (LOGGED_IN != TOKEN)
	{
		global $loader_modules_scripts;
		$loader_modules_scripts[] = 'modules/analytics/scripts/startup.js';
		$loader_modules_scripts[] = 'modules/analytics/scripts/analytics.js';
	}
}

/**
 * analytics scripts start
 * 
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Module
 * @author Henry Ruhs
 */

function analytics_scripts_start()
{
	if (LOGGED_IN != TOKEN)
	{
		$output = '<script src="//google-analytics.com/ga.js"></script>' . PHP_EOL;
		echo $output;
	}
}
