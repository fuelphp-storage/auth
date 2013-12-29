<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\User;

use Fuel\Auth\Driver;
use Fuel\Auth\Hasher;

/**
 * Auth User driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver
{
	/**
	 * @var  PHPSecLib\Crypt_Hash  used to create password hashes
	 */
	protected $hasher;

	/**
	 * Returns the hash object and creates it if necessary
	 *
	 * @return  Hasher
	 *
	 * @since 1.0.0
	 */
	public function hasher()
	{
		if ( ! $this->hasher)
		{
			// get an instance of our Crypt Hasher
			$this->hasher = new Hasher();
		}

		return $this->hasher;
	}
	/**
	 * Default password hash method
	 *
	 * @param   string  the string to hash
	 * @param   string  hash method to use
	 *
	 * @return  string  the hashed string, base64 encoded
	 *
	 * @since 1.0.0
	 */
	public function hash($password, $method = 'pbkdf2')
	{
		switch ($method)
		{
			case 'pbkdf2':
			default:
				$hash = base64_encode($this->hasher()->pbkdf2($password, $this->manager->getConfig('salt', ''), $this->manager->getConfig('iterations', 10000), 32));
		}

		return $hash;
	}

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function check();

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
	abstract public function login($user = '', $password = '');

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function isLoggedIn();

	/**
	 * Logout user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function logout();

	/**
	 * get a user data item
	 *
	 * @param  string  $key      the field to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function get($key = null, $value = null);
}
