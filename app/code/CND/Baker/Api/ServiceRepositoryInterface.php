<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Customer service CRUD interface.
 * @api
 * @since 100.0.2
 */
interface ServiceRepositoryInterface
{
    /**
     * Save customer service.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface $service
     * @return \CND\Baker\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\ServiceInterface $service);

    /**
     * Retrieve customer service.
     *
     * @param int $serviceId
     * @return \CND\Baker\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($serviceId);

    /**
     * Retrieve customers servicees matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\ServiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer service.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface $service
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\ServiceInterface $service);

    /**
     * Delete customer service by ID.
     *
     * @param int $serviceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($serviceId);
}
