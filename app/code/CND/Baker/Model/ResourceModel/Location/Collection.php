<?php

namespace CND\Baker\Model\ResourceModel\Location;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CND\Baker\Model\Location;
use CND\Baker\Model\ResourceModel\Location as locationResource;


class Collection extends AbstractCollection{
    protected $_idFieldName='id';

    protected  function _construct()
    {
       $this->_init(Location::class,locationResource::class);
    }
}