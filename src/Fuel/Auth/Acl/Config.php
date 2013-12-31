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
 * Config based acl authentication driver
 *
 * This driver stores all it's data in a fuel configuration file
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Config extends Base
{
	/**
	 * @var  array  default driver configuration
	 */
	protected $config = array();

	/**
	 *
	 */
	public function __construct($configInstance, array $config = array())
	{
		// store the config instance
		$this->configInstance = $configInstance;

		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}
}
