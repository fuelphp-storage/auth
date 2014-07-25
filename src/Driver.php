<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth;

use Fuel\Common\Arr;

/**
 * Auth base driver class.
 *
 * It is extended by all driver base classes, and provides common methods and
 * prototypes for all Auth drivers
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Driver
{
	/**
	 * @var  array  default driver configuration
	 */
	protected $config = [];

	/**
	 * @var  Manager  this drivers manager instance
	 */
	protected $manager;

	/**
	 * @var  bool  Whether or not this driver allows updates
	 */
	protected $isReadOnly = false;

	/**
	 * @var  bool  Whether or not this driver supports concurrency
	 */
	protected $hasConcurrency = true;

	/**
	 * Global driver constructor
	 */
	public function __construct(array $config = [])
	{
		// update the default config with whatever was passed
		$this->config = Arr::merge($this->config, $config);
	}

	/**
	 * Set this drivers manager instance
	 *
	 * @since 2.0.0
	 */
	public function setManager(Manager $manager)
	{
		// store the manager instance
		$this->manager = $manager;
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
		return func_num_args() ? Arr::get($this->config, $key, $default) : $this->config;
	}

	/**
	 * Set a configation value
	 *
	 * @param   mixed   $key    The dot-notated key to set or array of keys
	 * @param   mixed   $value  The value
	 * @return  void
	 *
	 * @since 2.0.0
	 */
	public function setConfig($key, $value)
	{
		Arr::set($this->config, $key, $value);
	}

	/**
	 * get the readonly status of this driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function isReadOnly()
	{
		return $this->isReadOnly;
	}

	/**
	 * get the concurrency status of this driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasConcurrency()
	{
		return $this->hasConcurrency;
	}

	/**
	 * Called from the Auth manager instance to trigger the driver on
	 * specific events. It is up to the driver to deal with that trigger
	 *
	 * @param  string  named hook trigger
	 * @param  string  any arguments for the hook method
	 *
	 * @return  boolean  true if the call succeeded, false if it didn't
	 *
	 * @since 2.0.0
	 */
	public function callHook($hook, $args)
	{
		// by default, drivers don't define hooks
		return false;
	}

}
