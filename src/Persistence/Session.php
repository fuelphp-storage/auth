<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Persistence;

use Fuel\Session\Manager;

/**
 * Auth Session persistence driver
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Session extends Base
{
	/**
	 * @var  array  session driver configuration
	 */
	protected $config = array();

	/**
	 * @var  object  application session instance
	 */
	protected $session;

	/**
	 *
	 */
	public function __construct(array $config = array(), Manager $session)
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);

		// store the session instance we're going to use
		$this->session = $session;
	}

	/**
	 * get a value from persistent storage
	 *
	 * @param  string  $key  key of the value to get
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key)
	{
		return $this->session->get($key, null);
	}

	/**
	 * write a value to persistent storage
	 *
	 * @param  string  $key    key of the value to write
	 * @param  string  $value  the value
	 *
	 * @since 2.0.0
	 */
	public function set($key, $value)
	{
		return $this->session->set($key, $value);
	}

	/**
	 * delete a value from persistent storage
	 *
	 * @param  string  $key  key of the value to delete
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function delete($key)
	{
		return $this->session->delete($key);
	}
}
