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

use Fuel\Auth\AuthException;

use Fuel\Common\Arr;


/**
 * File acl authentication driver
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
	 * @var  string  name of the file containing the acl data
	 */
	protected $file;

	/**
	 * @var  array  loaded acl data
	 */
	protected $data = ['_permissions' => [], '_assignments' => []];

	/**
	 * Constructor, read the file store, or create it if it doesn't exist
	 */
	public function __construct(array $config = [])
	{
		// deal with the config
		parent::__construct($config);

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
			$this->file .= DIRECTORY_SEPARATOR.'fuel_auth_acls.php';
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
	 * Create a new permission, optionally with one or more defined actions
	 *
	 * @param  string  $name     permission to create
	 * @param  array   $actions  indexed array with action names
	 *
	 * @throws AuthException  if the name passed is not valid
	 * @throws AuthException  if the name passed is already defined
	 *
	 * @return  boolean  true if the permission was succesfully created, false if not
	 */
	public function createPermission($name, Array $actions = [])
	{
		// validate the name
		if (empty($name) or ! is_string($name) or strpos($name, '*') !== false)
		{
			throw new AuthException('Permission name passed is not valid');
		}

		// make sure it doesn't exist yet
		if (Arr::get($this->data['_permissions'], $name) !== null)
		{
			throw new AuthException('Permission name passed is already defined');
		}

		Arr::set($this->data['_permissions'], $name, $actions);

		return true;
	}

	/**
	 * Update the action list of a permission
	 *
	 * @param  string  $name     permission to update
	 * @param  array   $actions  indexed array with action names, replaces the existing list
	 *
	 * @throws AuthException  if the name passed is not valid
	 * @throws AuthException  if the name passed is not defined
	 *
	 * @return  boolean  true if the permission was succesfully updated, false if not
	 */
	public function updatePermission($name, Array $actions = [])
	{
		// validate the name
		if (empty($name) or ! is_string($name) or strpos($name, '*') !== false)
		{
			throw new AuthException('Permission name passed is not valid');
		}

		// make sure it does exist
		if (($permission = Arr::get($this->data['_permissions'], $name)) === null)
		{
			throw new AuthException('Permission name passed is not defined');
		}

		// make sure the name refers to an end-node in the permission tree
		if (Arr::isAssoc($permission))
		{
			throw new AuthException('Permission name passed is not an end-node and doesn\'t have any actions');
		}

		Arr::set($this->data['_permissions'], $name, $actions);

		return true;
	}

	/**
	 * Delete an existing permission
	 *
	 * @param  string  $name  permission to delete
	 *
	 * @throws AuthException  if the name passed is not valid
	 * @throws AuthException  if the name passed is not defined
	 *
	 * @return  boolean  true if the permission was deleted, false if deletion failed
	 */
	public function deletePermission($name)
	{
		// validate the name
		if (empty($name) or ! is_string($name) or strpos($name, '*') !== false)
		{
			throw new AuthException('Permission name passed is not valid');
		}

		// make sure it does exist
		if (Arr::get($this->data['_permissions'], $name) === null)
		{
			throw new AuthException('Permission name passed is not defined');
		}

		// delete the permission
		if (Arr::delete($this->data['_permissions'], $name))
		{
			// strip the lowest level
			$parent = explode('.', $name);
			array_pop($parent);
			$parent = implode('.', $parent);

			// are we at the top?
			if ($parent !== $name)
			{
				// anything present at parent level?
				if ( ! Arr::get($this->data['_permissions'], $parent, true))
				{
					// delete the parent level too
					return $this->deletePermission($parent);
				}
			}

			// and any assignments for this permission
			if (Arr::delete($this->data['_assignments'], $name))
			{
				// strip the lowest level
				$parent = explode('.', $name);
				array_pop($parent);
				$parent = implode('.', $parent);

				// are we at the top?
				if ($parent !== $name)
				{
					// anything present at parent level?
					if ( ! Arr::get($this->data['_assignments'], $parent, true))
					{
						// delete the parent level too
						return $this->deletePermission($parent);
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Assign a defined Permission (and a possible (subset of) actions
	 * to a valid driver type and value. Optionally, it can be negated by
	 * setting the revoke flag
	 *
	 * @param  string   $type     Driver type ('role', 'group', etc) the value refers to
	 * @param  string   $name     permission to assign
	 * @param  array    $actions  indexed array with (a subset of) the defined action names
	 * @param  boolean  $revoke   optional, if true the permission assigned removes that permission
	 *
	 * @throws AuthException  if the name passed is not valid
	 * @throws AuthException  if the name passed is not defined
	 * @throws AuthException  if the type passed is not valid
	 * @throws AuthException  if the value passed is not valid
	 * @throws AuthException  if the action list passed is not valid
	 *
	 * @return  boolean  true if the assignment succeeded, false if not
	 */
	public function assignPermissionTo($type, $value, $name, Array $actions = [], $revoke = false)
	{
		// validate the name
		if (empty($name) or ! is_string($name) or strpos($name, '*') !== false)
		{
			throw new AuthException('Permission name passed is not valid');
		}

		// make sure it does exist
		if (($permission = Arr::get($this->data['_permissions'], $name)) === null)
		{
			throw new AuthException('Permission name passed is not defined');
		}

		// validate the actions list passed
		if (array_diff($actions, $permission))
		{
			throw new AuthException('One or more actions passed are invalid for the given permission');
		}

		// validate the value
		if (empty($value) or ! is_string($value))
		{
			throw new AuthException('Type value passed is not valid');
		}

		// check if this is a valid value for the type
		try
		{
			$lookup = $this->manager->{'get'.ucfirst($type)}($value);
		}
		catch (AuthException $e)
		{
			throw new AuthException('There is no driver loaded for the type "'.$type.'" passed');
		}
		if ( ! $lookup)
		{
			throw new AuthException('Value passed is not valid for the given type');
		}

		// make sure the revoke flag is a boolean
		$revoke = (bool) $revoke;

		// store the assignment
		Arr::set($this->data['_assignments'], $name, array('actions' => $actions, 'revoke' => $revoke));

		return true;
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
