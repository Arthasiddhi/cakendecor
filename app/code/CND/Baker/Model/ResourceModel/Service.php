<?php
 namespace CND\Baker\Model\ResourceModel;

 use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

 class Service extends AbstractDb{

     protected function _construct()
     {
         $this->_init('baker_service', 'id');
     }
 }
