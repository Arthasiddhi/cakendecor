<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Baker\Indexer;

/**
 * Customers collection for customer_grid indexer
 */
class Collection extends \CND\Baker\Model\ResourceModel\Baker\Collection
{
    /**
     * @inheritdoc
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }
}
