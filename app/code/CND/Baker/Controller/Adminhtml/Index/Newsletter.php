<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class Newsletter extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Baker newsletter grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $bakerId = $this->initCurrentBaker();
        /** @var  \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->_objectManager
            ->create(\Magento\Newsletter\Model\Subscriber::class)
            ->loadByBakerId($bakerId);

        $this->_coreRegistry->register('subscriber', $subscriber);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
