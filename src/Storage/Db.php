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

/**
 * Auth DB Storage driver
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Db extends Base
{
	/**
	 * @var  array  DB driver configuration
	 */
	protected $config = array();

	/**
	 * @var  object  DB driver instance
	 */
	protected $db;

	/**
	 *
	 */
	public function __construct(array $config = array(), $db)
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);

		// store the DB instance we're going to use
		$this->db = $db;
	}
}
