<?php namespace Xwiz\Pepipost;

use Illuminate\Support\ServiceProvider;

class PepipostServiceProvider extends ServiceProvider {

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
		$this->package('xwiz/pepipost');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['pepipost'] = $this->app->share(function($app){
			return new Pepipost($app['view']);
		});

		$this->app->booting(function(){
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Pepipost', 'Xwiz\Pepipost\Facades\Pepipost');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('pepipost');
	}

}
