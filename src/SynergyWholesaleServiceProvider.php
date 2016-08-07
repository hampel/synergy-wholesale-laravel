<?php namespace SynergyWholesale;

use SoapClient;
use Illuminate\Support\ServiceProvider;

class SynergyWholesaleServiceProvider extends ServiceProvider {

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

		$this->app->singleton('SynergyWholesale\SynergyWholesale', function($app)
		{
			$reseller_id = $app['config']->get('synergy-wholesale.reseller_id');
			$api_key = $app['config']->get('synergy-wholesale.api_key');

			$client = new SoapClient(null, array('location' => SynergyWholesale::WSDL_URL, 'uri' => ''));
			$responseGenerator = $app->make('SynergyWholesale\ResponseGenerator');
			$logger = $app->make('Psr\Log\LoggerInterface');

			return new SynergyWholesale($client, $responseGenerator, $logger, $reseller_id, $api_key);
		});

	}

	protected function defineConfiguration()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/config/synergy-wholesale.php', 'synergy-wholesale'
		);
	}

}
