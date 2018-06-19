<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class ViewCart extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Get shopping cart to view only
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentBaker();
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('admin.baker.view.cart')->setWebsiteId(
            (int)$this->getRequest()->getParam('website_id')
        );
        return $resultLayout;
    }
}
