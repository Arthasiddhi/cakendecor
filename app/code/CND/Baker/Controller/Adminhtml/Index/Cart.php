<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\AddressInterfaceFactory;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 100.2.0
 */
class Cart extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \CND\Baker\Model\BakerFactory $bakerFactory
     * @param \CND\Baker\Model\AddressFactory $addressFactory
     * @param \CND\Baker\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \CND\Baker\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $random
     * @param BakerRepositoryInterface $bakerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $bakerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param BakerInterfaceFactory $bakerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \CND\Baker\Model\Baker\Mapper $bakerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Quote\Model\QuoteFactory|null $quoteFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \CND\Baker\Model\BakerFactory $bakerFactory,
        \CND\Baker\Model\AddressFactory $addressFactory,
        \CND\Baker\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \CND\Baker\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        BakerRepositoryInterface $bakerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $bakerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        BakerInterfaceFactory $bakerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \CND\Baker\Model\Baker\Mapper $bakerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory = null
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $bakerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $bakerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $bakerAccountManagement,
            $addressRepository,
            $bakerDataFactory,
            $addressDataFactory,
            $bakerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
        $this->quoteFactory = $quoteFactory ?: $this->_objectManager->get(\Magento\Quote\Model\QuoteFactory::class);
    }

    /**
     * Handle and then get cart grid contents
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $bakerId = $this->initCurrentBaker();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->_objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
            /** @var \Magento\Quote\Model\Quote $quote */
            try {
                $quote = $quoteRepository->getForBaker($bakerId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $quote = $this->quoteFactory->create();
            }
            $quote->setWebsite(
                $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite($websiteId)
            );
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quoteRepository->save($quote->collectTotals());
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('admin.baker.view.edit.cart')->setWebsiteId($websiteId);
        return $resultLayout;
    }
}
