<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Customer location CRUD interface.
 * @api
 * @since 100.0.2
 */
interface LocationRepositoryInterface
{
    /**
     * Save customer location.
     *
     * @param \CND\Baker\Api\Data\LocationInterface $location
     * @return \CND\Baker\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\LocationInterface $location);

    /**
     * Retrieve customer location.
     *
     * @param int $locationId
     * @return \CND\Baker\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($locationId);

    /**
     * Retrieve customers locations matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\LocationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer location.
     *
     * @param \CND\Baker\Api\Data\LocationInterface $location
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\LocationInterface $location);

    /**
     * Delete customer location by ID.
     *
     * @param int $locationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($locationId);
}
