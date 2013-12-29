<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Acl;

/**
 * Dummy acl authentication driver, for test purposes
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Dummy extends Base
{
	/**
	 * @var  array  default driver configuration
	 */
	protected $config = array();

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}
}
