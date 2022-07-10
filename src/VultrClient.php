<?php

declare(strict_types=1);

namespace Vultr\VultrPhp;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;
use Vultr\VultrPhp\Services;

class VultrClient
{
	private VultrClientHandler $client;

    public readonly Services\Account\AccountService $account;
    public readonly Services\Applications\ApplicationService $applications;
    public readonly Services\Backups\BackupService $backups;
    public readonly Services\BareMetal\BareMetalService $baremetal;
    public readonly Services\Billing\BillingService $billing;
    public readonly Services\BlockStorage\BlockStorageService $blockstorage;
    public readonly Services\DNS\DNSService $dns;
    public readonly Services\Firewall\FirewallService $firewall;
    public readonly Services\Instances\InstanceService $instances;
    public readonly Services\ISO\ISOService $iso;
    public readonly Services\Kubernetes\KubernetesService $kubernetes;
    public readonly Services\LoadBalancers\LoadBalancerService $loadbalancers;
    public readonly Services\ObjectStorage\ObjectStorageService $objectstorage;
    public readonly Services\OperatingSystems\OperatingSystemService $operating_system;
    public readonly Services\Plans\PlanService $plans;
    public readonly Services\ReservedIP\ReservedIPService $reserved_ip;
    public readonly Services\Regions\RegionService $regions;
    public readonly Services\Snapshots\SnapshotService $snapshots;
    public readonly Services\SSHKeys\SSHKeyService $ssh_keys;
    public readonly Services\StartupScripts\StartupScriptService $startup_scripts;
    public readonly Services\Users\UserService $users;
    public readonly Services\VPC\VPCService $vpc;

	/**
	 * @param $http - PSR18 ClientInterface - https://www.php-fig.org/psr/psr-18/
	 * @param $request - PSR17 RequestFactoryInterface - https://www.php-fig.org/psr/psr-17/#21-requestfactoryinterface
	 * @param $response - PSR17 ResponseFactoryInterface - https://www.php-fig.org/psr/psr-17/#22-responsefactoryinterface
	 * @param $stream - PSR17 StreamFactoryInterface - https://www.php-fig.org/psr/psr-17/#22-responsefactoryinterface
	 */
	private function __construct(
		string $API_KEY,
		?ClientInterface $http = null,
		?RequestFactoryInterface $request = null,
		?ResponseFactoryInterface $response = null,
		?StreamFactoryInterface $stream = null
	)
	{
        foreach((new \ReflectionClass(self::class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            unset($this->{$property->name});
        }
		try
		{
			$this->setClientHandler($API_KEY, $http, $request, $response, $stream);
		}
		catch (Throwable $e)
		{
			throw new VultrException('Failed to initialize client: '.$e->getMessage(), VultrException::DEFAULT_CODE, null, $e);
		}
	}

	public static function create(
		string $API_KEY,
		?ClientInterface $http = null,
		?RequestFactoryInterface $request = null,
		?ResponseFactoryInterface $response = null,
		?StreamFactoryInterface $stream = null
	) : VultrClient
	{
		return new VultrClient($API_KEY, $http, $request, $response, $stream);
	}

	public function __get(string $name)
	{
        $type = (new \ReflectionClass(self::class))->getProperty($name)?->getType();
        if(!$type instanceof \ReflectionNamedType) {
            return null;
        }
        $class = $type->getName();

		if ($class !== null)
		{
			return $this->$name ??=  new $class($this, $this->client);
		}

		return null;
	}

	public function setClient(ClientInterface $http) : void
	{
		$this->client->setClient($http);
	}

	public function setRequestFactory(RequestFactoryInterface $request) : void
	{
		$this->client->setRequestFactory($request);
	}

	public function setResponseFactory(ResponseFactoryInterface $response) : void
	{
		$this->client->setResponseFactory($response);
	}

	public function setStreamFactory(StreamFactoryInterface $stream) : void
	{
		$this->client->setStreamFactory($stream);
	}

	protected function setClientHandler(
		string $API_KEY,
		?ClientInterface $http = null,
		?RequestFactoryInterface $request = null,
		?ResponseFactoryInterface $response = null,
		?StreamFactoryInterface $stream = null
	) : void
	{
		$this->client = new VultrClientHandler(
			new VultrAuth($API_KEY),
			$http ?: Psr18ClientDiscovery::find(),
			$request ?: Psr17FactoryDiscovery::findRequestFactory(),
			$response ?: Psr17FactoryDiscovery::findResponseFactory(),
			$stream ?: Psr17FactoryDiscovery::findStreamFactory()
		);
	}
}
