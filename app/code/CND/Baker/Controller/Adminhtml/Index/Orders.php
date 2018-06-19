<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class Orders extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Baker orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentBaker();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
