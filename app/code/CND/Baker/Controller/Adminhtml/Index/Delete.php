<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Delete baker action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Baker could not be deleted.'));
            return $resultRedirect->setPath('baker/index');
        }

        $bakerId = $this->initCurrentBaker();
        if (!empty($bakerId)) {
            try {
                $this->_bakerRepository->deleteById($bakerId);
                $this->messageManager->addSuccess(__('You deleted the baker.'));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('baker/index');
    }
}
