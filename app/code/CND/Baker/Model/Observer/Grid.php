<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Observer;

use CND\Baker\Model\ResourceModel\Baker\Grid as BakerGrid;

/**
 * @deprecated 100.1.0
 */
class Grid
{
    /**
     * @var BakerGrid
     */
    protected $bakerGrid;

    /**
     * @param BakerGrid $grid
     */
    public function __construct(
        BakerGrid $grid
    ) {
        $this->bakerGrid = $grid;
    }

    /**
     * @return void
     *
     * @deprecated 100.1.0
     */
    public function syncBakerGrid()
    {
        $this->bakerGrid->syncBakerGrid();
    }
}
