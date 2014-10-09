<?php namespace Cartalyst\Sentinel\Addons\Social\Laravel;
/**
 * Part of the Sentinel Social package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel Social
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentinel\Addons\Social\Manager;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Cartalyst\Sentinel\Addons\Social\Repositories\LinkRepository;
use Cartalyst\Sentinel\Addons\Social\RequestProviders\IlluminateRequestProvider;

class SocialServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	protected $defer = true;

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		$this->package('cartalyst/sentinel-social', 'cartalyst/sentinel-social', __DIR__.'/..');
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->registerLinkRepository();
		$this->registerRequestProvider();
		$this->registerSession();
		$this->registerSentinelSocial();
	}

	protected function registerLinkRepository()
	{
		$this->app['sentinel.addons.social.repository'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel-social::link'];

			$users = $app['config']['cartalyst/sentinel::users.model'];

			if (class_exists($model) and method_exists($model, 'setUsersModel'))
			{
				forward_static_call_array([$model, 'setUsersModel'], [$users]);
			}

			return new LinkRepository($model);
		});
	}

	protected function registerRequestProvider()
	{
		$this->app['sentinel.addons.social.request'] = $this->app->share(function($app)
		{
			return new IlluminateRequestProvider($app['request']);
		});
	}

	protected function registerSession()
	{
		$this->app['sentinel.addons.social.session'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentinel::cookie.key'].'_social';

			return new IlluminateSession($app['session.store'], $key);
		});
	}

	/**
	 * Registers Sentinel Social.
	 *
	 * @return void
	 */
	protected function registerSentinelSocial()
	{
		$this->app['sentinel.addons.social'] = $this->app->share(function($app)
		{
			$manager = new Manager(
				$app['sentinel'],
				$app['sentinel.addons.social.repository'],
				$app['sentinel.addons.social.request'],
				$app['sentinel.addons.social.session'],
				$app['events']
			);

			$connections = $app['config']['cartalyst/sentinel-social::connections'];

			$manager->addConnections($connections);

			return $manager;
		});

		$this->app->alias('sentinel.addons.social', 'Cartalyst\Sentinel\Addons\Social\Manager');
	}

	/**
	 * {@inheritDoc}
	 */
	public function provides()
	{
		return [
			'sentinel.addons.social.repository',
			'sentinel.addons.social.request',
			'sentinel.addons.social.session',
			'sentinel.addons.social',
		];
	}

}
