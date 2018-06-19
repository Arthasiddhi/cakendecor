<?php


namespace CND\Baker\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;

class  Location extends Action{
    public function execute()
    {
        /** @var Raw $result */
        $result=$this->resultFactory->create(ResultFactory::TYPE_PAGE);
        //$result->setContents("Hello");
        return $result;
    }

}