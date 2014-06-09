<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Persistence;

use Fuel\Auth\AuthInterface;

interface PersistenceInterface extends AuthInterface
{
	/**
	 * get a value from persistent storage
	 *
	 * @param  string  $key  key of the value to get
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key);

	/**
	 * write a value to persistent storage
	 *
	 * @param  string  $key    key of the value to write
	 * @param  string  $value  the value
	 *
	 * @since 2.0.0
	 */
	public function set($key, $value);

	/**
	 * delete a value from persistent storage
	 *
	 * @param  string  $key  key of the value to delete
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function delete($key);

}
