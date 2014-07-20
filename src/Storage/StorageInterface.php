<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Storage;

use Fuel\Auth\AuthInterface;

interface StorageInterface
{
	/**
	 * Find the unified user id using the login id's from all drivers
	 *
	 * @param  array  $ids  assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the logged-in user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	public function findUnifiedUser(array $ids = []);

	/**
	 * Find the drivers that have an account for a given user id
	 *
	 * @param  int  $id  link id we want to login
	 *
	 * @return  array  array with driver names that have an account for the given id
	 *
	 * @since 2.0.0
	 */
	public function getUnifiedUsers($id);

	/**
	 * Find the drivers that have an account for a given user id, and delete them
	 *
	 * @param  array  $ids  assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the deleted user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	public function deleteUnifiedUser(array $ids = []);
}
