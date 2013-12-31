<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
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
abstract class Base extends Driver
{
	/**
	 * @var  string  type for drivers extending this base class
	 */
	protected $type = 'group';

	/**
	 * @var  array  global methods, supported by all group drivers
	 *
	 * for every method listed, there MUST be an abstract method definition
	 * in this base class, to ensure the driver implements it!
	 */
	protected $methods = array(
	);
}
