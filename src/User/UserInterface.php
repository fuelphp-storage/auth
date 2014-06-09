<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\User;

use Fuel\Auth\AuthInterface;

interface UserInterface extends AuthInterface
{
	/**
	 * check if this driver supports guest users
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasGuestSupport();

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool  true if there is a logged-in user, false if not
	 *
	 * @since 2.0.0
	 */
	public function check();

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool  true if there is a logged-in user, false if not
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn();

	/**
	 * Login user using a user id (and no password!)
	 *
	 * @param   string  $id  id or name of the user for which we need to force a login
	 *
	 * @return  bool  true on a successful login, false if it failed
	 *
	 * @since 2.0.0
	 */
	public function forceLogin($id);

	/**
	 * Login user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @return  bool  true on a successful login, false if it failed
	 *
	 * @since 2.0.0
	 */
	public function login($user = null, $password = null);

	/**
	 * Shadow login for a user
	 *
	 * @return  int|false  the id of the logged-in user, or false if login failed
	 *
	 * @since 2.0.0
	 */
	public function shadowLogin();

	/**
	 * Logout user
	 *
	 * @return  bool|null  true if the logout was succesful, null if not
	 *
	 * @since 2.0.0
	 */
	public function logout();

	/**
	 * Get a user data item
	 *
	 * @param  string  $key      the field to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key = null, $default = null);

	/**
	 * Get the current users PK (usually some form of id number)
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getId();

	/**
	 * Get the current users username
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getName();

	/**
	 * Get the current users email address
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getEmail();

	/**
	 * Validate a user
	 *
	 * @param   string  $user      user identification (username or email)
	 * @param   string  $password  the password for this user
	 *
	 * @return  int|false  the id of the user if validated, or false if not
	 *
	 * @since 2.0.0
	 */
	public function validate($user, $password);

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
	public function create($username, $password, Array $attributes = []);

	/**
	 * Update an existing user
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * if the username is not given, the current logged-in user will be updated.
	 * if no user is logged in, an exception will be thrown. If a password is
	 * given, it must match with the password. If not, an exception is thrown.
	 *
	 * @param  string  $user        id or name of the user to be updated
	 * @param  array   $attributes  any attributes to be passed to the driver
	 * @param  string  $password    the users current password
	 *
	 * @throws  AuthException  if the user to be updated does not exist
	 * @throws  AuthException  if the given password doesn't match the user password
	 *
	 * @return  bool  true if the update succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function update($user = null, Array $attributes = array(), $password = null);

	/**
	 * Change a user's password
	 *
	 * if the username is not given, the password of the current logged-in user
	 * will be updated. if no user is logged in, an exception will be thrown.
	 * If a current password is given, it must match with the password of the
	 * user. If not, an exception is thrown.
	 *
	 * @param  string  $password         the users new password
	 * @param  string  $user             id or name of the user to be updated
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
	public function password($password, $user = null, $currentPassword = null);

	/**
	 * Reset a user's password
	 *
	 * if the user is not given, the password of the current logged-in user
	 * will be reset. if no user is logged in, an exception will be thrown.
	 *
	 * @param  string  $user      id or name of the user to be updated
	 *
	 * @throws  AuthException  if the user to be updated does not exist
	 *
	 * @return  mixed  the new password, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function reset($user = null);

	/**
	 * Delete a user
	 *
	 * if you delete the current logged-in user, a logout will be forced.
	 *
	 * @param  string  $user  id or name of the user to be deleted
	 *
	 * @throws  AuthException  if the user to be deleted does not exist
	 *
	 * @return  mixed  the id of the account deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function delete($user);

	/**
	 * Returns the hash object and creates it if necessary
	 *
	 * @return  Hasher
	 *
	 * @since 1.0.0
	 */
	public function hasher();

	/**
	 * Default password hash method
	 *
	 * @param   string  the string to hash
	 * @param   string  the salt to use
	 * @param   string  hash method to use
	 *
	 * @return  string  the hashed string, base64 encoded
	 *
	 * @since 1.0.0
	 */
	public function hash($password, $salt = null, $method = 'pbkdf2');

	/**
	 *  Generate a very random salt
	 *
	 *  @param  int  $length  required length of the salt string
	 *
	 *  @return  string  generated random salt
	 */
	public function salt($length);

	/**
	 *  Generate a quick random user readable string
	 *
	 *  @param  int  $length  required length of the string
	 *
	 *  @return  string  generated random string
	 */
	public function randomString($length);

}
