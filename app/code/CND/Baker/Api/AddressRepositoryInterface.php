<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Customer address CRUD interface.
 * @api
 * @since 100.0.2
 */
interface AddressRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return \CND\Baker\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\AddressInterface $address);

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \CND\Baker\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId);

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer address.
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\AddressInterface $address);

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
