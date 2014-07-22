<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Role;

use Fuel\Auth\AuthException;

/**
 * File role authentication driver
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
	 * @var  string  name of the file containing the role data
	 */
	protected $file;

	/**
	 * @var  array  loaded role data
	 */
	protected $data = [];

	/**
	 * Constructor, read the file store, or create it if it doesn't exist
	 */
	public function __construct(array $config = [])
	{
		// deal with the config
		parent::__construct($config);

		// make sure we have a file or a path
		if ( ! isset($config['file']))
		{
			// use the system temp directory
			$config['file'] = sys_get_temp_dir();
		}

		// unify the file separators
		$this->file = rtrim(str_replace('\\/', DIRECTORY_SEPARATOR, $config['file']), DIRECTORY_SEPARATOR);

		// if the file given is a path, construct the filename
		if (is_dir($this->file))
		{
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_roles.php';
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
	 * Create new role
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $role        name of the role to be created
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the role to be created already exists
	 *
	 * @return  mixed  the key of the role created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function createRole($role, Array $attributes = [])
	{
		// check if we already have this role
		foreach ($this->data as $id => $data)
		{
			if ($data['name'] == $role)
			{
				throw new AuthException('You can not create the role "'.$role.'". This role already exists.');
			}
		}
		$attributes['name'] = $role;

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

		// store it
		$this->data[$id] = $attributes;

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Update an existing role
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $role        id or name of the role to be checked
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the role to be updated does not exist
	 *
	 * @return  mixed  the id of the role updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateRole($role, Array $attributes = [])
	{
		// get the id of the role we're updating
		$id = $this->findId($role);

		// update the role data
		$attributes['id'] = $id;
		if ( ! isset($attributes['name']))
		{
			$attributes['name'] = $this->data[$id]['name'];
		}
		$this->data[$id] = $attributes;

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Delete a role
	 *
	 * @param  string  $role  id or name of the role to be checked
	 *
	 * @throws  AuthException  if the role to be deleted does not exist
	 *
	 * @return  mixed  the id of the role deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteRole($role)
	{
		// get the id of the role we're deleting
		$id = $this->findId($role);

		// delete the role
		unset($this->data[$id]);

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Assigns a given role to a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $role  id or name of the role to assign. This role must exist
	 * @param  string  $user  user to assign to. if not given, the current logged-in user will be used
	 *
	 * @throws  AuthException  if the requested role does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the role assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignUserToRole($role, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user)
		{
			// check the persistence store to see if this user is stored
			if ( ! $persistence = $this->manager->getPersistenceDriver() or ! $user = $persistence->get('user'))
			{
				throw new AuthException('Unable to assign a role. There is no user logged in.');
			}
		}

		// get the id of the role we're assiging
		$id = $this->findId($role);

		// assign the user
		if ( ! isset($this->data[$id]['__users__']))
		{
			$this->data[$id]['__users__'] = array();
		}
		$this->data[$id]['__users__'][] = $user;

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Return a list of all roles assigned to the current logged-in user
	 *
	 * @return  array  assoc array with roleid => name
	 *
	 * @throws  AuthException  if there is no user
	 *
	 * @since 2.0.0
	 */
	public function getAssignedRoles()
	{
		// check the persistence store to see if this user is stored
		if ( ! $persistence = $this->manager->getPersistenceDriver() or ! $user = $persistence->get('user'))
		{
			throw new AuthException('Unable to get the assigned roles. There is no user logged in.');
		}

		// fetch the list of role id/name combo's
		$roles = array();

		foreach ($this->data as $role)
		{
			if (isset($role['__users__']) and in_array($user, $role['__users__']))
			{
				$roles[$role['id']] = $role['name'];
			}
		}

		return $roles;
	}

	/**
	 * Return a list of all roles defined
	 *
	 * @return  array  assoc array with roleid => name
	 *
	 * @since 2.0.0
	 */
	public function getAllRoles()
	{
		// fetch the list of role id/name combo's
		$roles = array();

		foreach ($this->data as $role)
		{
			$roles[$role['id']] = $role['name'];
		}

		return $roles;
	}

	/**
	 * Return role information
	 *
	 * @param  string  $role     id or name of the role we need info of
	 * @param  string  $key      name of a role field to return
	 * @param  mixed   $default  value to return if no match could be found on key
	 *
	 * @throws  AuthException  if the requested role does not exist
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getRole($role, $key = null, $default = null)
	{
		// get the id of the role we're assiging
		$id = $this->findId($role);

		// no key given, return all role data
		if ( ! $key)
		{
			$role = $this->data[$id];
			unset($role['__users__']);
			return $role;
		}

		// key set, so return it
		elseif (isset($this->data[$id][$key]))
		{
			return $this->data[$id][$key];
		}

		// key not set
		return $default;
	}

	/**
	 * Returns wether or not a user is member of the given role.
	 *
	 * If no user is specified, the current logged-in user will be checked.
	 *
	 * @param  string  $role  id or name of the role to be checked
	 * @param  string  $user  user to check. if not given, the current logged-in user will be checked
	 *
	 * @return  bool  true if a match is found, false if not
	 *
	 * @since 2.0.0
	 */
	public function isAssignedTo($role, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user)
		{
			// check the persistence store to see if this user is stored
			if ( ! $persistence = $this->manager->getPersistenceDriver() or ! $user = $persistence->get('user'))
			{
				throw new AuthException('Unable to assign a role. There is no user logged in.');
			}
		}

		// get the id of the role we're assiging
		try
		{
			$id = $this->findId($role);

			// was the user assigned?
			if (isset($this->data[$id]['__users__']) and in_array($user, $this->data[$id]['__users__']))
			{
				return true;
			}
		}
		catch (AuthException $e)
		{
			// ignore this exception
		}

		// not a member
		return false;
	}

	/**
	 * Removes a given role from a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $role  id or name of the role to remove. This role must be assigned
	 * @param  string  $user  user to check. if not given, the current logged-in user will be checked
	 *
	 * @throws  AuthException  if the requested role does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the role removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeUserFromRole($role, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user)
		{
			// check the persistence store to see if this user is stored
			if ( ! $persistence = $this->manager->getPersistenceDriver() or ! $user = $persistence->get('user'))
			{
				throw new AuthException('Unable to assign a role. There is no user logged in.');
			}
		}

		// get the id of the role we're assiging
		$id = $this->findId($role);

		// was the user assigned?
		if ( ! isset($this->data[$id]['__users__']) or ! $keys = array_keys($this->data[$id]['__users__'], $user))
		{
			// no, nothing to remove
			return false;
		}

		// remove the user from the role
		foreach ($keys as $key)
		{
			unset($this->data[$id]['__users__'][$key]);
		}

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Find a role's id by name or id
	 */
	protected function findId($role)
	{
		// if no role is given, bail out
		if ($role === null)
		{
			throw new AuthException('Unable to perform this action. There is no role given.');
		}

		// if an id is given, just return that
		elseif (isset($this->data[$role]))
		{
			$id = $role;
		}

		// see if we can match the name
		else
		{
			foreach ($this->data as $id => $data)
			{
				if ($data['name'] == $role)
				{
					break;
				}
			}
			if ( ! isset($id))
			{
				throw new AuthException('There are no roles defined.');
			}
			elseif ($this->data[$id]['name'] != $role)
			{
				throw new AuthException('Unable to perform this action. No role identified by "'.$role.'" exists.');
			}
		}

		return $id;
	}

	/**
	 * Called when a user is deleted, can be used for cleanup purposes
	 *
	 * @param  string  $user  id of the user to be deleted
	 *
	 * @return  mixed  the id of the account deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteUser($id)
	{
		foreach ($this->getAllRoles() as $roleId => $name)
		{
			$this->removeUserFromRole($name, $id);
		}

		return true;
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
