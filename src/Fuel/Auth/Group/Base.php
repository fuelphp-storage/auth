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

use Fuel\Auth\Driver;

/**
 * Auth Group driver base class
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
	protected $type = 'group';

	/**
	 * @var  array  exported methods, must be supported by all user drivers
	 *
	 * for every method listed, there MUST be an method definition
	 * in this base class, to ensure the driver implements it!
	 */
	protected $methods = array(
		'getGroupName',
		'getGroups',
		'isMember',
		'createGroup',
		'updateGroup',
		'deleteGroup',
	);

	/**
	 * Base constructor. Prepare all things common for all group drivers
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Return the name(s) or all group(s) the current logged-in user is a
	 * member of.
	 *
	 * Since multiple user drivers may be in use, and since they could have
	 * a completely different system of dealing with groups, this method
	 * should silently ignore any groups it doesn't know, as they might be
	 * handled by another driver.
	 *
	 * Also, a user driver might support zero, one or more groups, so when
	 * querying group information from a user driver, you might get an int,
	 * string or array back, depending on the driver design.
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	abstract public function getGroupName();

	/**
	 * Return group information
	 *
	 * @throws  AuthException  if the requested group does not exist
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	abstract public function getGroups($id = null, $key = null, $default = null);

	/**
	 * Returns wether or not a user is member of the given group.
	 *
	 * If no user is specified, the current logged-in user will be checked.
	 *
	 * @return  bool  true if a match is found, false if not
	 *
	 * @since 2.0.0
	 */
	abstract public function isMember($id, $user = null);

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
	abstract public function createGroup($groupname, Array $attributes = array());

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
	abstract public function updateGroup(Array $attributes = array(), $groupname = null);

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
	abstract public function deleteGroup($groupname);
}
