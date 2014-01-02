<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Storage;

use Fuel\Auth\Driver;

/**
 * Auth Storage driver base class
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
	protected $type = 'storage';

	/**
	 * @var  array  empty array, storage drivers don't have global methods
	 */
	protected $methods = array();

	/**
	 * Base constructor. Prepare all things common for all storage drivers
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Find the linked user id using the login id's from all drivers
	 *
	 * @param  array  $ids  assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the logged-in user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	abstract public function findLinkedUser(array $ids = array());

	/**
	 * Find the drivers that have an account for a given user id
	 *
	 * @param  int  $id  link id we want to login
	 *
	 * @return  array  array with driver names that have an account for the given id
	 *
	 * @since 2.0.0
	 */
	abstract public function getLinkedUsers($id);

	/**
	 * Find the drivers that have an account for a given user id, and delete them
	 *
	 * @param  array  $ids  assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the deleted user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	abstract public function deleteLinkedUser(array $ids = array());
}
