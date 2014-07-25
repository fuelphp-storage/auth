<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Group;

use Fuel\Auth\AuthException;

use Fuel\Common\Arr;

/**
 * File group authentication driver
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
	 * @var  string  name of the file containing the group data
	 */
	protected $file;

	/**
	 * @var  array  loaded group data
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
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_groups.php';
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
	 * Create new group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       name of the group to be created
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be created already exists
	 *
	 * @return  mixed  the key of the group created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function createGroup($group, Array $attributes = [])
	{
		// check if we already have this group
		foreach ($this->data as $id => $data)
		{
			if ($data['name'] == $group)
			{
				throw new AuthException('You can not create the group "'.$group.'". This group already exists.');
			}
		}
		$attributes['name'] = $group;

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
	 * Update an existing group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       id or name of the group to be checked
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be updated does not exist
	 *
	 * @return  mixed  the id of the group updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup($group, Array $attributes = [])
	{
		// get the id of the group we're updating
		$id = $this->findId($group);

		// update the group data
		$attributes['id'] = $id;
		if ( ! isset($attributes['name']))
		{
			$attributes['name'] = $this->data[$id]['name'];
		}
		$this->data[$id] = Arr::merge($this->data[$id], $attributes);

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Delete a group
	 *
	 * @param  string  $group  id or name of the group to be checked
	 *
	 * @throws  AuthException  if the group to be deleted does not exist
	 *
	 * @return  mixed  the id of the group deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteGroup($group)
	{
		// get the id of the group we're deleting
		$id = $this->findId($group);

		// delete the group
		unset($this->data[$id]);

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Assigns a given group to a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $group  id or name of the group to assign. This group must exist
	 * @param  string  $user   user to assign to. if not given, the current logged-in user will be used
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the group assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignUserToGroup($group, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user and ! $user = $this->manager->getUserId())
		{
			throw new AuthException('Unable to assign a group. There is no user logged in.');
		}

		// get the id of the group we're assiging
		$id = $this->findId($group);

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
	 * Return a list of all groups assigned to a user
	 *
	 * @param  string  $user   user to assign to. if not given, the current logged-in user will be used
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	public function getAssignedGroups($user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user and ! $user = $this->manager->getUserId())
		{
			throw new AuthException('Unable to get the assigned groups. There is no user logged in.');
		}

		// fetch the list of group id/name combo's
		$groups = array();

		foreach ($this->data as $group)
		{
			if (isset($group['__users__']) and in_array($user, $group['__users__']))
			{
				$groups[$group['id']] = $group['name'];
			}
		}

		return $groups;
	}

	/**
	 * Return a list of all groups defined
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	public function getAllGroups()
	{
		// fetch the list of group id/name combo's
		$groups = array();

		foreach ($this->data as $group)
		{
			$groups[$group['id']] = $group['name'];
		}

		return $groups;
	}

	/**
	 * Return group information
	 *
	 * @param  string  $group    id or name of the group we need info of
	 * @param  string  $key      name of a group field to return
	 * @param  mixed   $default  value to return if no match could be found on key
	 *
	 * @throws  AuthException  if the requested group does not exist
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getGroup($group, $key = null, $default = null)
	{
		// get the id of the group we're assiging
		$id = $this->findId($group);

		// no key given, return all group data
		if ( ! $key)
		{
			$group = $this->data[$id];
			unset($group['__users__']);
			return $group;
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
	 * Returns wether or not a user is member of the given group.
	 *
	 * If no user is specified, the current logged-in user will be checked.
	 *
	 * @param  string  $group  id or name of the group to be checked
	 * @param  string  $user   user to check. if not given, the current logged-in user will be checked
	 *
	 * @return  bool  true if a match is found, false if not
	 *
	 * @since 2.0.0
	 */
	public function isMemberOf($group, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user and ! $user = $this->manager->getUserId())
		{
			throw new AuthException('Unable to assign a group. There is no user logged in.');
		}

		// get the id of the group we're assiging
		try
		{
			$id = $this->findId($group);

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
	 * Removes a given group from a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $group  id or name of the group to remove. This group must be assigned
	 * @param  string  $user   user to check. if not given, the current logged-in user will be checked
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the group removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeUserFromGroup($group, $user = null)
	{
		// if we don't have a user, get the logged-in user
		if ( ! $user and ! $user = $this->manager->getUserId())
		{
			throw new AuthException('Unable to remove a group. There is no user logged in.');
		}

		// get the id of the group we're assiging
		$id = $this->findId($group);

		// was the user assigned?
		if ( ! isset($this->data[$id]['__users__']) or ! $keys = array_keys($this->data[$id]['__users__'], $user))
		{
			// no, nothing to remove
			return false;
		}

		// remove the user from the group
		foreach ($keys as $key)
		{
			unset($this->data[$id]['__users__'][$key]);
		}

		// write the data
		$this->store();

		return $id;
	}

	/**
	 * Find a group's id by name or id
	 */
	protected function findId($group)
	{
		// if no group is given, bail out
		if ($group === null)
		{
			throw new AuthException('Unable to perform this action. There is no group given.');
		}

		// if an id is given, just return that
		elseif (isset($this->data[$group]))
		{
			$id = $group;
		}

		// see if we can match the name
		else
		{
			foreach ($this->data as $id => $data)
			{
				if ($data['name'] == $group)
				{
					break;
				}
			}
			if ( ! isset($id))
			{
				throw new AuthException('There are no groups defined.');
			}
			elseif ($this->data[$id]['name'] != $group)
			{
				throw new AuthException('Unable to perform this action. No group identified by "'.$group.'" exists.');
			}
		}

		return $id;
	}

	/**
	 * Called from the Auth manager instance to trigger the driver on
	 * specific events. It is up to the driver to deal with that trigger
	 *
	 * @param  string  named hook trigger
	 * @param  string  any arguments for the hook method
	 *
	 * @return  boolean  true if the call succeeded, false if it didn't
	 *
	 * @since 2.0.0
	 */
	public function callHook($hook, $args)
	{
		// process the hooks
		switch ($hook)
		{
			case 'deleteUser':
				return $this->deleteUserHook(reset($args));
		}

		// unknown hook requested
		return parent::callHook($hook, $args);
	}

	/**
	 * Called when a user is deleted, used for cleanup purposes
	 *
	 * @param  string  $user  id of the user to be deleted
	 *
	 * @return  mixed  the id of the account deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	protected function deleteUserHook($id)
	{
		foreach ($this->getAllGroups() as $groupId => $name)
		{
			$this->removeUserFromGroup($name, $id);
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
