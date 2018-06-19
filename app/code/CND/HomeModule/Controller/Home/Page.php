<?php

namespace CND\HomeModule\Controller\Home;

use Magento\Framework\Controller\ResultFactory;

class Page extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
