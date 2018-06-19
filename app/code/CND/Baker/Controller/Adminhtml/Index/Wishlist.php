<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class Wishlist extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Wishlist Action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $bakerId = $this->initCurrentBaker();
        $itemId = (int)$this->getRequest()->getParam('delete');
        if ($bakerId && $itemId) {
            try {
                $this->_objectManager->create(\Magento\Wishlist\Model\Item::class)->load($itemId)->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($exception);
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
