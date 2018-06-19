<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class ProductReviews extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Get baker's product reviews list
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $bakerId = $this->initCurrentBaker();
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('admin.baker.reviews');
        $block->setBakerId($bakerId)->setUseAjax(true);
        return $resultLayout;
    }
}
