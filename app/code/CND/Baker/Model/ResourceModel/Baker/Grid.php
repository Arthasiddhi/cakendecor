<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Baker;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use CND\Baker\Model\Baker;

/**
 * @deprecated 100.1.0
 */
class Grid
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
     */
    protected $flatScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     * @param FlatScopeResolver $flatScopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry,
        FlatScopeResolver $flatScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexerRegistry = $indexerRegistry;
        $this->flatScopeResolver = $flatScopeResolver;
    }

    /**
     * Synchronize baker grid
     *
     * @return void
     *
     * @deprecated 100.1.0
     */
    public function syncBakerGrid()
    {
        $indexer = $this->indexerRegistry->get(Baker::CUSTOMER_GRID_INDEXER_ID);
        $bakerIds = $this->getBakerIdsForReindex();
        if ($bakerIds) {
            $indexer->reindexList($bakerIds);
        }
    }

    /**
     * Retrieve baker IDs for reindex
     *
     * @return array
     *
     * @deprecated 100.1.0
     */
    protected function getBakerIdsForReindex()
    {
        $connection = $this->resource->getConnection();
        $gridTableName = $this->flatScopeResolver->resolve(Baker::CUSTOMER_GRID_INDEXER_ID, []);

        $select = $connection->select()
            ->from($this->resource->getTableName($gridTableName), 'last_visit_at')
            ->order('last_visit_at DESC')
            ->limit(1);
        $lastVisitAt = $connection->query($select)->fetchColumn();

        $select = $connection->select()
            ->from($this->resource->getTableName('baker_log'), 'baker_id')
            ->where('last_login_at > ?', $lastVisitAt);

        $bakerIds = [];
        foreach ($connection->query($select)->fetchAll() as $row) {
            $bakerIds[] = $row['baker_id'];
        }

        return $bakerIds;
    }
}
