<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth;

/**
 * Auth manager class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Manager
{
	/**
	 * @var  array  default auth configuration
	 */
	protected $config = array();

	/**
	 * @var  array  loaded auth drivers
	 */
	protected $drivers = array(
		'storage' => array(),
		'user' => array(),
		'group' => array(),
		'acl' => array(),
	);

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = array())
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}

	/**
	 * add a new driver
	 *
	 * @param  string  $type    the type of driver added
	 * @param  string  $name    the name of the driver added
	 * @param  string  $driver  the driver instance
	 *
	 * @since 2.0.0
	 */
	public function addDriver($type, $name, Driver $driver)
	{
		// make sure it's the correct driver for this type
		$base = 'Fuel\\Auth\\'.ucfirst($type).'\\Base';
		if ( ! $driver instanceOf $base)
		{
			throw new AuthException('Auth "'.$type.'.'.$name.'" driver does not extend "'.$base.'"');
		}

		// link this driver to it's manager
		$driver->setManager($this);

		// store the driver
		$this->drivers[$type][$name] = $driver;
	}

	/**
	 * get a specific driver instance
	 *
	 * @param  string  $type    the type of driver to get
	 * @param  string  $name    the name of the driver to get
	 *
	 * @return  mixed  driver instance, or null if not found
	 *
	 * @since 2.0.0
	 */
	public function getDriver($type, $name = null)
	{
		// if it exists, remove the driver
		return isset($this->drivers[$type][$name]) ? $this->drivers[$type][$name] : null;
	}

	/**
	 * remove an existing driver
	 *
	 * @param  string  $type    the type of driver to be removed
	 * @param  string  $name    the name of the driver to be removed
	 *
	 * @return  boolean  whether or not the driver was removed
	 *
	 * @since 2.0.0
	 */
	public function removeDriver($type, $name)
	{
		// if it exists, remove the driver
		if (isset($this->drivers[$type][$name]))
		{
			unset($this->drivers[$type][$name]);
			return true;
		}

		return false;
	}

	/**
	 * get a configuration item
	 *
	 * @param  string  $key      the config key to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getConfig($key = null, $default = null)
	{
		return func_num_args() ? \Arr::get($this->config, $key, $default) : $this->config;
	}

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function check()
	{
		$loggedin = false;

		// loop over the defined user drivers
		foreach ($this->drivers['user'] as $driver)
		{
			// attempt a login with this driver
			if ($driver->isLoggedIn() or $driver->check())
			{
				// mark the success
				$loggedin = true;

				// if we don't have to try all, bail out now
				if ($this->getConfig('use_all_drivers', false) === false)
				{
					break;
				}
			}
		}

		// return the result
		return $loggedin;
	}

	/**
	 * Login user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function login($user = '', $password = '')
	{
		$loggedin = false;

		// make sure nobody's logged in
		$this->logout();

		// loop over the defined user drivers
		foreach ($this->drivers['user'] as $driver)
		{
			// attempt a login with this driver
			if ($driver->login($user, $password))
			{
				// mark the success
				$loggedin = true;

				// if we don't have to try all, bail out now
				if ($this->getConfig('use_all_drivers', false) === false)
				{
					break;
				}
			}
		}

		// return the result
		return $loggedin;
	}

	/**
	 * Logout user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		$loggedout = false;

		// loop over the defined user drivers
		foreach ($this->drivers['user'] as $driver)
		{
			// logout
			if ($driver->logout())
			{
				// mark the success
				$loggedout = true;

				// if we don't have to try all, bail out now
				if ($this->getConfig('use_all_drivers', false) === false)
				{
					break;
				}
			}
		}

		// return the result
		return $loggedout;
	}
}
