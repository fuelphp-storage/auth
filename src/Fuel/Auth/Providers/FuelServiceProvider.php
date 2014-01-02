<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Providers;

use Fuel\Dependency\ServiceProvider;
use Fuel\Auth\Driver;

/**
 * FuelPHP ServiceProvider class for this package
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array(
		'auth',
		'auth.storage.db',
		'auth.user.config', 'auth.group.config', 'auth.acl.config',
	);

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Auth\Manager
		$this->register('auth', function ($dic, $name = 'default', array $config = array())
		{
			// get the auth config
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}
			$app->getConfig()->load('auth', true);

			// merge the different config sections
			$config = \Arr::merge($app->getConfig()->get('auth.global'), $app->getConfig()->get('auth.'.$name), $config);

			// create the auth manager instance
			$instance = $dic->multiton('Fuel\Auth\Manager', $name, array($config));

			// closure to add a driver to the Auth manager instance
			$addDriver = function($section, $driver) use($dic, $instance) {
				if ($driver['driver'] instanceOf Driver)
				{
					$instance->addDriver($section, $driver['name'], $driver['driver']);
				}
				elseif (strpos('\\', $driver['driver']) !== false and class_exists($driver['driver']))
				{
					$class = $driver['driver'];
					$instance->addDriver($section, $driver['name'], new $class($driver));
				}
				else
				{
					try
					{
						$instance->addDriver($section, $driver['name'], $dic->resolve('auth.'.strtolower($driver['driver']), array($driver)));
					}
					catch (\ResolveException $e)
					{
						$instance->addDriver($section, $driver['name'], $dic->resolve(strtolower($driver['driver']), array($driver)));
					}
				}
			};

			// instantiate and inject the configured auth drivers
			foreach (array_keys($config['drivers']) as $section)
			{
				foreach ($config['drivers'][$section] as $index => $driver)
				{
					$addDriver($section, $driver);
				}
			}

			// instantiate and inject the configured support drivers
			foreach (array('storage', 'persistence') as $driver)
			{
				// instantiate and inject the configured support driver
				if ( ! empty($config[$driver]) and is_array($config[$driver]))
				{
					// make sure it does not have a name
					$config[$driver]['name'] = null;

					// config file present?
					if (isset($config[$driver]['config_file']))
					{
						// was a full path specified?
						if (strpos($config[$driver]['config_file'], DIRECTORY_SEPARATOR) === false)
						{
							// get the current application object
							$stack = $this->container->resolve('requeststack');
							if ($request = $stack->top())
							{
								$app = $request->getApplication();
							}
							else
							{
								$app = $this->container->resolve('application.main');
							}

							$config[$driver]['config_file'] = $app->getPath().'config'.DIRECTORY_SEPARATOR.$config[$driver]['config_file'].'.php';
						}
					}

					// add the driver
					$addDriver($driver, $config[$driver]);
				}
			}

			return $instance;
		});

		/**
		 * Persistence drivers
		 */
		// \Fuel\Auth\Persistence\Session
		$this->register('auth.persistence.session', function ($dic, array $config = array())
		{
			// get the current application object
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}

			return $dic->resolve('Fuel\Auth\Persistence\Session', array($config, $app->getSession()));
		});

		/**
		 * Storage drivers
		 */

		// \Fuel\Auth\Storage\Config
		$this->register('auth.storage.config', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\Storage\Config', array($config));
		});

		// \Fuel\Auth\Storage\Db
		$this->register('auth.storage.db', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\Storage\Db', array($config, $dic->resolve('storage.db', array($config['name']))));
		});

		/**
		 * Auth user drivers
		 */

		// \Fuel\Auth\User\Config
		$this->register('auth.user.config', function ($dic, array $config = array())
		{
			// make sure we have a config filename
			if ( ! isset($config['config_file']))
			{
				$config['config_file'] = 'auth-users';
			}

			// was a full path specified?
			if (strpos($config['config_file'], DIRECTORY_SEPARATOR) === false)
			{
				// get the current application object
				$stack = $this->container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$app = $request->getApplication();
				}
				else
				{
					$app = $this->container->resolve('application.main');
				}

				$config['config_file'] = $app->getPath().'config'.DIRECTORY_SEPARATOR.$config['config_file'].'.php';
			}

			return $dic->resolve('Fuel\Auth\User\Config', array($config, $app->getInput()));
		});

		/**
		 * Auth group drivers
		 */

		// \Fuel\Auth\Group\Config
		$this->register('auth.group.config', function ($dic, array $config = array())
		{
			// make sure we have a config filename
			if ( ! isset($config['config_file']))
			{
				$config['config_file'] = 'auth-groups';
			}

			// was a full path specified?
			if (strpos($config['config_file'], DIRECTORY_SEPARATOR) === false)
			{
				// get the current application object
				$stack = $this->container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$app = $request->getApplication();
				}
				else
				{
					$app = $this->container->resolve('application.main');
				}

				$config['config_file'] = $app->getPath().'config'.DIRECTORY_SEPARATOR.$config['config_file'].'.php';
			}

			return $dic->resolve('Fuel\Auth\Group\Config', array($config));
		});

		/**
		 * Auth acl drivers
		 */

		// \Fuel\Auth\Acl\Config
		$this->register('auth.acl.config', function ($dic, array $config = array())
		{
			// make sure we have a config filename
			if ( ! isset($config['config_file']))
			{
				$config['config_file'] = 'auth-acls';
			}

			// was a full path specified?
			if (strpos($config['config_file'], DIRECTORY_SEPARATOR) === false)
			{
				// get the current application object
				$stack = $this->container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$app = $request->getApplication();
				}
				else
				{
					$app = $this->container->resolve('application.main');
				}

				$config['config_file'] = $app->getPath().'config'.DIRECTORY_SEPARATOR.$config['config_file'].'.php';
			}

			return $dic->resolve('Fuel\Auth\Acl\Config', array($config));
		});
	}
}
