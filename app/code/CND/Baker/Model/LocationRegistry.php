<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for Location models
 */
class LocationRegistry
{
    /**
     * @var Location[]
     */
    protected $registry = [];

    /**
     * @var LocationFactory
     */
    protected $locationFactory;

    /**
     * @param LocationFactory $locationFactory
     */
    public function __construct(LocationFactory $locationFactory)
    {
        $this->locationFactory = $locationFactory;
    }

    /**
     * Get instance of the Location Model identified by id
     *
     * @param int $locationId
     * @return Location
     * @throws NoSuchEntityException
     */
    public function retrieve($locationId)
    {
        if (isset($this->registry[$locationId])) {
            return $this->registry[$locationId];
        }
        $location = $this->locationFactory->create();
        $location->load($locationId);
        if (!$location->getId()) {
            throw NoSuchEntityException::singleField('locationId', $locationId);
        }
        $this->registry[$locationId] = $location;
        return $location;
    }

    /**
     * Remove an instance of the Location Model from the registry
     *
     * @param int $locationId
     * @return void
     */
    public function remove($locationId)
    {
        unset($this->registry[$locationId]);
    }

    /**
     * Replace existing Location Model with a new one
     *
     * @param Location $location
     * @return $this
     */
    public function push(Location $location)
    {
        $this->registry[$location->getId()] = $location;
        return $this;
    }
}
