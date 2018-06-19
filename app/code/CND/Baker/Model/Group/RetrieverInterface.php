<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Group;

/**
 * Interface for getting current customer group from session.
 *
 * @api
 * @since 100.2.0
 */
interface RetrieverInterface
{
    /**
     * Retrieve customer group id.
     *
     * @return int
     * @since 100.2.0
     */
    public function getBakerGroupId();
}
