<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Persistence;

use Fuel\Auth\AuthException;

use Fuel\Common\Arr;

/**
 * Auth Persistence file driver class
 *
 * Note: This driver is not thread safe!
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class File extends Base
{
	/**
	 * @var  string  name of the file containing the persistence data
	 */
	protected $file;

	/**
	 * @var  array  loaded persistence data
	 */
	protected $data = [];

	/**
	 * Constructor, read the file store, or create it if it doesn't exist
	 */
	public function __construct($file)
	{
		// unify the file separators
		$this->file = rtrim(str_replace('\\/', DIRECTORY_SEPARATOR, $file), DIRECTORY_SEPARATOR);

		// if the file given is a path, construct the filename
		if (is_dir($this->file))
		{
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_persistence.php';
		}

		// open the file
		if ($handle = @fopen($this->file, 'r'))
		{
			// lock the file
			flock($handle, LOCK_SH);

			// load it's contents
			$this->data = include $this->file;

			// release the lock, and close it
			flock($handle, LOCK_UN);
			fclose($handle);
		}
		else
		{
			// attempt to create it
			$this->store();
		}
	}

	/**
	 * Destructor, write the stored data.
	 */
	public function __destruct()
	{
		// write any data
		$this->store();
	}

	/**
	 * get a value from persistent storage
	 *
	 * @param  string  $key  key of the value to get
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key)
	{
		return Arr::get($this->data, $this->prefix($key), null);
	}

	/**
	 * write a value to persistent storage
	 *
	 * @param  string  $key    key of the value to write
	 * @param  string  $value  the value
	 *
	 * @since 2.0.0
	 */
	public function set($key, $value)
	{
		Arr::set($this->data, $this->prefix($key), $value);
	}

	/**
	 * delete a value from persistent storage
	 *
	 * @param  string  $key  key of the value to delete
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function delete($key)
	{
		return Arr::delete($this->data, $this->prefix($key));
	}

	/**
	 * prefix the key with the IP to make it somewhat unique
	 */
	protected function prefix($key)
	{
		// prefix the key with the users IP
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			return '__'.$_SERVER['REMOTE_ADDR'].'__'.$key;
		}

		return '__127.0.0.1__'.$key;
	}

	/**
	 * write any stored data.
	 */
	protected function store()
	{
		// open the file
		if ($handle = @fopen($this->file, 'c'))
		{
			// lock the file, and truncate it
			flock($handle, LOCK_EX);
			ftruncate($handle, 0);

			// write the data as a PHP array
			fwrite($handle, '<?php'.PHP_EOL.'return '.var_export($this->data, true).';'.PHP_EOL);

			// release the lock, and close it
			flock($handle, LOCK_UN);
			fclose($handle);
		}
		else
		{
			throw new AuthException('Can not open "'.$this->file.'" for write');
		}
	}

}
