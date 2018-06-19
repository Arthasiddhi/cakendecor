<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api\Data;

/**
 * Interface for customer address search results.
 * @api
 * @since 100.0.2
 */
interface ServiceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer addresses list.
     *
     * @return \CND\Baker\Api\Data\ServiceInterface[]
     */
    public function getItems();

    /**
     * Set customer addresses list.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
