<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Address;

use CND\Baker\Api\BakerRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \CND\Baker\Controller\Address
{
    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \CND\Baker\Model\Session $bakerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \CND\Baker\Model\Metadata\FormFactory $formFactory
     * @param \CND\Baker\Api\AddressRepositoryInterface $addressRepository
     * @param \CND\Baker\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \CND\Baker\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param BakerRepositoryInterface $bakerRepository
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \CND\Baker\Model\Session $bakerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \CND\Baker\Model\Metadata\FormFactory $formFactory,
        \CND\Baker\Api\AddressRepositoryInterface $addressRepository,
        \CND\Baker\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \CND\Baker\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        BakerRepositoryInterface $bakerRepository
    ) {
        $this->bakerRepository = $bakerRepository;
        parent::__construct(
            $context,
            $bakerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Baker addresses list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $addresses = $this->bakerRepository->getById($this->_getSession()->getBakerId())->getAddresses();
        if (count($addresses)) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $block = $resultPage->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath('*/*/new');
        }
    }
}
