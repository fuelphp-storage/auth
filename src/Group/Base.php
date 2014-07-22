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

use Fuel\Auth\Driver;

/**
 * Auth Group driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver implements GroupInterface
{
	/**
	 * @var  bool  These drivers support concurrency
	 */
	protected $hasConcurrency = true;

	/**
	 * @var  bool  By default group drivers don't have nested group support
	 */
	protected $hasNestedGroupSupport = false;

	/**
	 * Base constructor. Prepare all things common for all group drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}

	/**
	 * check if this driver supports nested groups
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasNestedGroupSupport()
	{
		return $this->hasNestedGroupSupport;
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
		// by default, group drivers don't do user cleanup
		return false;
	}

	/**
	 * Assigns a given group to another group (group nesting)
	 *
	 * @param  string  $group    id or name of the group to assign. This group must exist
	 * @param  string  $groupid  id of the group to assign to. This group must exist
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if the group to assign to  does not exist
	 *
	 * @return  mixed  the id of the group assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignGroupToGroup($group, $groupid)
	{
		// by default, group nesting is not supported
		return false;
	}

	/**
	 * Removes a given group to another group (group nesting)
	 *
	 * @param  string  $group    id or name of the group to assign. This group must exist
	 * @param  string  $groupid  id of the group to assign to. This group must exist
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if the group to assign to  does not exist
	 *
	 * @return  mixed  the id of the group removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeGroupFromGroup($group, $groupid)
	{
		// by default, group nesting is not supported
		return false;
	}
}
