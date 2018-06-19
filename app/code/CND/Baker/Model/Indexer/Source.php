<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Indexer;

use CND\Baker\Model\ResourceModel\Baker\Indexer\Collection;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Traversable;

/**
 * Bakers data batch generator for baker_grid indexer
 */
class Source implements \IteratorAggregate, \Countable, SourceProviderInterface
{
    /**
     * @var Collection
     */
    private $bakerCollection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param \CND\Baker\Model\ResourceModel\Baker\Indexer\CollectionFactory $collection
     * @param int $batchSize
     */
    public function __construct(
        \CND\Baker\Model\ResourceModel\Baker\Indexer\CollectionFactory $collectionFactory,
        $batchSize = 10000
    ) {
        $this->bakerCollection = $collectionFactory->create();
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTable()
    {
        return $this->bakerCollection->getMainTable();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        return $this->bakerCollection->getIdFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToSelect($fieldName, $alias = null)
    {
        $this->bakerCollection->addFieldToSelect($fieldName, $alias);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelect()
    {
        return $this->bakerCollection->getSelect();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $this->bakerCollection->addFieldToFilter($attribute, $condition);
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->bakerCollection->getSize();
    }

    /**
     * Retrieve an iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        $this->bakerCollection->setPageSize($this->batchSize);
        $lastPage = $this->bakerCollection->getLastPageNumber();
        $pageNumber = 0;
        do {
            $this->bakerCollection->clear();
            $this->bakerCollection->setCurPage($pageNumber);
            foreach ($this->bakerCollection->getItems() as $key => $value) {
                yield $key => $value;
            }
            $pageNumber++;
        } while ($pageNumber <= $lastPage);
    }
}
