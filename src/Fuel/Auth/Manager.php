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
	protected $config = array(
		'use_all_drivers' => false,
	);

	/**
	 * @var  array  loaded auth drivers
	 */
	protected $drivers = array();

	/**
	 * @var  array  supported global methods, with driver and return type
	 */
	protected $methods = array();

	/**
	 * @var  array  errors picked up in the last driver call
	 */
	protected $lastErrors = array();

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
	public function __call($method, $args)
	{
		if (isset($this->methods[$method]))
		{
			// reset the last error array
			$this->lastErrors = array();

			// get the driver type
			$type = $this->methods[$method];

			// and process the call
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
					// store the exception
					$this->lastErrors[$name] = $e;

					// and reset the result
					$result[$name] = false;
				}
			}

			return $result;
		}

		// we don't know or support this method
		throw new \ErrorException('Method "Fuel\Auth\Manager::'.$method.'()" does not exist.');
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
		if (($driverType = $driver->getType()) != $type)
		{
			throw new AuthException('Auth driver error: "'.$name.'" is a "'.$driverType.'" instead of a "'.$type.'" driver.');
		}

		// link this driver to it's manager
		$driver->setManager($this);

		// is this the first driver loaded for this type?
		if ( ! isset($this->drivers[$type]))
		{
			// import all methods exported by the base class of this driver type
			$this->methods = \Arr::merge($this->methods, array_fill_keys($driver->getMethods(), $type));
		}

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
	 * Return the errors detected in the last driver call
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function lastErrors()
	{
		return $this->lastErrors;
	}

	/*--------------------------------------------------------------------------
	 * Custom user driver methods
	 *------------------------------------------------------------------------*/

	/*--------------------------------------------------------------------------
	 * Custom group driver methods
	 *------------------------------------------------------------------------*/

	/*--------------------------------------------------------------------------
	 * Custom ACL driver methods
	 *------------------------------------------------------------------------*/
}
