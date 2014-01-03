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
	 * @var  string  type for drivers extending this base class
	 */
	protected $type = 'user';

	/**
	 * @var  array  exported methods, must be supported by all user drivers
	 *
	 * for every method listed, there MUST be an method definition
	 * in this base class, to ensure the driver implements it!
	 */
	protected $methods = array(
		'hasGuestSupport',
		'check',
		'validate',
		'login',
		'forceLogin',
		'isLoggedIn',
		'logout',
		'create',
		'update',
		'password',
		'reset',
		'delete',
		'get',
		'getUser',
		'getId',
		'getName',
		'getEmail',
	);

	/**
	 * @var  bool  Whether or not this driver has guest support
	 */
	protected $guestSupport = false;

	/**
	 * @var  bool  Whether or not this driver has shadow login support
	 */
	protected $shadowSupport = false;

	/**
	 * @var  Input  Current applications' input container
	 */
	protected $input;

	/**
	 * @var  PHPSecLib\Crypt_Hash  used to create password hashes
	 */
	protected $hasher;

	/**
	 * Base constructor. Prepare all things common for all user drivers
	 */
	public function __construct(array $config = array(), $input = null)
	{
		parent::__construct($config);

		$this->input = $input;

		// note it can only be disabled, not enabled if the driver doesn't support it
		if ($this->guestSupport)
		{
			// update the guest support status for this driver
			$this->guestSupport = (bool) $this->getConfig('guest_account', $this->guestSupport);
		}

		// note it can only be disabled, not enabled if the driver doesn't support it
		if ($this->shadowSupport)
		{
			// update the shadow login mode status for this driver
			$this->shadowSupport = (bool) $this->getConfig('shadow_mode', $this->shadowSupport);
		}
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

	/**
	 * Check if this driver has shadow login support
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasShadowSupport()
	{
		return $this->shadowSupport;
	}

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
	 * @param   string  the salt to use
	 * @param   string  hash method to use
	 *
	 * @return  string  the hashed string, base64 encoded
	 *
	 * @since 1.0.0
	 */
	public function hash($password, $salt = null, $method = 'pbkdf2')
	{
		switch ($method)
		{
			case 'bcrypt':
				$hash = base64_encode($this->hasher()->bcrypt($password, $salt ?: $this->manager->getConfig('salt', '')));
			break;

			case 'crypt':
				$hash = base64_encode($this->hasher()->crypt($password, $salt ?: $this->manager->getConfig('salt', '')));
			break;

			case 'pbkdf2':
			default:
				$hash = base64_encode($this->hasher()->pbkdf2($password, $salt ?: $this->manager->getConfig('salt', ''), $this->manager->getConfig('iterations', 10000), 32));
		}

		return $hash;
	}

	/**
	 *  Generate a very random salt
	 *
	 *  @param  int  $length  required length of the salt string
	 *
	 *  @return  string  generated random salt
	 */
	public function salt($length)
	{
		return $this->hasher()->salt($length);
	}

	/**
	 *  Generate a quick random user readable string
	 *
	 *  @param  int  $length  required length of the string
	 *
	 *  @return  string  generated random string
	 */
	public function randomString($length)
	{
		// allowed characters
		static $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

		// make sure we have enough length
		while (strlen($chars) < $length)
		{
			$chars .= $chars;
		}

		return substr(str_shuffle($chars),0,$length);
	}

	/*--------------------------------------------------------------------------
	 * User driver methods
	 *------------------------------------------------------------------------*/

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  int|false  the id of the logged-in user, or false if not
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
	 * @return  int|false  the id of the user if validated, or false if not
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
	 * @return  int|false  the id of the logged-in user, or false if login failed
	 *
	 * @since 2.0.0
	 */
	abstract public function login($user = null, $password = null);

	/**
	 * Shadow login for a user
	 *
	 * @return  int|false  the id of the logged-in user, or false if login failed
	 *
	 * @since 2.0.0
	 */
	abstract public function shadowLogin();

	/**
	 * Login user using a user id (and no password!)
	 *
	 * This method may not be supported by all user drivers, as some backends
	 * don't allow a forced login without a password.
	 *
	 * @param   string  $id  id or name of the user for which we need to force a login
	 *
	 * @return  bool  true on a successful login, false if it failed
	 *
	 * @since 2.0.0
	 */
	abstract public function forceLogin($id);

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool  true if there is a logged-in user, false if not
	 *
	 * @since 2.0.0
	 */
	abstract public function isLoggedIn();

	/**
	 * Logout user
	 *
	 * @return  bool  true if the logout was succesful, false if not
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
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function get($key = null, $default = null);

	/**
	 * Get the current users PK (usually some form of id number)
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function getId();

	/**
	 * Get the current users username
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function getName();

	/**
	 * Get the current users email address
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function getEmail();

	/**
	 * Get user data
	 *
	 * @param  string  $user      id or name of the user who's data should be retrieved
	 * @param  string  $key       the field to retrieve
	 * @param  string  $default   the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	abstract public function getUser($user, $key = null, $default = null);

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
	 * if the user is not given, the current logged-in user will be updated.
	 * if no user is logged in, an exception will be thrown. If a password is
	 * given, it must match with the password of the user. If not, an exception
	 * is thrown.
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
	abstract public function update($user = null, Array $attributes = array(), $password = null);

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
	abstract public function password($password, $user = null, $currentPassword = null);

	/**
	 * Reset a user's password
	 *
	 * if the username is not given, the password of the current logged-in user
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
	abstract public function reset($user = null);

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
	abstract public function delete($user);
}
