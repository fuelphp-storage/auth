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
	 * @var  array  default driver configuration
	 */
	protected $config = array(
		'configFile' => 'auth-groups',
	);

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
	 * @throws  AuthException  if the requested group does not exist
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function getGroups($id = null, $key = null, $default = null)
	{
		if (func_num_args())
		{
			if ( ! isset($this->data[$id]))
			{
				throw new AuthException('There is no group defined with id "'.$id.'"');
			}

			if (func_num_args() === 1)
			{
				return $this->data[$id];
			}

			return \Arr::get($this->data[$id], $key, $default);
		}

		return $this->data;
	}

	/**
	 * Returns wether or not a user is member of the given group.
	 *
	 * If no user is specified, the current logged-in user will be checked.
	 *
	 * @return  bool  true if a match is found, false if not
	 *
	 * @since 2.0.0
	 */
	public function isMember($id, $user = null)
	{
		// get the group memberships
		if ($user === null)
		{
			$groups = $this->manager->get('group');
		}
		else
		{
			$groups = $this->manager->getUser($user, 'group');
		}

		// fetch the group information for the user driver(s)
		foreach ($groups as $driver => $grouplist)
		{
			// ignore false or null results
			if ($grouplist)
			{
				// process the group or list of groups returned
				foreach((array) $grouplist as $group)
				{
					// check for an existing group and a match
					if (isset($this->data[$group]) and $id == $group)
					{
						return true;
					}
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
	 * @param  string  $groupname   name of the group to be created
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be created already exists
	 *
	 * @return  mixed  the new id of the group created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function createGroup($groupname, Array $attributes = array())
	{
	}

	/**
	 * Update an existing group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  array   $attributes  any attributes to be passed to the driver
	 * @param  string  $groupname    name of the group to be updated
	 *
	 * @throws  AuthException  if the group to be updated does not exist
	 *
	 * @return  bool  true if the update succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup(Array $attributes = array(), $groupname = null)
	{
	}

	/**
	 * Delete a group
	 *
	 * @param  string  $groupname         name of the group to be deleted
	 *
	 * @throws  AuthException  if the group to be deleted does not exist
	 *
	 * @return  bool  true if the delete succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteGroup($groupname)
	{
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
