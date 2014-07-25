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

use Fuel\Auth\Driver;

/**
 * Auth Acl driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver implements AclInterface
{
	/**
	 * @var  bool  These drivers support concurrency
	 */
	protected $hasConcurrency = true;

	/**
	 * Base constructor. Prepare all things common for all acl drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
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
		// hooks that must flush (part of) the ACL cache
		switch ($hook)
		{
			// flush a specific user cache
			case 'login':
			case 'forceLogin':
			case 'deleteUser':
			case 'assignUserToGroup':
			case 'removeUserFromGroup':
			case 'assignUserToRole':
			case 'removeUserFromRole':
				$this->flushCache($hook, reset($args));
			break;

			// flush all user caches
			case 'deleteGroup':
			case 'assignGroupToGroup':
			case 'removeGroupFromGroup':
			case 'deleteRole':
			case 'assignRoleToRole':
			case 'removeRoleFromRole':
			case 'updatePermission':
			case 'deletePermission':
			case 'assignPermissionTo':
			case 'deletePermissionFrom':
				$this->flushCache($hook);
			break;
		}

		// unknown hook requested
		return parent::callHook($hook, $args);
	}

	/**
	 * Flush the ACL cache for a specific user, or for all users if none given
	 *
	 * @param  string  hook triggered
	 * @param  string  optionally, a unified user id
	 *
	 * @since 2.0.0
	 */
	protected function flushCache($hook, $id = null)
	{
		// not implemented in the base class, functionality is specific to each driver
	}
}
