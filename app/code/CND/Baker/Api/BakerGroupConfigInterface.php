<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Interface for system configuration operations for baker groups.
 *
 * @api
 * @since 100.2.0
 */
interface BakerGroupConfigInterface
{
    /**
     * Set system default baker group.
     *
     * @param int $id
     * @return int
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    public function setDefaultBakerGroup($id);
}
