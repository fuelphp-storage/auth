<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\User;

use Fuel\Auth\Hasher;
use Fuel\Auth\Driver;

/**
 * Auth User driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver implements UserInterface
{
	/**
	 * @var  bool  These drivers support concurrency
	 */
	protected $hasConcurrency = true;

	/**
	 * @var  bool  By default user drivers don't have guest support
	 */
	protected $hasGuestSupport = false;

	/**
	 * @var  bool  By default user drivers don't have shadow login support
	 */
	protected $shadowSupport = false;

	/**
	 * @var  Hasher  used to create password hashes
	 */
	protected $hasher;

	/**
	 * Base constructor. Prepare all things common for all user drivers
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}

	/**
	 * check if this driver supports guest users
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function hasGuestSupport()
	{
		return $this->hasGuestSupport;
	}

	/**
	 * Returns the hash object and creates it if necessary
	 *
	 * @return  Hasher
	 *
	 * @since 1.0.0
	 */
	public function hasher()
	{
		if ( ! $this->hasher)
		{
			// get an instance of our Crypt Hasher
			$this->hasher = new Hasher();
		}

		return $this->hasher;
	}

	/**
	 * Default password hash method
	 *
	 * @param   string  the string to hash
	 * @param   string  the salt to use
	 * @param   string  hash method to use
	 *
	 * @return  string  the hashed string, base64 encoded
	 *
	 * @since 1.0.0
	 */
	public function hash($password, $salt = null, $method = 'pbkdf2')
	{
		switch ($method)
		{
			case 'bcrypt':
				$hash = base64_encode($this->hasher()->bcrypt($password, $salt ?: $this->manager->getConfig('salt', '')));
			break;

			case 'crypt':
				$hash = base64_encode($this->hasher()->crypt($password, $salt ?: $this->manager->getConfig('salt', '')));
			break;

			case 'pbkdf2':
			default:
				$hash = base64_encode($this->hasher()->pbkdf2($password, $salt ?: $this->manager->getConfig('salt', ''), $this->manager->getConfig('iterations', 10000), 32));
		}

		return $hash;
	}

	/**
	 *  Generate a very random salt
	 *
	 *  @param  int  $length  required length of the salt string
	 *
	 *  @return  string  generated random salt
	 */
	public function salt($length)
	{
		return $this->hasher()->salt($length);
	}

	/**
	 *  Generate a quick random user readable string
	 *
	 *  @param  int  $length  required length of the string
	 *
	 *  @return  string  generated random string
	 */
	public function randomString($length)
	{
		// allowed characters
		static $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

		// make sure we have enough length
		while (strlen($chars) < $length)
		{
			$chars .= $chars;
		}

		return substr(str_shuffle($chars),0,$length);
	}
}
