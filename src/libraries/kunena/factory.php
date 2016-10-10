<?php
/**
 * Kunena Component
 * @package        Kunena.Framework
 *
 * @copyright      Copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/
defined('_JEXEC') or die();

/**
 * Class KunenaFactory
 * @since Kunena
 */
abstract class KunenaFactory
{
	/**
	 * @var null
	 * @since Kunena
	 */
	static $session = null;

	/**
	 * Get a Kunena configuration object
	 *
	 * Returns the global {@link KunenaConfig} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaConfig
	 * @since Kunena
	 */
	public static function getConfig()
	{
		return KunenaConfig::getInstance();
	}

	/**
	 * Get a Kunena template object
	 *
	 * Returns the global {@link KunenaTemplate} object, only creating it if it doesn't already exist.
	 *
	 * @param   string $name
	 *
	 * @return KunenaTemplate
	 * @since Kunena
	 */
	public static function getTemplate($name = null)
	{
		return KunenaTemplate::getInstance($name);
	}

	/**
	 * Get a Kunena template object
	 *
	 * Returns the global {@link KunenaTemplate} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaAdminTemplate|KunenaTemplate
	 * @since Kunena
	 */
	public static function getAdminTemplate()
	{
		require_once KPATH_ADMIN . '/template/template.php';
		$template = new KunenaAdminTemplate;

		return $template;
	}

	/**
	 * Get Kunena user object
	 *
	 * Returns the global {@link KunenaUser} object, only creating it if it doesn't already exist.
	 *
	 * @param   int  $id The user to load - Can be an integer or string - If string, it is converted to Id automatically.
	 * @param   bool $reload
	 *
	 * @return KunenaUser
	 * @since Kunena
	 */
	public static function getUser($id = null, $reload = false)
	{
		return KunenaUserHelper::get($id, $reload);
	}

	/**
	 * Get Kunena session object
	 *
	 * Returns the global {@link KunenaSession} object, only creating it if it doesn't already exist.
	 *
	 * @param   array|bool $update An array containing session options
	 *
	 * @return KunenaSession
	 * @since Kunena
	 */
	public static function getSession($update = false)
	{
		if (!is_object(self::$session))
		{
			self::$session = KunenaSession::getInstance($update);
		}

		return self::$session;
	}

	/**
	 * Get Kunena avatar integration object
	 *
	 * Returns the global {@link KunenaAvatar} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaAvatar
	 * @since Kunena
	 */
	public static function getAvatarIntegration()
	{
		return KunenaAvatar::getInstance();
	}

	/**
	 * Get Kunena private message system integration object
	 *
	 * Returns the global {@link KunenaPrivate} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaPrivate
	 * @since Kunena
	 */
	public static function getPrivateMessaging()
	{
		return KunenaPrivate::getInstance();
	}

	/**
	 * Get Kunena activity integration object
	 *
	 * Returns the global {@link KunenaIntegrationActivity} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaIntegrationActivity
	 * @since Kunena
	 */
	public static function getActivityIntegration()
	{
		return KunenaIntegrationActivity::getInstance();
	}

	/**
	 * Get Kunena profile integration object
	 *
	 * Returns the global {@link KunenaProfile} object, only creating it if it doesn't already exist.
	 *
	 * @return KunenaProfile
	 * @since Kunena
	 */
	public static function getProfile()
	{
		return KunenaProfile::getInstance();
	}

	/**
	 * Load Kunena language file
	 *
	 * Helper function for external modules and plugins to load the main Kunena language file(s)
	 *
	 * @param   string $file
	 * @param   string $client
	 *
	 * @return mixed
	 * @since Kunena
	 */
	public static function loadLanguage($file = 'com_kunena', $client = 'site')
	{
		static $loaded = array();
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

		if ($client == 'site')
		{
			$lookup1 = JPATH_SITE;
			$lookup2 = KPATH_SITE;
		}
		else
		{
			$client  = 'admin';
			$lookup1 = JPATH_ADMINISTRATOR;
			$lookup2 = KPATH_ADMIN;
		}

		if (empty($loaded["{$client}/{$file}"]))
		{
			$lang = JFactory::getLanguage();

			$english = false;

			if ($lang->getTag() != 'en-GB' && !JDEBUG && !$lang->getDebug()
				&& !self::getConfig()->get('debug') && self::getConfig()->get('fallback_english')
			)
			{
				$lang->load($file, $lookup2, 'en-GB', true, false);
				$english = true;
			}

			$loaded[$file] = $lang->load($file, $lookup1, null, $english, false)
				|| $lang->load($file, $lookup2, null, $english, false);
		}

		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

		return $loaded[$file];
	}

	/**
	 * @param $lang
	 * @param $filename
	 *
	 * @return boolean
	 * @since Kunena
	 */
	protected static function parseLanguage($lang, $filename)
	{
		if (!is_file($filename))
		{
			return false;
		}

		// Capture hidden PHP errors from the parsing.
		$php_errormsg = null;
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		$contents = file_get_contents($filename);
		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings  = @parse_ini_string($contents);

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		if (!is_array($strings))
		{
			$strings = array();
		}

		$lang->_strings = array_merge($lang->_strings, $strings);

		return !empty($strings);
	}
}
