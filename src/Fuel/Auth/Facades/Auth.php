<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Facades;

use Fuel\Foundation\Facades\Base;

/**
 * Auth Facade class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Auth extends Base
{
	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::$dic->resolve('auth');
	}
}
