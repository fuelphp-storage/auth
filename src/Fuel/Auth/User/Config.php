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

use Fuel\Auth\AuthException;

/**
 * Config based user authentication driver
 *
 * This driver stores all it's data in a fuel configuration file
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Config extends Base
{
	/**
	 * @var  bool  This is a read/write driver
	 */
	protected $readOnly = false;

	/**
	 * @var  bool  This driver has guest account support
	 */
	protected $guestSupport = true;

	/**
	 * @var  array  default driver configuration
	 */
	protected $config = array(
		'configFile' => 'auth-users',
	);

	/**
	 * @var  int  When logged in, the id of the current user
	 */
	protected $currentUser;

	/**
	 * @var  array  dummy guest data
	 */
	protected $guest = array(
		'userid'   => 0,
		'groupid'  => 0,
		'username' => 'Guest',
		'salt'     => '-not-used-',
		'password' => '-not-used-',
		'fullname' => 'Guest User',
		'email'    => 'guest@example.org',
	);

	/**
	 * @var  array  dummy user data
	 */
	protected $data = array(
	);

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		// load the auth user config
		if (is_file($config['config_file']))
		{
			$this->data = include $config['config_file'];
		}

		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function check()
	{
		if ( ! $this->isLoggedIn())
		{
			 $persistence = $this->manager->getDriver('persistence');
			 if ( ! $persistence or ($this->currentUser = $persistence->get('user')) === null)
			 {
				return false;
			 }
		}

		return true;
	}

	/**
	 * Validate a user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @return  mixed  the id of the user if validated, or false if not
	 *
	 * @since 2.0.0
	 */
	public function validate($user, $password)
	{
		foreach ($this->data as $id => $data)
		{
			if ($data['username'] == $user)
			{
				$password = $this->hash($password, $data['salt']);
				if ($data['password'] == $password)
				{
					return $id;
				}
			}
		}

		return false;
	}

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
	public function login($user, $password)
	{
		// log the user in when it validates
		if ($id = $this->validate($user, $password))
		{
			$this->currentUser = $id;

			if ($persistence =$this->manager->getDriver('persistence'))
			{
				$persistence->set('user', $this->currentUser);
			}
			return true;
		}

		// login didn't validate, do a forced logout if needed
		elseif ($this->isLoggedIn())
		{
			$this->logout();
		}

		return false;
	}

	/**
	 * Login user using a user id (and no password!)
	 *
	 * @param   string  $id  id of the user for which we need to force a login
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function forceLogin($id)
	{
		if (isset($this->data[$id]))
		{
			$this->currentUser = $this->data[$id]['userid'];

			if ($persistence =$this->manager->getDriver('persistence'))
			{
				$persistence->set('user', $this->currentUser);
			}
			return true;
		}

		return false;
	}

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn()
	{
		return $this->currentUser !== null;
	}

	/**
	 * Logout user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		if ($this->isLoggedIn())
		{
			$this->currentUser = $this->getConfig('guest_account', false) ? 0 : null;
			if ($persistence =$this->manager->getDriver('persistence'))
			{
				$persistence->delete('user');
			}
			return true;
		}

		return false;
	}

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
	public function get($key = null, $default = null)
	{
		if ($this->isLoggedIn())
		{
			if ($this->currentUser === 0)
			{
				return func_num_args() ? \Arr::get($this->guest, $key, $default) : $this->guest;
			}
			else
			{
				return func_num_args() ? \Arr::get($this->data[$this->currentUser], $key, $default) : $this->data[$this->currentUser];
			}
		}

		return null;
	}

	/**
	 * Get user data
	 *
	 * @param  string  $username  name of the user who's data should be retrieved
	 * @param  string  $key       the field to retrieve
	 * @param  string  $default   the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getUser($username, $key = null, $default = null)
	{
		// get the id of the user who's information we want
		$id = $this->findId($username);

		// return either the requested value or all data
		return func_num_args() > 1 ? \Arr::get($this->data[$id], $key, $default) : $this->data[$id];
	}

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
	public function create($username, $password, Array $attributes = array())
	{
		// check if we already have this user
		foreach ($this->data as $id => $data)
		{
			if ($data['username'] == $username)
			{
				throw new AuthException('You can not create an account for "'.$username.'". This account already exists.');
			}
		}

		// get it's id
		$id = empty($this->data) ? 1 : end($this->data);

		// add it to the attributes
		$attributes['userid'] = $id;
		$attributes['username'] = $username;
		$attributes['salt'] = $this->salt(32);
		$attributes['password'] = $this->hash($password, $attributes['salt']);

		// store it
		$this->data[$id] = $attributes;

		// write the data
		$this->store();

		return $id;
	}

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
	public function update(Array $attributes = array(), $username = null, $password = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($username);

		// validate the password if needed
		if ($password and $this->hash($password, $this->data[$id]['salt']) != $this->data[$id]['password'])
		{
			throw new AuthException('Update failed. The given password does not match the account password.');
		}

		// update the user
		$this->data[$id] = \Arr::merge($this->data[$id], $attributes);

		// write the update
		$this->store();

		return true;
	}

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
	public function password($password, $username = null, $currentPassword = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($username);

		// validate the current password if needed
		if ($currentPassword and $this->hash($currentPassword, $this->data[$id]['salt']) != $this->data[$id]['password'])
		{
			throw new AuthException('Password update failed. The given password does not match the account password.');
		}

		// validate the new password
		$password = (string) $password;
		if (empty($password) or strlen($password) < 6)
		{
			throw new AuthException('Password update failed. The new password does not validate.');
		}

		// generate a new salt, and a hash the new password
		$this->data[$id]['salt'] = $this->salt(32);
		$this->data[$id]['password'] = $this->hash($password, $this->data[$id]['salt']);

		// write the update
		$this->store();

		return true;
	}

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
	public function reset($username = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($username);

		// generate a unique password
		$password = $this->randomString(8);

		// generate a new salt, and a hash the new password
		$this->data[$id]['salt'] = $this->salt(32);
		$this->data[$id]['password'] = $this->hash($password, $this->data[$id]['salt']);

		// write the update
		$this->store();

		// assign a new password and return it
		return $password;
	}

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
	public function delete($username)
	{
		// get the id of the user we're updating
		$id = $this->findId($username);

		// delete the user
		unset($this->data[$id]);

		// check the persistence store to see if this user is stored
		$persistence = $this->manager->getDriver('persistence');
		if ($persistence and $id == $persistence->get('user'))
		{
			$persistence->delete('user');
		}

		// if this was the current user, force a logout
		if ($id === $this->currentUser)
		{
			$this->logout();
		}

		// update the stored data
		$this->store();

		return true;
	}

	/**
	 * Find a user's id by name, or use the logged-in user's id
	 */
	protected function findId($username = null)
	{
		// get the id of the user we're updating
		if ($username === null)
		{
			if ($this->isLoggedIn())
			{
				$id = $this->currentUser;
			}
			else
			{
				throw new AuthException('You can not reset the password. There is no user logged in.');
			}
		}
		else
		{
			foreach ($this->data as $id => $data)
			{
				if ($data['username'] == $username)
				{
					break;
				}
			}
			if ( ! isset($id))
			{
				throw new AuthException('There are no users defined.');
			}
			elseif ($this->data[$id]['username'] != $username)
			{
				throw new AuthException('You can not reset the password of "'.$username.'". This account does not exist.');
			}
		}

		return $id;
	}

	/**
	 *
	 */
	protected function store()
	{
		// open the file
		$handle = fopen($this->config['config_file'], 'c');
		if ($handle)
		{
			// lock the file, and truncate it
			flock($handle, LOCK_EX);
			ftruncate($handle, 0);

			fwrite($handle, '<?php'.PHP_EOL.'return '.var_export($this->data, true).';'.PHP_EOL);

			// release the lock, and close it
			flock($handle, LOCK_UN);
			fclose($handle);
		}
		else
		{
			throw new AuthException('Can not open "'.$this->config['config_file'].'" for write');
		}
	}
}
