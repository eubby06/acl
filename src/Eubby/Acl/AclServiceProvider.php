<?php namespace Eubby\Acl;

use Illuminate\Support\ServiceProvider;
use Eubby\Acl\User;
use Eubby\Acl\Role;
use Eubby\Acl\Permission;
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
		$this->package('eubby/acl');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->app->singleton('acl', function()
		{
			return new Acl(new User, new Role, new Permission);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}