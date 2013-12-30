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
	 * @var  array  supported global methods, with driver and return type
	 */
	protected $methods = array(
		// user drivers
		'check'       => array('user' => 'bool'),
		'validate'    => array('user' => 'bool'),
		'login'       => array('user' => 'bool'),
		'forceLogin'  => array('user' => 'bool'),
		'isLoggedIn'  => array('user' => 'bool'),
		'logout'      => array('user' => 'bool'),
		'create'      => array('user' => 'array'),
		'update'      => array('user' => 'array'),
		'password'    => array('user' => 'array'),
		'reset'       => array('user' => 'array'),
		'delete'      => array('user' => 'array'),
		// group drivers
		// acl drivers
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
	 * Capture calls to driver methods, and distribute them after checking...
	 *
	 * @since 2.0.0
	 */
	public function __call($name, $args)
	{
		if (isset($this->methods[$name]))
		{
			$type = key($this->methods[$name]);
			switch ($this->methods[$name][$type])
			{
				case 'array':
					return $this->returnAsArray($type, $name, $args);
				break;

				case 'bool':
					return $this->returnAsBool($type, $name, $args);
				break;

				default:
					throw new \ErrorException('Unknown return type "'.$this->methods[$name][$type].'" defined for "Fuel\Auth\Manager::'.$name.'()".');
			}
		}

		// we don't know or support this method
		throw new \ErrorException('Method "Fuel\Auth\Manager::'.$name.'()" does not exist.');
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

	/*--------------------------------------------------------------------------
	 * User driver methods
	 *------------------------------------------------------------------------*/

	/**
	 * Get a user data item
	 *
	 * @param  string  $key      the field to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key = null, $value = null)
	{
		die('TODO: Auth-Manager::get()');
	}

	/*--------------------------------------------------------------------------
	 * Group driver methods
	 *------------------------------------------------------------------------*/

	/*--------------------------------------------------------------------------
	 * ACL driver methods
	 *------------------------------------------------------------------------*/

	/*--------------------------------------------------------------------------
	 * Internal methods
	 *------------------------------------------------------------------------*/

	/**
	 * Calls driver methods with a consolidated boolean return value
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	protected function returnAsBool($type, $method, $args)
	{
		$result = false;

		// loop over the defined drivers
		foreach ($this->drivers[$type] as $driver)
		{
			// call the driver method
			try
			{
				if (call_user_func_array(array($driver, $method), $args))
				{
					// mark the success
					$result = true;

					// if we don't have to try all, bail out now
					if ($this->getConfig('use_all_drivers', false) === false)
					{
						break;
					}
				}
			}
			catch (AuthException $e)
			{
				$result[$name] = false;
			}
		}

		// return the result
		return $result;
	}

	/**
	 * Calls driver methods with per-driver results, as assoc array
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	protected function returnAsArray($type, $method, $args)
	{
		$result = array();

		// loop over the defined drivers
		foreach ($this->drivers[$type] as $name => $driver)
		{
			// call the driver method
			try
			{
				if ($result[$name] = call_user_func_array(array($driver, $method), $args))
				{
					// if we don't have to try all, bail out now
					if ($this->getConfig('use_all_drivers', false) === false)
					{
						break;
					}
				}
			}
			catch (AuthException $e)
			{
				$result[$name] = false;
			}
		}

		// return the result
		return $result;
	}
}
