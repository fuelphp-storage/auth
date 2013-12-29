<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\User;

/**
 * Dummy user authentication driver, for test purposes
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
	 * @var  int  When logged in, the id of the current user
	 */
	protected $currentUser;

	/**
	 * @var  array  dummy guest data
	 */
	protected $guest = array(
		'userid'   => 0,
		'username' => 'Guest',
		'password' => '-not-used-',
		'fullname' => 'Guest User',
		'email'    => 'guest@example.org',
	);

	/**
	 * @var  array  dummy user data
	 */
	protected $data = array(
		'userid'   => 1,
		'username' => 'Dummy',
		'password' => 'Password',
		'fullname' => 'Dummy User',
		'email'    => 'dummy@example.org',
	);

	/**
	 *
	 */
	public function __construct(array $config = array())
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}

	/**
	 * Check for a logged-in user. Check uses persistence data to restore
	 * a logged-in user if needed and supported by the driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function check()
	{
		if ( ! $this->isLoggedIn())
		{
			 $persistence = $this->manager->getDriver('persistence');
			 if ( ! $persistence or ($this->currentUser = $persistence->get('user')) === null)
			 {
				return false;
			 }
		}

		return true;
	}

	/**
	 * Login user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function login($user = '', $password = '')
	{
		if ($this->data['username'] == $user and $this->data['password'] == $password)
		{
			$this->currentUser = $this->data['userid'];

			if ($persistence =$this->manager->getDriver('persistence'))
			{
				$persistence->set('user', $this->currentUser);
			}
			return true;
		}

		return false;
	}

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn()
	{
		return $this->currentUser !== null;
	}

	/**
	 * Logout user
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		$this->currentUser = $this->getConfig('guest_account', false) ? 0 : null;
		if ($persistence =$this->manager->getDriver('persistence'))
		{
			$persistence->delete('user');
		}
		return true;
	}

	/**
	 * get a user data item
	 *
	 * @param  string  $key      the field to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function get($key = null, $value = null)
	{
		if ($this->isLoggedIn())
		{
			if ($this->currentUser === 0)
			{
				return func_num_args() ? \Arr::get($this->guest, $key, $default) : $this->guest;
			}
			else
			{
				return func_num_args() ? \Arr::get($this->data, $key, $default) : $this->data;
			}
		}

		return null;
	}
}
