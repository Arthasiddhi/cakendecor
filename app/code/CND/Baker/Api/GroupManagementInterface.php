<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Interface for managing customer groups.
 * @api
 * @since 100.0.2
 */
interface GroupManagementInterface
{
    /**
     * Check if customer group can be deleted.
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($id);

    /**
     * Get default customer group.
     *
     * @param int $storeId
     * @return \CND\Baker\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultGroup($storeId = null);

    /**
     * Get customer group representing customers not logged in.
     *
     * @return \CND\Baker\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotLoggedInGroup();

    /**
     * Get all customer groups except group representing customers not logged in.
     *
     * @return \CND\Baker\Api\Data\GroupInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoggedInGroups();

    /**
     * Get customer group representing all customers.
     *
     * @return \CND\Baker\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllBakersGroup();
}
