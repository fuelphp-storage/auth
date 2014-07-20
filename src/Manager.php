<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth;

use Fuel\Auth\Storage\StorageInterface;
use Fuel\Auth\Persistence\PersistenceInterface;

use Fuel\Common\Arr;

/**
 * Auth manager class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Manager
{
	/**
	 * @var  StorageInterface  loaded storage driver
	 */
	protected $storage;

	/**
	 * @var  PersistanceInterface  loaded persistence driver
	 */
	protected $persistence;

	/**
	 * @var  array  default auth configuration
	 */
	protected $config = ['use_all_drivers' => false, 'always_return_arrays' => false];

	/**
	 * @var  array  loaded auth drivers
	 */
	protected $drivers = array();

	/**
	 * @var  array  supported method list, and a link to the driver that implements it
	 */
	protected $methods = [];

	/**
	 * @var  int  When logged in, the unified unique id of the current logged-in user
	 */
	protected $unifiedUserId;

	/**
	 * @var  array  errors picked up in the last driver call
	 */
	protected $lastErrors = [];

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct(StorageInterface $storage, PersistenceInterface $persistence, Array $config = [])
	{
		// store the passed drivers
		$this->storage = $storage;
		$this->persistence = $persistence;

		// update the default config with whatever was passed
		$this->config = Arr::merge($this->config, $config);
	}

	/**
	 * Capture calls to driver methods, and distribute them after checking...
	 *
	 * @param  string  $method      method name that was called
	 * @param  array   $args        array of arguments for the method
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function __call($method, $args)
	{
		// DEBUG CODE - REMOVE THIS LATER !
		foreach($this->methods as $_method => $_drivers)
		{
			if (count($_drivers) !== 1)
			{
				echo "WARNING - Duplicate method '".$_method."' detected: (";
				$_drlist = '';
				foreach ($_drivers as $_driver)
				{
					$_drlist .= (empty($_drlist) ? '' : ', ').get_class($_driver);
				}
				echo $_drlist.")".PHP_EOL;
			}
		}
		// DEBUG CODE - REMOVE THIS LATER !

		// do we know this method?
		if (isset($this->methods[$method]))
		{
			// reset the last error array
			$this->lastErrors = [];

			// some storage for the results
			$result = [];

			// loop over the defined drivers
			foreach ($this->methods[$method] as $name => $driver)
			{
				// call the driver method
				try
				{
					if ($result[$name] = call_user_func_array(array($driver, $method), $args))
					{
						// if we don't have to try all, bail out now
						if ($this->getConfig('use_all_drivers', false) === false)
						{
							break;
						}
					}
				}
				catch (AuthException $e)
				{
					// store the exception
					$this->lastErrors[$name] = $e;

					// and (re)set the result
					$result[$name] = false;
				}
			}

			if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
			{
				return reset($result);
			}
			else
			{
				return $result;
			}
		}

		// we don't know or support this method
		throw new AuthException('Method "'.get_class($this).'::'.$method.'()" does not exist.');
	}

	/**
	 * get a configuration item
	 *
	 * @param  string  $key      the config key to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getConfig($key = null, $default = null)
	{
		return func_num_args() ? Arr::get($this->config, $key, $default) : $this->config;
	}

	/**
	 * Set a configation value
	 *
	 * @param   mixed   $key    The dot-notated key to set or array of keys
	 * @param   mixed   $value  The value
	 * @return  void
	 *
	 * @since 2.0.0
	 */
	public function setConfig($key, $value)
	{
		Arr::set($this->config, $key, $value);
	}

	/**
	 * add a new driver
	 *
	 * @param  string  $driver  the driver instance
	 * @param  string  $name    the name of the driver added
	 *
	 * @throws  AuthException if the driver by the given name is already loaded
	 * @throws  AuthException if the driver doesn't implement Fuel\Auth\AuthInterface at all
	 * @throws  AuthException if the driver doesn't implement an interface that implements Fuel\Auth\AuthInterface
	 * @since 2.0.0
	 */
	public function addDriver($driver, $name = null)
	{
		// bail out if it doesn't implement AuthInterface
		if ( ! $driver instanceOf AuthInterface)
		{
			throw new AuthException('Driver '.get_class($driver).' does not implement Fuel\Auth\AuthInterface.');
		}

		// link this driver to it's manager
		$driver->setManager($this);

		// if no name is given, use the class name
		if ( ! $name)
		{
			$name = get_class($driver);
		}

		// store the driver
		if (isset($this->drivers[$name]))
		{
			throw new AuthException('A driver with name '.$name.' is already loaded.');
		}

		// Get this drivers interface definition
		$interfacePresent = false;
		foreach (class_implements($driver, false) as $interface)
		{
			if (in_array('Fuel\Auth\AuthInterface', class_implements($interface, false)))
			{
				$interfacePresent = true;

				// store the method reference
				foreach (get_class_methods($interface) as $method)
				{
					if ( ! isset($this->methods[$method]))
					{
						$this->methods[$method] = [];
					}
					elseif ( ! $driver->hasConcurrency())
					{
						throw new AuthException('A driver with name '.$name.' is already loaded and multiple drivers of this type are not supported.');
					}

					$this->methods[$method][$name] = $driver;
				}
			}
		}

		// was an interface present
		if ( ! $interfacePresent)
		{
			throw new AuthException('Driver '.get_class($driver).' does not implement an Interface that implements Fuel\Auth\AuthInterface.');
		}

		$this->drivers[$name] = $driver;
	}

	/**
	 * get a specific driver instance
	 *
	 * @param  string  $name    the name of the driver to get
	 *
	 * @return  Driver  driver instance
	 *
	 * @since 2.0.0
	 */
	public function getDriver($name = null)
	{
		if (func_num_args() === 0)
		{
			return $this->drivers;
		}

		elseif (isset($this->drivers[$name]))
		{
			return $this->drivers[$name];
		}

		// no hit
		throw new AuthException('A driver with name '.$name.' is not loaded.');
	}

	/**
	 * remove an existing driver
	 *
	 * @param  string  $name    the name of the driver to be removed
	 *
	 * @return  void
	 *
	 * @since 2.0.0
	 */
	public function removeDriver($name)
	{
		// make sure we have a driver by this name
		if ( ! isset($this->drivers[$name]))
		{
			throw new AuthException('A driver with name '.$name.' is not loaded.');
		}

		// remove all method references for this driver
		foreach ($this->methods as $method => &$driverlist)
		{
			unset($driverlist[$name]);
			if (empty($this->methods[$method]))
			{
				unset ($this->methods[$method]);
			}
		}
	}

	/**
	 * Return the loaded persistence driver
	 *
	 * @return  PersistenceInterface   the loaded persistence driver
	 *
	 * @since 2.0.0
	 */
	public function getPersistenceDriver()
	{
		return $this->persistence;
	}

	/**
	 * Return the loaded storage driver
	 *
	 * @return  StorageInterface   the loaded storage driver
	 *
	 * @since 2.0.0
	 */
	public function getStorageDriver()
	{
		return $this->storage;
	}

	/**
	 * Return the errors detected in the last driver call
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function lastErrors()
	{
		return $this->lastErrors;
	}

	/*--------------------------------------------------------------------------
	 * Unified user driver methods
	 *------------------------------------------------------------------------*/

	/**
	 * Return the current unified unique user id
	 *
	 * @return  mixed  user id, or null if not logged in
	 *
	 * @since 2.0.0
	 */
	public function getUserId()
	{
		return $this->unifiedUserId;
	}

	/**
	 * Check if we have a logged-in user
	 *
	 * @return  bool  true if a user is logged in, false if not
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn()
	{
		return $this->unifiedUserId !== null;
	}

	/**
	 * Check if we have no logged-in user
	 *
	 * @return  bool  false if a user is logged in, true if not
	 *
	 * @since 2.0.0
	 */
	public function isGuest()
	{
		return ! $this->isLoggedIn();
	}

	/**
	 * Login user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @throws  AuthException  if no storage driver is defined
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function login($user = null, $password = null)
	{
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getStorageDriver())
		{
			throw new AuthException('No storage driver is defined, can not access global auth information');
		}

		// store the return type setting
		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the login method on all loaded drivers
		$result = $this->__call('login', array($user, $password));

		// restore the return type setting
		$this->config['always_return_arrays'] = $orgSetting;

		// if we have a successful login
		if ( ! empty(array_filter($result)))
		{
			// attempt a shadow login for all login drivers that failed
			foreach ($result as $driver => $id)
			{
				if ($id === false and $this->getDriver($driver)->hasShadowSupport())
				{
					// call the driver method
					try
					{
						$result[$driver] = $this->getDriver($driver)->shadowLogin();
					}
					catch (\Exception $e)
					{
						// store the exception
						$this->lastErrors[$driver] = $e;

						// and (re)set the result
						$result[$driver] = false;
					}
				}
			}
		}

		$this->config['always_return_arrays'] = $orgSetting;

		// determine the unified user id
		if ( ! $this->unifiedUserId = $storage->findUnifiedUser($result))
		{
			// no hit, all logins must have failed
			$this->unifiedUserId = null;
		}

		if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
		{
			return reset($result);
		}

		return $result;
	}

	/**
	 * Login user using a (linked) user id (and no password!)
	 *
	 * This method may not be supported by all user drivers, as some backends
	 * don't allow a forced login without a password.
	 *
	 * @param   string  $id  id of the user for which we need to force a login
	 *
	 * @throws  AuthException  if no storage driver is defined
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function forceLogin($id)
	{
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getStorageDriver())
		{
			throw new AuthException('No storage driver is defined, can not access global auth information');
		}

		// storage for the result
		$result = array();

		// get the list of drivers that has an account for this user
		if ($accounts = $storage->getUnifiedUsers($id))
		{
			// loop over the list
			foreach($accounts as $driver => $id)
			{
				// if we don't have this driver loaded
				if ( ! isset($this->drivers[$driver]))
				{
					// then the login obviously failed
					$result[$driver] = false;
				}
				else
				{
					// if the driver is already in logged-in state
					if ($this->drivers[$driver]->isLoggedIn())
					{
						// then there's no point logging in again
						$result[$driver] = false;
					}
					else
					{
						// attempt a forced login
						$result[$driver] = $this->drivers[$driver]->forceLogin($id);
					}
				}
			}
		}

		// return the result
		return $result;
	}

	/**
	 * Logout user
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		// store the return type setting
		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the login method on all loaded drivers
		$result = $this->__call('logout', array());

		// restore the return type setting
		$this->config['always_return_arrays'] = $orgSetting;

		// check for a success for at least one driver
		if (in_array(true, $result))
		{
			// reset the unified user id
			$this->unifiedUserId = null;
		}

		return $result;
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
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getStorageDriver())
		{
			throw new AuthException('No storage driver is defined, can not access global auth information');
		}

		// store the return type setting
		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the delete method on all loaded drivers
		$result = $this->__call('delete', array($username));

		// restore the return type setting
		$this->config['always_return_arrays'] = $orgSetting;

		// delete the unified user information
		$id =  $storage->deleteUnifiedUser($result);
		if ($this->unifiedUserId === $id)
		{
			// delete of the logged-in user, force a logout
			$this->unifiedUserId = null;
		}

		if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
		{
			return reset($result);
		}

		return $result;
	}
}
