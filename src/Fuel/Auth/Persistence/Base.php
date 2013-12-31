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

use Fuel\Auth\Driver;

/**
 * Auth Persistence driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver
{
	/**
	 * @var  string  type for drivers extending this base class
	 */
	protected $type = 'persistence';

	/**
	 * @var  array  empty array, persistence drivers don't have global methods
	 */
	protected $methods = array();

	/**
	 * get a value from persistent storage
	 *
	 * @param  string  $key  key of the value to get
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function get($key);

	/**
	 * write a value to persistent storage
	 *
	 * @param  string  $key    key of the value to write
	 * @param  string  $value  the value
	 *
	 * @since 2.0.0
	 */
	abstract public function set($key, $value);

	/**
	 * delete a value from persistent storage
	 *
	 * @param  string  $key  key of the value to delete
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function delete($key);
}
