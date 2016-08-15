<?php namespace SynergyWholesale;

use SoapClient;
use Psr\Log\LoggerInterface;
use Illuminate\Cache\Repository;
use SynergyWholesale\Types\DomainList;
use SynergyWholesale\Commands\Command;
use SynergyWholesale\Commands\DomainInfoCommand;
use SynergyWholesale\Commands\CheckDomainCommand;
use SynergyWholesale\Commands\ListContactsCommand;
use SynergyWholesale\Commands\BulkCheckDomainCommand;
use SynergyWholesale\Responses\BulkCheckDomainResponse;

class CachingSynergyWholesale extends SynergyWholesale
{

	/** @var Repository cache */
	protected $cache;

	/**
	 * Constructor
	 *
	 * @param SoapClient $client					soap client
	 * @param ResponseGenerator $responseGenerator	response generator
	 * @param LoggerInterface $logger				PSR Log interface
	 * @param string $reseller_id 					Synergy Wholesale reseller id
	 * @param string $api_key						Synergy Wholesale api key
	 */
	public function __construct(
		SoapClient $client,
		ResponseGenerator $responseGenerator,
		LoggerInterface $logger = null,
		Repository $cache = null,
		$reseller_id,
		$api_key
	)
	{
		$this->cache = $cache;
		parent::__construct($client, $responseGenerator, $logger, $reseller_id, $api_key);
	}

	protected function cachedExecute(Command $command, $fresh = false)
	{
		$key = $this->deriveSoapCommand($this->getClassName($command));
		$cache_key = config("synergy-wholesale.cache.{$key}.key");
		$cache_expiry = config("synergy-wholesale.cache.{$key}.expiry", 5);

		if (is_null($cache_key))
		{
			$this->log('warning', 'Cache key not found in config', ['key' => "synergy-wholesale.cache.{$key}.key"]);
			return $this->execute($command); // bypass cache if not configured
		}

		if ($suffix = $command->getKey()) $cache_key .= ".{$suffix}"; // add optional suffix

		if ($fresh) $this->cache->forget($cache_key); // clear cache for this key if we're getting fresh data

		return $this->cache->remember($cache_key, $cache_expiry, function() use ($command)	{
			return $this->execute($command);
		});
	}

	protected function cacheForget(Command $command, $classname)
	{
		$key = $this->deriveSoapCommand($classname);
		$cache_key = config("synergy-wholesale.cache.{$key}.key");

		if (is_null($cache_key))
		{
			$this->log('warning', 'Cache key not found in config', ['key' => "synergy-wholesale.cache.{$key}.key"]);
			return;
		}

		if ($suffix = $command->getKey()) $cache_key .= ".{$suffix}"; // add optional suffix

		$this->cache->forget($cache_key);
	}

	/**
	 * @param Commands\BalanceQueryCommand $command
	 * @return Responses\BalanceQueryResponse
	 */
	public function balanceQuery(Commands\BalanceQueryCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\BulkCheckDomainCommand $command
	 * @return Responses\BulkCheckDomainResponse
	 */
	public function bulkCheckDomain(Commands\BulkCheckDomainCommand $command, $fresh = false)
	{
		$cache_prefix = config("synergy-wholesale.cache.bulkCheckDomain.key");
		$cache_expiry = config("synergy-wholesale.cache.bulkCheckDomain.expiry", 1440);

		$domains = collect($command->getDomainList()->getDomainNames());

		$cached = $domains->filter(function ($domain) use ($cache_prefix, $fresh) {
			$cache_key = "{$cache_prefix}.{$domain}";
			if ($fresh)
			{
				$this->cache->forget($cache_key);
				return false;
			}
			return $this->cache->has($cache_key);
		});

		$cached->transform(function ($domain) use ($cache_prefix) {
			$cache_key = "{$cache_prefix}.{$domain}";
			$avail = new \stdClass();
			$avail->domain = $domain;
			$avail->available = $this->cache->get($cache_key) ? 1 : 0;
			return $avail;
		});

		$uncached = $domains->filter(function ($domain) use ($cache_prefix) {
			return !$this->cache->has("{$cache_prefix}.{$domain}");
		});

		if (!$uncached->isEmpty())
		{
			$command = new BulkCheckDomainCommand(new DomainList($uncached->all()));
			/** @var BulkCheckDomainResponse $response */
			$response = $this->execute($command);

			$available = collect($response->getAvailableDomains());
			$available->transform(function ($isAvailable, $domain) use ($cache_prefix, $cache_expiry) {
				$cache_key = "{$cache_prefix}.{$domain}";
				$this->cache->put($cache_key, $isAvailable, $cache_expiry);

				$result = new \stdClass();
				$result->domain = $domain;
				$result->available = $isAvailable ? 1 : 0;
				return $result;
			});

			$cached = $cached->merge($available->values());
		}

		$results = new \stdClass();
		$results->status = "OK";
		$results->domainList = $cached->all();
		return new BulkCheckDomainResponse($results, 'BulkCheckDomainCommand');
	}

	/**
	 * @param Commands\BusinessCheckRegistrationCommand $command
	 * @return Responses\BusinessCheckRegistrationResponse
	 */
	public function businessCheckRegistration(Commands\BusinessCheckRegistrationCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\CanRenewDomainCommand $command
	 * @return Responses\CanRenewDomainResponse
	 */
	public function canRenewDomain(Commands\CanRenewDomainCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\CheckDomainCommand $command
	 * @return Responses\CheckDomainResponse
	 */
	public function checkDomain(Commands\CheckDomainCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\DomainInfoCommand $command
	 * @return Responses\DomainInfoResponse
	 */
	public function domainInfo(Commands\DomainInfoCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\DomainRegisterAUCommand $command
	 * @return Responses\DomainRegisterAUResponse
	 */
	public function domainRegisterAU(Commands\DomainRegisterAUCommand $command)
	{
		$this->cacheForget($command, CheckDomainCommand::class);
		$this->cacheForget($command, BulkCheckDomainCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\DomainRegisterCommand $command
	 * @return Responses\DomainRegisterResponse
	 */
	public function domainRegister(Commands\DomainRegisterCommand $command)
	{
		$this->cacheForget($command, CheckDomainCommand::class);
		$this->cacheForget($command, BulkCheckDomainCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\DomainRegisterUKCommand $command
	 * @return Responses\DomainRegisterUKResponse
	 */
	public function domainRegisterUK(Commands\DomainRegisterUKCommand $command)
	{
		$this->cacheForget($command, CheckDomainCommand::class);
		$this->cacheForget($command, BulkCheckDomainCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\DomainRegisterUSCommand $command
	 * @return Responses\DomainRegisterUSResponse
	 */
	public function domainRegisterUS(Commands\DomainRegisterUSCommand $command)
	{
		$this->cacheForget($command, CheckDomainCommand::class);
		$this->cacheForget($command, BulkCheckDomainCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\EnableAutoRenewalCommand $command
	 * @return Responses\EnableAutoRenewalResponse
	 */
	public function enableAutoRenewal(Commands\EnableAutoRenewalCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\EnableIdProtectionCommand $command
	 * @return Responses\EnableIdProtectionResponse
	 */
	public function enableIDProtection(Commands\EnableIDProtectionCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\GetDomainExtensionOptionsCommand $command
	 * @return Responses\GetDomainExtensionOptionsResponse
	 */
	public function getDomainExtensionOptions(Commands\GetDomainExtensionOptionsCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\GetDomainPricingCommand $command
	 * @return Responses\GetDomainPricingResponse
	 */
	public function getDomainPricing(Commands\GetDomainPricingCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\GetTransferredAwayDomainsCommand $command
	 * @return Responses\GetTransferredAwayDomainsResponse
	 */
	public function getTransferredAwayDomains(Commands\GetTransferredAwayDomainsCommand $command, $fresh = false) {
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\GetUsNexusDataCommand $command
	 * @return Responses\GetUsNexusDataResponse
	 */
	public function getUSNexusData(Commands\GetUSNexusDataCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\ListContactsCommand $command
	 * @return Responses\ListContactsResponse
	 */
	public function listContacts(Commands\ListContactsCommand $command, $fresh = false)
	{
		return $this->cachedExecute($command, $fresh);
	}

	/**
	 * @param Commands\RenewDomainCommand $command
	 * @return Responses\RenewDomainResponse
	 */
	public function renewDomain(Commands\RenewDomainCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\UnlockDomainCommand $command
	 * @return Responses\UnlockDomainResponse
	 */
	public function unlockDomain(Commands\UnlockDomainCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\UpdateContactCommand $command
	 * @return Responses\UpdateContactResponse
	 */
	public function updateContact(Commands\UpdateContactCommand $command)
	{
		$this->cacheForget($command, ListContactsCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\UpdateDomainPasswordCommand $command
	 * @return Responses\UpdateDomainPasswordResponse
	 */
	public function updateDomainPassword(Commands\UpdateDomainPasswordCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

	/**
	 * @param Commands\UpdateNameServersCommand $command
	 * @return Responses\UpdateNameServersResponse
	 */
	public function updateNameServers(Commands\UpdateNameServersCommand $command)
	{
		$this->cacheForget($command, DomainInfoCommand::class);
		return $this->execute($command);
	}

}
