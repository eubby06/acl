<?php namespace Eubby\Acl;

use Illuminate\Support\ServiceProvider;
use Eubby\Acl\UserModel;
use Eubby\Acl\RoleModel;
use Eubby\Acl\PermissionModel;
use Eubby\Acl\SessionHelper;
use Eubby\Acl\CookieHelper;
use Eubby\Acl\Acl;

class AclServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('eubby/acl', 'eubby/acl');

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->registerSession();

		$this->registerCookie();

		$this->app['acl.user'] = $this->app->share(function($app)
		{
			$model = $app['config']['eubby/acl::users.model'];

			if (method_exists($model, 'setLoginAttributeName'))
			{
				$loginAttribute = $app['config']['eubby/acl::users.login_attribute'];

				forward_static_call_array(
					array($model, 'setLoginAttributeName'),
					array($loginAttribute)
				);
			}

			return new $model;
		});

		$this->app['acl'] = $this->app->share(function($app)
		{
			return new Acl(
							$app['acl.user'], 
							new RoleModel, 
							new ThrottleModel,
							$app['acl.session'],
							$app['acl.cookie']
						);
		});

	}

	protected function registerSession()
	{
		$this->app['acl.session'] = $this->app->share(function($app)
		{
			$key = $app['config']['eubby/acl::session.key'];
			return new SessionHelper($app['session.store'], $key);
		});
	}

	protected function registerCookie()
	{
		$this->app['acl.cookie'] = $this->app->share(function($app)
		{
			$key = $app['config']['eubby/acl::cookie.key'];
			return new CookieHelper($app['cookie'], $key);
		});
	}

	public function provides()
	{
		return array();
	}

}