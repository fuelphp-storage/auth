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

use Fuel\Auth\AuthException;

/**
 * Auth Config Storage driver
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Config extends Base
{
	/**
	 * @var  array  loaded user link data
	 */
	protected $data = array(
		'__last-issued__' => 0
	);

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		// load the auth group config
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
	 * Find the linked user id using the login id's from all drivers
	 *
	 * @param   array  $ids      assoc array $drivername => $id
	 *
	 * @return  int|false  the id of the logged-in user, or false if none could be found
	 *
	 * @since 2.0.0
	 */
	public function findLinkedUser(array $ids = array())
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

			// process the result
			if (empty($idmatches))
			{
				// no match found, issue a new id
				$id = ++$this->data['__last-issued__'];

				// store the id with the lookup keys
				foreach ($keys as $key)
				{
					$this->data[$key] = $id;
				}

				// update the store
				$this->store();
			}

			elseif (count($idmatches) === 1)
			{
				// return the matched id
				return reset($idmatches);
			}

			else
			{
				throw new AuthException('This is unexpected. Found multiple linked id\'s for a single login!');
			}
		}

		// unable to determine the linked id
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
	public function getLinkedUsers($id)
	{
		// fetch the list of drivers and id's we have for this linked id
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
	public function deleteLinkedUser(array $ids = array())
	{
		// find the id for this user
		if ($linkedId = $this->findLinkedUser($ids))
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

		return $linkedId;
	}

	/**
	 *
	 */
	protected function store()
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
