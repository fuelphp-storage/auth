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
	 * @var  bool  This is a ReadOnly driver
	 */
	protected $isReadOnly = true;

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
	 * @return  mixed  the id of the group updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup($group, Array $attributes = [])
	{
		throw new AuthException('There are no groups defined.');
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
		throw new AuthException('There are no groups defined.');
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
		throw new AuthException('There are no groups defined.');
	}

	/**
	 * Return a list of all groups assigned to the current logged-in user
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @throws  AuthException  if there is no user
	 *
	 * @since 2.0.0
	 */
	public function getAssignedGroups()
	{
		// this is a read-only driver
		return array();
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
		// this is a read-only driver
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
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getGroup($group, $key = null, $default = null)
	{
		throw new AuthException('There are no groups defined.');
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
		// no groups, so user can't be a member
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
		throw new AuthException('There are no groups defined.');
	}

}
