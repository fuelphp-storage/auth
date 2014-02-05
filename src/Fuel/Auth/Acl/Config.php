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
	 * @var  bool  This is a read/write driver
	 */
	protected $readOnly = false;

	/**
	 * @var  array  loaded acl data
	 */
	protected $data = array(
	);

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		// load the auth acl config
		if (is_file($file = $this->getConfig('config_file', null)))
		{
			$this->data = include $file;
		}
		else
		{
			// attempt to create it
			$this->store();
		}
	}

	/**
	 *
	 */
	protected function store()
	{
		if ( ! $this->readOnly)
		{
			// open the file
			$handle = fopen($this->getConfig('config_file'), 'c');
			if ($handle)
			{
				// lock the file, and truncate it
				flock($handle, LOCK_EX);
				ftruncate($handle, 0);

				fwrite($handle, '<?php'.PHP_EOL.'return '.var_export($this->data, true).';'.PHP_EOL);

				// release the lock, and close it
				flock($handle, LOCK_UN);
				fclose($handle);
			}
			else
			{
				throw new AuthException('Can not open "'.$this->config['config_file'].'" for write');
			}
		}
	}
}
