<?php namespace SynergyWholesale\Laravel;

use SoapClient;
use SynergyWholesale\SynergyWholesale;
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
		$this->app->bindShared('synergy-wholesale', function($app)
		{
			$reseller_id = $app['config']->get('services.synergy-wholesale.reseller_id');
			$api_key = $app['config']->get('services.synergy-wholesale.api_key');

			$client = new SoapClient(null, array('location' => SynergyWholesale::WSDL_URL, 'uri' => ''));
			$responseGenerator = $app->make('SynergyWholesale\ResponseGenerator');

			$logger = $app['log']->getMonolog();

			return new SynergyWholesale($client, $responseGenerator, $logger, $reseller_id, $api_key);
		});

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('hampel/synergy-wholesale-laravel', 'synergy-wholesale', __DIR__);

		$this->app->bind('SynergyWholesale\ResponseGenerator', 'SynergyWholesale\BasicResponseGenerator');
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