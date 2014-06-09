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
	 * Base constructor. Prepare all things common for all role drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}
}
