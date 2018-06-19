<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for Service models
 */
class ServiceRegistry
{
    /**
     * @var Service[]
     */
    protected $registry = [];

    /**
     * @var ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * Get instance of the Service Model identified by id
     *
     * @param int $serviceId
     * @return Service
     * @throws NoSuchEntityException
     */
    public function retrieve($serviceId)
    {
        if (isset($this->registry[$serviceId])) {
            return $this->registry[$serviceId];
        }
        $service = $this->serviceFactory->create();
        $service->load($serviceId);
        if (!$service->getId()) {
            throw NoSuchEntityException::singleField('serviceId', $serviceId);
        }
        $this->registry[$serviceId] = $service;
        return $service;
    }

    /**
     * Remove an instance of the Service Model from the registry
     *
     * @param int $serviceId
     * @return void
     */
    public function remove($serviceId)
    {
        unset($this->registry[$serviceId]);
    }

    /**
     * Replace existing Service Model with a new one
     *
     * @param Service $service
     * @return $this
     */
    public function push(Service $service)
    {
        $this->registry[$service->getId()] = $service;
        return $this;
    }
}
