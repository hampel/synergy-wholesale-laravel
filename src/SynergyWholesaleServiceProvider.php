<?php namespace Hampel\SynergyWholesale;

use Hampel\SynergyWholesale\SynergyWholesale;
use Illuminate\Support\ServiceProvider;

class SynergyWholesaleServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('hampel/synergy-wholesale-laravel');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['synergy-wholesale'] = $this->app->share(function($app)
		{
			$reseller_id = $app['config']->get('services.synergy-wholesale.reseller_id');
			$api_key = $app['config']->get('services.synergy-wholesale.api_key');

			return SynergyWholesale::make($reseller_id, $api_key);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('synergy-wholesale');
	}

}