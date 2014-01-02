<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Group;

use Fuel\Auth\AuthException;

/**
 * Config based group authentication driver
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
	 * @var  array  loaded group data
	 */
	protected $data = array(
	);

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		// load the auth group config
		if (is_file($file = $this->getConfig('config_file', null)))
		{
			$this->data = include $file;
		}
		else
		{
			// attempt to create it
			$this->store();
		}
	}

	/**
	 * Return group information for the current logged-in user
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	public function getGroupName()
	{
		// construct the group data
		$groups = array();

		// fetch the group information for the user driver(s)
		foreach ($this->manager->get('group') as $driver => $grouplist)
		{
			// ignore false or null results
			if ($grouplist)
			{
				// process the group or list of groups returned
				foreach((array) $grouplist as $group)
				{
					// ignore group id's not known by this driver
					if (isset($this->data[$group]))
					{
						$groups[$group] = $this->data[$group]['name'];
					}
				}
			}
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
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function getGroups($group = null, $key = null, $default = null)
	{
		if (func_num_args())
		{
			if (isset($this->data[$group]))
			{
				$data &= $this->data[$group];
			}
			else
			{
				foreach ($this->data as $id => $groupinfo)
				{
					if ($groupinfo['name'] == $group)
					{
						$data &= $this->data[$id];
						break;
					}
				}

				if ( ! isset($data))
				{
					throw new AuthException('There is no group identified by "'.$group.'"');
				}
			}

			if (func_num_args() === 1)
			{
				return $data;
			}

			return \Arr::get($data, $key, $default);
		}

		return $this->data;
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
	public function isMember($group, $user = null)
	{
		// get the id for the requested group
		if ( ! isset($this->data[$group]))
		{
			foreach ($this->data as $id => $groupinfo)
			{
				if ($groupinfo['name'] == $group)
				{
					$group = $id;
					break;
				}
			}
		}

		// get the group memberships
		if ($user === null)
		{
			// ... for the current user
			$groups = $this->manager->get('group');
		}
		else
		{
			// ... for a named user
			$groups = $this->manager->getUser($user, 'group');
		}

		// fetch the group information for the user driver(s)
		foreach ($groups as $driver => $grouplist)
		{
			// ignore false or null results
			if ($grouplist)
			{
				// process the group or list of groups returned
				if (in_array($group, (array) $grouplist))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Create new group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       id of the group to be created
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be created already exists
	 *
	 * @return  bool  true if the group was succesfully created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function createGroup($group, Array $attributes = array())
	{
		if (isset($this->data[$group]))
		{
			throw new AuthException('Group "'.$group.'" already exists');
		}

		// create the new group
		$this->data[$group] = $attributes;

		// sort the data on group id
		ksort($this->data);

		// write it to the store
		$this->store();

		return true;
	}

	/**
	 * Update an existing group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       id of the group to be updated
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be updated does not exist
	 *
	 * @return  bool  true if the group was succesfully updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup($group, Array $attributes = array())
	{
		if ( ! isset($this->data[$group]))
		{
			throw new AuthException('Group "'.$group.'" does not exist');
		}

		// update the group
		$this->data[$group] = \Arr::merge($this->data[$group], $attributes);

		// write it to the store
		$this->store();

		return true;
	}

	/**
	 * Delete a group
	 *
	 * @param  string  $group  id of the group to be deleted
	 *
	 * @throws  AuthException  if the group to be deleted does not exist
	 *
	 * @return  bool  true if the delete succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteGroup($group)
	{
		if ( ! isset($this->data[$group]))
		{
			throw new AuthException('Group "'.$group.'" does not exist');
		}

		// delete the group
		unset($this->data[$group]);

		// write it to the store
		$this->store();

		return true;
	}

	/**
	 *
	 */
	protected function store()
	{
		if ( ! $this->readOnly)
		{
			// open the file
			$handle = fopen($this->getConfig('config_file'), 'c');
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
}
