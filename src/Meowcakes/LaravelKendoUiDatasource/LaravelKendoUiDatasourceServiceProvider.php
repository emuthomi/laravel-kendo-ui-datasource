<?php namespace Meowcakes\LaravelKendoUiDatasource;

use Illuminate\Support\ServiceProvider;

class LaravelKendoUiDatasourceServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		$this->package('meowcakes/laravel-kendo-ui-datasource');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['laravel-kendo-ui-datasource'] = $this->app->share(function($app)
		{
			return new DataSourceManager($app);
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