<?php

namespace CND\Baker\Model\ResourceModel\Service;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CND\Baker\Model\Service;
use CND\Baker\Model\ResourceModel\Service as serviceResource;


class Collection extends AbstractCollection{
    protected $_idFieldName='id';

    protected  function _construct()
    {
       $this->init(Service::class,serviceResource::class);
    }
}