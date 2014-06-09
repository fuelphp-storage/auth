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

use Fuel\Auth\AuthException;

use Fuel\Common\Arr;

/**
 * Auth user file driver class
 *
 * Note: This driver is not thread safe!
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class File extends Base
{
	/**
	 * @var  bool  This driver supports shadow login
	 */
	protected $shadowSupport = true;

	/**
	 * @var  string  name of the file containing the user data
	 */
	protected $file;

	/**
	 * @var  array  loaded user data
	 */
	protected $data = [];

	/**
	 * @var  int  When logged in, the id of the current user
	 */
	protected $currentUser;

	/**
	 * @var  array  guest data
	 */
	protected $guest = array(
		'id'       => 0,
		'group'    => 0,
		'username' => 'Guest',
		'salt'     => '-not-used-',
		'password' => '-not-used-',
		'fullname' => 'Guest User',
		'email'    => 'guest@example.org',
	);

	/**
	 * Constructor, read the file store, or create it if it doesn't exist
	 */
	public function __construct($file)
	{
		// unify the file separators
		$this->file = rtrim(str_replace('\\/', DIRECTORY_SEPARATOR, $file), DIRECTORY_SEPARATOR);

		// if the file given is a path, construct the filename
		if (is_dir($this->file))
		{
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_users.php';
		}

		// open the file
		if ($handle = @fopen($this->file, 'r'))
		{
			// lock the file
			flock($handle, LOCK_SH);

			// load it's contents
			$this->data = include $this->file;

			// release the lock, and close it
			flock($handle, LOCK_UN);
			fclose($handle);
		}
		else
		{
			// attempt to create it
			$this->store();
		}
	}

	/**
	 * Destructor, write the stored data.
	 */
	public function __destruct()
	{
		// write any data
		$this->store();
	}

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool  true if there is a logged-in user, false if not
	 *
	 * @since 2.0.0
	 */
	public function check()
	{
		// if we're not already logged in
		if ( ! $this->isLoggedIn())
		{
			// fetch the loaded persistence driver
			if ($persistence = $this->manager->getPersistenceDriver())
			{
				// and check if there was a current user stored
				if (($this->currentUser = $persistence->get('user')) !== null)
				{
					return true;
				}
			}

			// no persistence driver, so user to login
			return false;
		}

		// already logged in
		return true;
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
	public function create($username, $password, Array $attributes = [])
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
		if (empty($this->data))
		{
			$id = 1;
		}
		else
		{
			end($this->data);
			$id = key($this->data);
			$id++;
		}

		// add it to the attributes
		$attributes['id'] = $id;
		$attributes['username'] = $username;
		if ( ! isset($attributes['group']))
		{
			$attributes['group'] = $this->getConfig('group', 1);
		}
		$attributes['password'] = $password === null ? $password : $this->hash($password, $attributes['salt'] = $this->salt(32));

		// store it
		$this->data[$id] = $attributes;

		// write the data
		$this->store();

		return $id;
	}

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
	public function delete($user)
	{
		// get the id of the user we're updating
		$id = $this->findId($user);

		// delete the user
		unset($this->data[$id]);

		// check the persistence store to see if this user is stored
		if ($persistence = $this->manager->getPersistenceDriver())
		{
			if ($id == $persistence->get('user'))
			{
				$persistence->delete('user');
			}
		}

		// if this was the current user, force a logout
		if ($id === $this->currentUser)
		{
			$this->logout();
		}

		// update the stored data
		$this->store();

		return $id;
	}

	/**
	 * Login user using a user id (and no password!)
	 *
	 * @param   string  $id  id or name of the user for which we need to force a login
	 *
	 * @return  bool  true on a successful login, false if it failed
	 *
	 * @since 2.0.0
	 */
	public function forceLogin($id)
	{
		// get the id of the user who's information we want
		$id = $this->findId($id);

		if (isset($this->data[$id]))
		{
			$this->currentUser = $this->data[$id]['id'];

			if ($persistence = $this->manager->getPersistenceDriver())
			{
				$persistence->set('user', $this->currentUser);
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
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key = null, $default = null)
	{
		// we need a user for this to work
		if ( ! $this->isLoggedIn())
		{
			throw new AuthException('Can not get user data. There is no user logged-in');
		}

		// if we have a guest user
		if ($this->currentUser === 0)
		{
			// return guest data
			return func_num_args() ? Arr::get($this->guest, $key, $default) : $this->guest;
		}

		// return user data
		return func_num_args() ? Arr::get($this->data[$this->currentUser], $key, $default) : $this->data[$this->currentUser];
	}

	/**
	 * Get the current users email address
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getEmail()
	{
		// we need a user for this to work
		if ( ! $this->isLoggedIn())
		{
			throw new AuthException('Can not get the email address. There is no user logged-in');
		}

		// return the users email, if set
		return Arr::get($this->data[$this->currentUser], 'email', null);
	}

	/**
	 * Get the current users PK (usually some form of id number)
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getId()
	{
		// we need a user for this to work
		if ( ! $this->isLoggedIn())
		{
			throw new AuthException('Can not get the user id. There is no user logged-in');
		}

		// return the id of the logged-in user
		return $this->currentUser;
	}

	/**
	 * Get the current users username
	 *
	 * @throws  AuthException  if no user is logged-in
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getName()
	{
		// we need a user for this to work
		if ( ! $this->isLoggedIn())
		{
			throw new AuthException('Can not get the username. There is no user logged-in');
		}

		// return the username, if set
		return Arr::get($this->data[$this->currentUser], 'username', null);
	}

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool  true if there is a logged-in user, false if not
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn()
	{
		return $this->currentUser !== null;
	}

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
	public function login($user = null, $password = null)
	{
		// if we don't have a user or password, check if it was posted
//		$user = $user === null ? $this->input->getParam($this->config['username_post_key'], null) : $user;
//		$password = $password === null ? $this->input->getParam($this->config['password_post_key'], null) : $password;

		// log the user in when it validates
		if ($id = $this->validate($user, $password))
		{
			$this->currentUser = $id;

			if ($persistence = $this->manager->getPersistenceDriver())
			{
				$persistence->set('user', $this->currentUser);
			}
			return $id;
		}

		// login didn't validate, do a forced logout if needed
		elseif ($this->isLoggedIn())
		{
			$this->logout();
		}

		return false;
	}

	/**
	 * Logout user
	 *
	 * @return  bool|null  true if the logout was succesful, null if not
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		// someone needs to be logged-in first
		if ($this->isLoggedIn())
		{
			// reset the current user
			$this->currentUser = $this->hasGuestSupport() ? 0 : null;

			// fetch the loaded persistence driver
			if ($persistence = $this->manager->getPersistenceDriver())
			{
				// remove the stored user from the persistence driver
				$persistence->delete('user');
			}

			// user logged out
			return true;
		}

		// no user logged in
		return false;
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
	public function password($password, $user = null, $currentPassword = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($user);

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
	public function reset($user = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($user);

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
	 * Shadow login for a user
	 *
	 * @return  int|false  the id of the logged-in user, or false if login failed
	 *
	 * @since 2.0.0
	 */
	public function shadowLogin()
	{
		// get the list if logged-in users
		$username = array_filter($this->manager->getName());

		// if there are no valid users, we can't attempt a shadow login
		if (empty($username))
		{
			return false;
		}

		// get the email addresses, and all available data of these users
		$email = array_filter($this->manager->getEmail());
		$attributes = array_filter($this->manager->get());

		// to avoid race conditions (multiple valid logins, different data),
		// use the results of the first authenticated driver
		$username = reset($username);
		$email = reset($email);
		$attributes = reset($attributes);

		// see if we know this user
		try
		{
			// find the id for this user
			$id = $this->findId($username);
		}
		catch (AuthException $e)
		{
			// create a new user
			$id = $this->create($username, null, array('email' => $email));
		}

		// and do a force login of the id found
		return $this->forceLogin($id) ? $id : false;
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
	public function update($user = null, Array $attributes = array(), $password = null)
	{
		// get the id of the user we're updating
		$id = $this->findId($user);

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
	 * Validate a user
	 *
	 * @param   string  $user      user identification (username or email)
	 * @param   string  $password  the password for this user
	 *
	 * @return  int|false  the id of the user if validated, or false if not
	 *
	 * @since 2.0.0
	 */
	public function validate($user, $password)
	{
		// find a match on username or email
		foreach ($this->data as $id => $data)
		{
			if ($data['username'] == $user or $data['email'] == $user)
			{
				// and if a match is found, verify the password
				if ($data['password'] === $this->hash($password, $data['salt']))
				{
					// return the id on a match
					return $id;
				}
			}
		}

		// input didn't validate
		return false;
	}

	/**
	 * Find a user's id by name or email, or use the logged-in user's id
	 */
	protected function findId($user = null)
	{
		// if no user is given, use the currently logged-in user
		if ($user === null)
		{
			if ($this->isLoggedIn())
			{
				$id = $this->currentUser;
			}
			else
			{
				throw new AuthException('Unable to perform this action. There is no user logged in.');
			}
		}

		// if an id is given, ust return that
		elseif (isset($this->data[$user]))
		{
			$id = $user;
		}

		// see if we can match the username or the email address
		else
		{
			foreach ($this->data as $id => $data)
			{
				if ($data['username'] == $user or $data['email'] == $user)
				{
					break;
				}
			}
			if ( ! isset($id))
			{
				throw new AuthException('There are no users defined.');
			}
			elseif ($this->data[$id]['username'] != $user)
			{
				throw new AuthException('Unable to perform this action. No account identified by "'.$user.'" exists.');
			}
		}

		return $id;
	}

	/**
	 * write any stored data.
	 */
	protected function store()
	{
		// open the file
		if ($handle = @fopen($this->file, 'c'))
		{
			// lock the file, and truncate it
			flock($handle, LOCK_EX);
			ftruncate($handle, 0);

			// write the data as a PHP array
			fwrite($handle, '<?php'.PHP_EOL.'return '.var_export($this->data, true).';'.PHP_EOL);

			// release the lock, and close it
			flock($handle, LOCK_UN);
			fclose($handle);
		}
		else
		{
			throw new AuthException('Can not open "'.$this->file.'" for write');
		}
	}
}
