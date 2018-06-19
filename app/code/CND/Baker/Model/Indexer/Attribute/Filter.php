<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Indexer\Attribute;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use CND\Baker\Model\Baker;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;

class Filter
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
     */
    protected $flatScopeResolver;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param ResourceConnection $resource
     * @param FlatScopeResolver $flatScopeResolver
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        ResourceConnection $resource,
        FlatScopeResolver $flatScopeResolver,
        IndexerRegistry $indexerRegistry
    ) {
        $this->resource = $resource;
        $this->flatScopeResolver = $flatScopeResolver;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function filter(array $attributes)
    {
        $indexer = $this->indexerRegistry->get(Baker::CUSTOMER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() != StateInterface::STATUS_VALID) {
            $tableName = $this->flatScopeResolver->resolve(Baker::CUSTOMER_GRID_INDEXER_ID, []);
            $columns = $this->resource->getConnection()->describeTable($tableName);

            foreach (array_keys($attributes) as $attributeCode) {
                if (!isset($columns[$attributeCode])) {
                    unset($attributes[$attributeCode]);
                }
            }
        }

        return $attributes;
    }
}
