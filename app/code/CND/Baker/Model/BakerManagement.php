<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\BakerManagementInterface;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;

class BakerManagement implements BakerManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $bakersFactory;

    /**
     * @param CollectionFactory $bakersFactory
     */
    public function __construct(CollectionFactory $bakersFactory)
    {
        $this->bakersFactory = $bakersFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $bakers = $this->bakersFactory->create();
        /** @var \CND\Baker\Model\ResourceModel\Baker\Collection $bakers */
        return $bakers->getSize();
    }
}
