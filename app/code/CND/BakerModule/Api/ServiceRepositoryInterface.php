<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\BakerModule\Api;

/**
 * Customer address CRUD interface.
 * @api
 * @since 100.0.2
 */
interface ServiceRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param \CND\BakerModule\Api\Data\ServiceInterface $address
     * @return \CND\BakerModule\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\BakerModule\Api\Data\ServiceInterface $address);

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \CND\BakerModule\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId);

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CND\BakerModule\Api\Data\ServiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer address.
     *
     * @param \CND\BakerModule\Api\Data\ServiceInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\BakerModule\Api\Data\ServiceInterface $address);

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId);
}
