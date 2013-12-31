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
}
