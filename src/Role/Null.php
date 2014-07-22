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
 * Null role authentication driver
 *
 * This driver doesn't do anything, and can be used if you don't require
 * role support in your Auth environment
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
		// this is a read-only driver
		return false;
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
		throw new AuthException('There are no roles defined.');
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
		throw new AuthException('There are no roles defined.');
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
		throw new AuthException('There are no roles defined.');
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
		// this is a read-only driver
		return array();
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
		// this is a read-only driver
		return array();
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
		throw new AuthException('There are no groups defined.');
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
		// no roles, so user can't be assigned to it
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
		throw new AuthException('There are no groups defined.');
	}
}
