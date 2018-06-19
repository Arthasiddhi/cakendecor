<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api\Data;

/**
 * Interface for customer search results.
 * @api
 * @since 100.0.2
 */
interface BakerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @return \CND\Baker\Api\Data\CustomerInterface[]
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @param \CND\Baker\Api\Data\CustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
