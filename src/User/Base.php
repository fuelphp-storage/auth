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
	public function hasher($hasher = null)
	{
		if ( ! $this->hasher)
		{
			// create an instance of the default Crypt Hasher
			$this->setHasher();
		}

		return $this->hasher;
	}

	/**
	 * Sets the hash object and creates it if necessary
	 *
	 * @return  Hasher
	 *
	 * @since 1.0.0
	 */
	public function setHasher($hasher = null)
	{
		// was one passed
		if ($hasher and $hasher instanceOf Hasher)
		{
			// use it
			$this->hasher = $hasher;
		}
		else
		{
			// get an instance of our Crypt Hasher
			$this->hasher = new Hasher();
		}
	}

	/**
	 * Default password hash method
	 *
	 * @param   string  the string to hash
	 * @param   string  the individual-salt to use
	 * @param   string  the method to use to hash the string
	 *
	 * @return  string  the hashed string, base64 encoded
	 *
	 * @since 1.0.0
	 */
	public function hash($password, $salt = '', $method = 'pbkdf2')
	{
		switch ($method)
		{
			case 'pbkdf2':
				$hash = base64_encode($this->hasher()->pbkdf2($password, (string) $salt.$this->manager->getConfig('salt', ''), $this->manager->getConfig('iterations', 10000), 32));
			break;

			case 'crypt':
			case 'bcrypt':
				$hash = base64_encode($this->hasher()->{$method}($password, (string) $salt.$this->manager->getConfig('salt', '')));
			break;

			default:
				if (is_callable(array($this->hasher(), $method)))
				{
					$hash = base64_encode($this->hasher()->{$method}($password, (string) $salt.$this->manager->getConfig('salt', '')));
				}
				else
				{
					throw new AuthException('There is no hashing method called "'.$method.'" defined in the loaded hasher');
				}
			break;
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
