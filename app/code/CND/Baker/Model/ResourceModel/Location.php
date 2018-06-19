<?php
/**
 * Created by PhpStorm.
 * User: ravi
 * Date: 15/6/18
 * Time: 5:14 PM
 */

namespace CND\Baker\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Location extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('baker_location', 'id');
    }

}