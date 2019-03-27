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
		$this->app->bind(ResponseGenerator::class, BasicResponseGenerator::class);
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->defineConfiguration();

		$this->app->singleton(SynergyWholesale::class, function()
		{
			$reseller_id = $this->app['config']->get('synergy-wholesale.reseller_id');
			$api_key = $this->app['config']->get('synergy-wholesale.api_key');

			$client = new SoapClient(null, array('location' => SynergyWholesale::WSDL_URL, 'uri' => ''));
			$responseGenerator = $this->app->make(ResponseGenerator::class);
			$logger = $this->app['log'];
			$cache = $this->app['cache.store'];

			return new CachingSynergyWholesale($client, $responseGenerator, $logger, $cache, $reseller_id, $api_key);
		});

	}

	protected function defineConfiguration()
	{
		$this->publishes([
			__DIR__ . '/config/synergy-wholesale.php' => config_path('synergy-wholesale.php'),
		], 'config');

		$this->mergeConfigFrom(
			__DIR__ . '/config/synergy-wholesale.php', 'synergy-wholesale'
		);
	}

}
