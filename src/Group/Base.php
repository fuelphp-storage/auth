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
	 * Base constructor. Prepare all things common for all group drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
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
}
