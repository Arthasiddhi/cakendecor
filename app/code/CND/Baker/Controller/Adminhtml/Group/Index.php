<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Group;

class Index extends \CND\Baker\Controller\Adminhtml\Group
{
    /**
     * Baker groups list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CND_Baker::baker_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Baker Groups'));
        $resultPage->addBreadcrumb(__('Bakers'), __('Bakers'));
        $resultPage->addBreadcrumb(__('Baker Groups'), __('Baker Groups'));
        return $resultPage;
    }
}
