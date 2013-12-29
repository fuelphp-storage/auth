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
		'auth.user.dummy', 'auth.group.dummy', 'auth.acl.dummy',
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

			// closure to add a driver
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

			// instantiate and inject the configured persistence driver
			if ( ! empty($config['persistence']))
			{
				$config['persistence']['name'] = null;
				$addDriver('persistence', $config['persistence']);
			}

			return $instance;
		});

		/**
		 * Persistence drivers
		 */
		// \Fuel\Auth\Persistence\Session
		$this->register('auth.persistence.session', function ($dic, array $config = array())
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

			return $dic->resolve('Fuel\Auth\Persistence\Session', array($config, $app->getSession()));
		});

		/**
		 * Storage drivers
		 */

		// \Fuel\Auth\Storage\Db
		$this->register('auth.storage.db', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\Storage\Db', array($config, $dic->resolve('storage.db', array($config['name']))));
		});

		/**
		 * Auth user drivers
		 */

		// \Fuel\Auth\User\Dummy
		$this->register('auth.user.dummy', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\User\Dummy', array($config));
		});

		/**
		 * Auth group drivers
		 */

		// \Fuel\Auth\Group\Dummy
		$this->register('auth.group.dummy', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\Group\Dummy', array($config));
		});

		/**
		 * Auth acl drivers
		 */

		// \Fuel\Auth\Acl\Dummy
		$this->register('auth.acl.dummy', function ($dic, array $config = array())
		{
			return $dic->resolve('Fuel\Auth\Acl\Dummy', array($config));
		});
	}
}
