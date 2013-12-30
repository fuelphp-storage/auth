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
	 * Validate a user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function validate($user, $password);

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
	abstract public function login($user, $password);

	/**
	 * Login user using a user id (and no password!)
	 *
	 * This method may not be supported by all user drivers, as some backends
	 * don't allow a forced login without a password.
	 *
	 * @param   string  $id  id of the user for which we need to force a login
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	abstract public function forceLogin($id);

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
	 * Get a user data item
	 *
	 * @param  string  $key      the field to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function get($key = null, $value = null);

	/**
	 * Create new user
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $username    name of the user to be created
	 * @param  string  $password    the users password
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the user to be created already exists
	 *
	 * @return  mixed  the new id of the account created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function create($username, $password, Array $attributes = array());

	/**
	 * Update an existing user
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * if the username is not given, the current logged-in user will be updated.
	 * if no user is logged in, an exception will be thrown. If a password is
	 * given, it must match with the password of the user. If not, an exception
	 * is thrown.
	 *
	 * @param  array   $attributes  any attributes to be passed to the driver
	 * @param  string  $username    name of the user to be updated
	 * @param  string  $password    the users current password
	 *
	 * @throws  AuthException  if the user to be updated does not exist
	 * @throws  AuthException  if the given password doesn't match the user password
	 *
	 * @return  bool  true if the update succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function update(Array $attributes = array(), $username = null, $password = null);

	/**
	 * Change a user's password
	 *
	 * if the username is not given, the password of the current logged-in user
	 * will be updated. if no user is logged in, an exception will be thrown.
	 * If a current password is given, it must match with the password of the
	 * user. If not, an exception is thrown.
	 *
	 * @param  string  $password         the users new password
	 * @param  string  $username         name of the user to be updated
	 * @param  string  $currentPassword  the users current password
	 *
	 * @throws  AuthException  if the user to be updated does not exist
	 * @throws  AuthException  if the given password doesn't match the user password
	 * @throws  AuthException  if the new password doesn't validate
	 *
	 * @return  bool  true if the update succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function password($password, $username = null, $currentPassword = null);

	/**
	 * Reset a user's password
	 *
	 * if the username is not given, the password of the current logged-in user
	 * will be reset. if no user is logged in, an exception will be thrown.
	 *
	 * @param  string  $password         the users new password
	 * @param  string  $username         name of the user to be updated
	 *
	 * @throws  AuthException  if the user to be updated does not exist
	 *
	 * @return  mixed  the new password, or false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function reset($username = null);

	/**
	 * Delete a user
	 *
	 * if you delete the current logged-in user, a logout will be forced.
	 *
	 * @param  string  $username         name of the user to be deleted
	 *
	 * @throws  AuthException  if the user to be deleted does not exist
	 *
	 * @return  bool  true if the delete succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function delete($username);
}
