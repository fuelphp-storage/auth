<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Storage;

use Fuel\Auth\AuthException;

use Fuel\Common\Arr;

/**
 * Auth Storage file driver class
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
	 * @var  string  name of the file containing the auth global data
	 */
	protected $file;

	/**
	 * @var  array  loaded persistence data
	 */
	protected $data = array(
		'__last-issued__' => 0
	);

	/**
	 * Constructor, read the file store, or create it if it doesn't exist
	 */
	public function __construct(array $config = [])
	{
		// make sure we have a file or a path
		if ( ! isset($config['file']))
		{
			// use the system temp directory
			$config['file'] = sys_get_temp_dir();
		}

		// unify the file separators
		$this->file = rtrim(str_replace('\\/', DIRECTORY_SEPARATOR, $config['file']), DIRECTORY_SEPARATOR);

		// if the file given is a path, construct the filename
		if (is_dir($this->file))
		{
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_storage.php';
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
	 * Find the unified user id using the login id's from all drivers
	 *
	 * @param   array  $ids      assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the logged-in user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	public function findUnifiedUser(array $ids = array())
	{
		// construct the list of lookup keys
		$keys = array();
		foreach ($ids as $driver => $id)
		{
			if ($id !== false)
			{
				$keys[] = $driver.'::'.trim($id);
			}
		}

		// do an id lookup for the keys found
		if ($keys)
		{
			$idmatches = array();
			foreach ($keys as $key)
			{
				if (isset($this->data[$key]) and ! in_array($this->data[$key], $idmatches))
				{
					$idmatches[] = $this->data[$key];
				}
			}

			$updateStore = false;

			// no match found, issue a new id
			if (empty($idmatches))
			{
				$unifiedId = ++$this->data['__last-issued__'];
				$updateStore = true;
			}

			// one match found, get it
			elseif (count($idmatches) === 1)
			{
				// get the matched id
				$unifiedId = reset($idmatches);
			}

			// multiple matches. this should not happen!
			else
			{
				throw new AuthException('This is unexpected. Found multiple unified id\'s for a single login!');
			}

			// update the id store
			foreach ($keys as $key)
			{
				if ( ! isset($this->data[$key]))
				{
					$this->data[$key] = $unifiedId;
					$updateStore = true;
				}
			}

			// update the store if needed
			if ($updateStore)
			{
				$this->store();
			}

			// and return the matched id
			return $unifiedId;
		}

		// unable to determine the unified id
		return false;
	}

	/**
	 * Find the drivers that have an account for a given user id
	 *
	 * @param  int  $id  link id we want to login
	 *
	 * @return  array  array with driver names that have an account for the given id
	 *
	 * @since 2.0.0
	 */
	public function getUnifiedUsers($id)
	{
		// fetch the list of drivers and id's we have for this unified id
		$keys = array_intersect($this->data, array($id));
		unset($keys['__last-issued__']);

		// construct the driver => id mapping
		$result = array();
		foreach ($keys as $key => $id)
		{
			$key = explode('::', $key);
			$result[$key[0]] = $key[1];
		}

		return $result;
	}

	/**
	 * Find the drivers that have an account for a given user id, and delete them
	 *
	 * @param  array  $ids  assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the deleted user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	public function deleteUnifiedUser(array $ids = array())
	{
		// find the id for this user
		if ($unifiedId = $this->findUnifiedUser($ids))
		{
			// construct the list of lookup keys
			$keys = array();
			foreach ($ids as $driver => $id)
			{
				if ($id !== false)
				{
					// delete the entry
					unset($this->data[$driver.'::'.trim($id)]);
				}
			}

			// update the store
			$this->store();
		}

		return $unifiedId;
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
