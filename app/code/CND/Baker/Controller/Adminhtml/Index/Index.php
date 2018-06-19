<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

class Index extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Bakers list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('CND_Baker::baker_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Bakers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Bakers'), __('Bakers'));
        $resultPage->addBreadcrumb(__('Manage Bakers'), __('Manage Bakers'));

        $this->_getSession()->unsBakerData();
        $this->_getSession()->unsBakerFormData();

        return $resultPage;
    }
}
