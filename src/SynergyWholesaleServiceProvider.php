<?php namespace SynergyWholesale;

use SoapClient;
use SynergyWholesale\SynergyWholesale as SynergyWholesaleApi;
use Illuminate\Support\ServiceProvider;

class SynergyWholesaleServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('SynergyWholesale\ResponseGenerator', 'SynergyWholesale\BasicResponseGenerator');
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->defineConfiguration();

		$this->app->singleton('synergy-wholesale', function($app)
		{
			$reseller_id = $app['config']->get('synergy-wholesale.reseller_id');
			$api_key = $app['config']->get('synergy-wholesale.api_key');

			$client = new SoapClient(null, array('location' => SynergyWholesaleApi::WSDL_URL, 'uri' => ''));
			$responseGenerator = $app->make('SynergyWholesale\ResponseGenerator');

			$logger = $app['log']->getMonolog();

			return new SynergyWholesaleApi($client, $responseGenerator, $logger, $reseller_id, $api_key);
		});

	}

	protected function defineConfiguration()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/config/synergy-wholesale.php', 'synergy-wholesale'
		);
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
