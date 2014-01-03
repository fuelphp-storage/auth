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
 * Null group authentication driver
 *
 * This driver doesn't do anything, and can be used if you don't require
 * group support in your Auth environment
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Null extends Base
{
	/**
	 * @var  bool  This is a read/write driver
	 */
	protected $readOnly = true;

	/**
	 * Return group information for the current logged-in user
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	public function getGroupName()
	{
		// this driver doesn't do groups
		return array();
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
		// this driver doesn't do groups
		return array();
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
		// no groups, so it can never be a member
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
		// this is a read-only driver
		return false;
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
	 * @return  bool  true if the group was succesfully updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup($group, Array $attributes = array())
	{
		// this is a read-only driver
		return false;
	}

	/**
	 * Delete a group
	 *
	 * @param  string  $group  id or name of the group to be checked
	 *
	 * @throws  AuthException  if the group to be deleted does not exist
	 *
	 * @return  bool  true if the delete succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteGroup($group)
	{
		// this is a read-only driver
		return false;
	}
}
