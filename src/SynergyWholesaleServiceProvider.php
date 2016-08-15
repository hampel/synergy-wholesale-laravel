<?php namespace SynergyWholesale;

use SoapClient;
use Psr\Log\LoggerInterface;
use Illuminate\Cache\Repository;
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

		$this->app->singleton('SynergyWholesale\SynergyWholesale', function($app)
		{
			$reseller_id = $app['config']->get('synergy-wholesale.reseller_id');
			$api_key = $app['config']->get('synergy-wholesale.api_key');

			$client = new SoapClient(null, array('location' => SynergyWholesale::WSDL_URL, 'uri' => ''));
			$responseGenerator = $app->make(ResponseGenerator::class);
			$logger = $app->make(LoggerInterface::class);
			$cache = $app->make(Repository::class);

			return new CachingSynergyWholesale($client, $responseGenerator, $logger, $cache, $reseller_id, $api_key);
		});

	}

	protected function defineConfiguration()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/config/synergy-wholesale.php', 'synergy-wholesale'
		);
	}

}
