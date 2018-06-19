<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Baker edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $bakerId = $this->initCurrentBaker();

        $bakerData = [];
        $bakerData['account'] = [];
        $bakerData['address'] = [];
        $baker = null;
        $isExistingBaker = (bool)$bakerId;
        if ($isExistingBaker) {
            try {
                $baker = $this->_bakerRepository->getById($bakerId);
                $bakerData['account'] = $this->bakerMapper->toFlatArray($baker);
                $bakerData['account'][BakerInterface::ID] = $bakerId;
                try {
                    $addresses = $baker->getAddresses();
                    foreach ($addresses as $address) {
                        $bakerData['address'][$address->getId()] = $this->addressMapper->toFlatArray($address);
                        $bakerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the baker.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('baker/*/index');
                return $resultRedirect;
            }
        }
        $bakerData['baker_id'] = $bakerId;
        $this->_getSession()->setBakerData($bakerData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CND_Baker::baker_manage');
        $this->prepareDefaultBakerTitle($resultPage);
        $resultPage->setActiveMenu('CND_Baker::baker');
        if ($isExistingBaker) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getBakerName($baker));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Baker'));
        }
        return $resultPage;
    }
}
