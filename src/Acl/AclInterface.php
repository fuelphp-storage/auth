<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Acl;

use Fuel\Auth\AuthInterface;

interface AclInterface extends AuthInterface
{
	/**
	 * Create a new permission, optionally with one or more defined actions
	 *
	 * @param  string  $name     permission to create
	 * @param  array   $actions  indexed array with action names
	 *
	 * @return  boolean  true if the permission was succesfully created, false if not
	 */
	public function createPermission($name, Array $actions = []);

	/**
	 * Update the action list of a permission
	 *
	 * @param  string  $name     permission to update
	 * @param  array   $actions  indexed array with action names, replaces the existing list
	 *
	 * @return  boolean  true if the permission was succesfully updated, false if not
	 */
	public function updatePermission($name, Array $actions = []);

	/**
	 * Delete an existing permission
	 *
	 * @param  string  $name  permission to delete
	 *
	 * @return  boolean  true if the permission was deleted, false if deletion failed
	 */
	public function deletePermission($name);

	/**
	 * Assign a defined Permission (and a possible (subset of) actions
	 * to a valid driver type and value. Optionally, it can be negated by
	 * setting the revoke flag
	 *
	 * @param  string   $type     Driver type ('role', 'group', etc) the value refers to
	 * @param  string   $name     permission to assign
	 * @param  array    $actions  indexed array with (a subset of) the defined action names
	 * @param  boolean  $revoke   optional, if true the permission assigned removes that permission
	 *
	 * @return  boolean  true if the assignment succeeded, false if not
	 */
	public function assignPermissionTo($type, $value, $name, Array $actions = [], $revoke = false);

	/**
	 * Remove an assigned Permission and a possible (subset of) actions
	 * to a valid driver type and value. If this removes all actions assigned, the entire
	 * Permission assignment is removed. If not, the action list is updated
	 *
	 * @param  string  $type     Driver type ('role', 'group', etc) the value refers to
	 * @param  string  $name     permission to remove
	 * @param  array   $actions  indexed array with (a subset of) the assigned action names
	 *
	 * @return  boolean  true if the removal succeeded, false if not
	 */
//	public function removePermissionFrom($type, $value, $name, Array $actions = []);

	/**
	 * Check if a permission has been assigned to a user
	 *
	 * @param  string  $permisson  permission to check
	 * @param  string  $user       user to check. if not given, the current logged-in user will be checked
	 *
	 * @return  boolean  true if the user has the permission, false if not
	 */
//	public function hasPermission($permission, $user = null);
}
