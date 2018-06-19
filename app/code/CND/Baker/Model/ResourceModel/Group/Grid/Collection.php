<?php
/**
 * Customer group collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Group\Grid;

class Collection extends \CND\Baker\Model\ResourceModel\Group\Collection
{
    /**
     * Resource initialization
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addTaxClass();
        return $this;
    }
}
