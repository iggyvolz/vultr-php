<?php

declare(strict_types=1);

namespace Vultr\VultrPhp\Services\LoadBalancers;

use Vultr\VultrPhp\Services\VultrService;
use Vultr\VultrPhp\Util\VultrUtil;
use Vultr\VultrPhp\VultrClientException;

class LoadBalancerService extends VultrService
{
	/**
	 * @see https://www.vultr.com/api/#operation/get-load-balancer
	 * @param $id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @throws VultrException
	 * @return LoadBalancer
	 */
	public function getLoadBalancer(string $id) : LoadBalancer
	{
		$object = $this->getObject('load-balancers/'.$id, new LoadBalancer());
		$this->setRules($object);
		return $object;
	}

	/**
	 * @see https://www.vultr.com/api/#operation/list-load-balancers
	 * @param $options - ListOptions|null - Interact via reference.
	 * @throws LoadBalancerException
	 * @return LoadBalancer[]
	 */
	public function getLoadBalancers(?ListOptions &$options = null) : array
	{
		$objects = $this->getListObjects('load-balancers', new LoadBalancer(), $options);
		foreach ($objects as &$object)
		{
			$this->setRules($object);
		}
		return $objects;
	}

	/**
	 * @see https://www.vultr.com/api/#operation/create-load-balancer
	 * @param $create - LoadBalancerCreate
	 * @throws LoadBalancerException
	 * @return LoadBalancer
	 */
	public function createLoadBalancer(LoadBalancerCreate $create) : LoadBalancer
	{

	}

	/**
	 * @see https://www.vultr.com/api/#operation/update-load-balancer
	 * ????
	 */
	public function updateLoadBalancer()
	{

	}

	/**
	 * @see https://www.vultr.com/api/#operation/delete-load-balancer
	 * @param $id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @return void
	 */
	public function deleteLoadBalancer(string $id) : void
	{
		$this->deleteObject('load-balancers/'.$id, new LoadBalancer());
	}

	/**
	 * @see https://www.vultr.com/api/#operation/list-load-balancer-forwarding-rules
	 * @param $id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @param $options - ListOptions|null - Interact via reference.
	 * @throws LoadBalancerException
	 * @return ForwardRule[]
	 */
	public function getForwardingRules(string $id, ?ListOptions &$options = null) : array
	{
		return $this->getListObjects('load-balancers/'.$id.'/forwarding-rules', new ForwardRule(), $options);
	}

	/**
	 * @see https://www.vultr.com/api/#operation/get-load-balancer-forwarding-rule
	 * @param $loadbalancer_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @param $forward_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @return FowardRule
	 */
	public function getForwardingRule(string $loadbalancer_id, string $forward_id) : ForwardRule
	{
		return $this->getObject('load-balancers/'.$loadbalancer_id.'/forwarding-rules/'.$forward_id, new ForwardRule());
	}

	/**
	 * @see https://www.vultr.com/api/#operation/create-load-balancer-forwarding-rules
	 * @param $id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @return void
	 */
	public function createForwardingRule(string $id, ForwardRule $rule) : void
	{
		try
		{
			$this->getClientHandler()->post('load-balancers/'.$id.'/forwarding-rules', $rule->getInitializedProps());
		}
		catch (VultrClientException $e)
		{
			throw new LoadBalancerException('Failed to create forwarding rule for load balancer '.$id.': '.$e->getMessage(), $e->getHTTPCode(), $e);
		}
	}

	/**
	 * @see https://www.vultr.com/api/#operation/delete-load-balancer-forwarding-rule
	 * @param $loadbalancer_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @param $forward_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @return void
	 */
	public function deleteForwardRule(string $loadbalancer_id, string $forward_id) : void
	{
		$this->deleteObject('load-balancers/'.$loadbalancer_id.'/forwarding-rules/'.$forward_id, new ForwardRule());
	}

	/**
	 * @see https://www.vultr.com/api/#operation/list-loadbalancer-firewall-rules
	 * @param $id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @param $options - ListOptions|null - Interact via reference.
	 * @throws LoadBalancerException
	 * @return FirewallRule[]
	 */
	public function getFirewallRules(string $id, ?ListOptions &$options = null) : array
	{
		return $this->getListObjects('load-balancers/'.$id.'/firewall-rules', new FirewallRule(), $options);
	}

	/**
	 * @see https://www.vultr.com/api/#operation/get-loadbalancer-firewall-rule
	 * @param $loadbalancer_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @param $firewall_id - string - Example: cb676a46-66fd-4dfb-b839-443f2e6c0b60
	 * @throws LoadBalancerException
	 * @return FirewallRule
	 */
	public function getFirewallRule(string $loadbalancer_id, string $firewall_id) : FirewallRule
	{
		return $this->getObject('load-balancers/'.$loadbalancer_id.'/firewall-rules/'.$firewall_id, new FirewallRule());
	}

	/**
	 * @throws VultrException
	 */
	private function setRules(LoadBalancer &$object) : void
	{
		$rules = [];
		foreach ($object->getForwardingRules() as $rule)
		{
			$rules[] = VultrUtil::mapObject($rule, new ForwardRule());
		}
		$object->setForwardingRules($rules);

		$rules = [];
		foreach ($object->getFirewallRules() as $rule)
		{
			$rules[] = VultrUtil::mapObject($rule, new FirewallRule());
		}
		$object->setFirewallRules($rules);
	}
}
