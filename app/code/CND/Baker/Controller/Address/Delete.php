<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Address;

class Delete extends \CND\Baker\Controller\Address
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId && $this->_formKeyValidator->validate($this->getRequest())) {
            try {
                $address = $this->_addressRepository->getById($addressId);
                if ($address->getBakerId() === $this->_getSession()->getBakerId()) {
                    $this->_addressRepository->deleteById($addressId);
                    $this->messageManager->addSuccess(__('You deleted the address.'));
                } else {
                    $this->messageManager->addError(__('We can\'t delete the address right now.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t delete the address right now.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}