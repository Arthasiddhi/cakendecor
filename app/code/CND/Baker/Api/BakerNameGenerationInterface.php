<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

use CND\Baker\Api\Data\BakerInterface;

/**
 * Interface CustomerNameGenerationInterface
 *
 * @api
 * @since 100.1.0
 */
interface BakerNameGenerationInterface
{
    /**
     * Concatenate all customer name parts into full customer name.
     *
     * @param BakerInterface $customerData
     * @return string
     * @since 100.1.0
     */
    public function getBakerName(BakerInterface $customerData);
}
