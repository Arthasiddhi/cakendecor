<?php
/**
 * Created by PhpStorm.
 * User: ravi
 * Date: 1/6/18
 * Time: 2:31 PM
 */

namespace CND\Baker\Model;


use Magento\Framework\Model\AbstractModel;

class Location extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\CND\Baker\Model\ResourceModel\Location::class);
    }
}