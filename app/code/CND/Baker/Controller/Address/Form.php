<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Address;

class Form extends \CND\Baker\Controller\Address
{
    /**
     * Address book form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('baker_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('baker/address');
        }
        return $resultPage;
    }
}
