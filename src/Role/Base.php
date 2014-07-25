<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Role;

use Fuel\Auth\Driver;

/**
 * Auth Role driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver implements RoleInterface
{
	/**
	 * @var  bool  These drivers support concurrency
	 */
	protected $hasConcurrency = true;

	/**
	 * @var  bool  By default role drivers don't have nested role support
	 */
	protected $hasNestedRoleSupport = false;

	/**
	 * Base constructor. Prepare all things common for all role drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}

	/**
	 * check if this driver supports nested roles
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasNestedRoleSupport()
	{
		return $this->hasNestedRoleSupport;
	}

	/**
	 * Assigns a given role to another role (role nesting)
	 *
	 * @param  string  $role     id or name of the role to assign. This role must exist
	 * @param  string  $roleid  id of the role to assign to. This role must exist
	 *
	 * @throws  AuthException  if the requested role does not exist
	 * @throws  AuthException  if the role to assign to  does not exist
	 *
	 * @return  mixed  the id of the role assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignRoleToRole($role, $roleid)
	{
		// by default, role nesting is not supported
		return false;
	}

	/**
	 * Removes a given role to another role (role nesting)
	 *
	 * @param  string  $role    id or name of the role to assign. This role must exist
	 * @param  string  $roleid  id of the role to assign to. This role must exist
	 *
	 * @throws  AuthException  if the requested role does not exist
	 * @throws  AuthException  if the role to assign to  does not exist
	 *
	 * @return  mixed  the id of the role removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeRoleFromRole($role, $roleid)
	{
		// by default, role nesting is not supported
		return false;
	}
}
