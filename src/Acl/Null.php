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

/**
 * Null acl driver
 *
 * This driver doesn't do anything, and can be used if you don't require
 * acl support in your Auth environment
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Null extends Base
{
	/**
	 * @var  bool  This is a ReadOnly driver
	 */
	protected $isReadOnly = true;
}
