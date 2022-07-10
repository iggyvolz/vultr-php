<?php

declare(strict_types=1);

namespace Vultr\VultrPhp\Tests\Suite;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use ReflectionClass;
use Vultr\VultrPhp\Tests\VultrTest;
use Vultr\VultrPhp\VultrAuth;
use Vultr\VultrPhp\VultrClient;
use Vultr\VultrPhp\VultrException;

class VultrClientTest extends VultrTest
{
	public function testAuth()
	{
		$auth = new VultrAuth('Test1234');
		$this->assertEquals('Test1234', $auth->getSecret());
		$this->assertEquals('Test1234', $auth->getBearerToken());
		$this->assertEquals('Bearer '.$auth->getBearerToken(), $auth->getBearerTokenHead());
	}

	public function testClientCreate()
	{
		$client = VultrClient::create('Test1234');
		$this->assertInstanceOf(VultrClient::class, $client);

		$guzzle_factory = new HttpFactory();
		$guzzle_client = new Client();
		$client = VultrClient::create('Test1234', $guzzle_client, $guzzle_factory, $guzzle_factory, $guzzle_factory);
		$this->assertInstanceOf(VultrClient::class, $client);
		$this->assertNull($client->setClient($guzzle_client));
		$this->assertNull($client->setRequestFactory($guzzle_factory));
		$this->assertNull($client->setResponseFactory($guzzle_factory));
		$this->assertNull($client->setStreamFactory($guzzle_factory));

		$mock = $this->getMockBuilder(VultrClient::class)
		->disableOriginalConstructor()
		->setMethods(['setClientHandler'])
		->getMock();

		$mock->expects($this->once())
		->method('setClientHandler')
		->willThrowException(new Exception('I am your father.'));

		$class = new ReflectionClass(VultrClient::class);
		$constructor = $class->getConstructor();
		$constructor->setAccessible(true);
		$this->expectException(VultrException::class);
		$constructor->invokeArgs($mock, ['Test1234', $guzzle_client, $guzzle_factory, $guzzle_factory, $guzzle_factory]);
	}
}
