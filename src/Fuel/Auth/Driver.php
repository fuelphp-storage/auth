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

use Fuel\Auth\AuthException;

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
	 * @var  Manager  this drivers manager instance
	 */
	protected $manager;

	/**
	 * @var  bool  Whether or not this driver allows updates
	 */
	protected $readOnly = false;

	/**
	 * @var  bool  Whether or not this driver has guest support
	 */
	protected $guestSupport = false;

	/**
	 * Set this drivers manager instance
	 */
	public function setManager(Manager $manager)
	{
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
		return func_num_args() ? \Arr::get($this->config, $key, $default) : $this->config;
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
		return $this->readOnly;
	}

	/**
	 * Check if this driver has guest support
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasGuestSupport()
	{
		return $this->guestSupport;
	}

}
