<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Controller\Adminhtml\Baker;

use Magento\Integration\Api\BakerTokenServiceInterface;
use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\AddressInterfaceFactory;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class to invalidate tokens for bakers
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class InvalidateToken extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * @var BakerTokenServiceInterface
     */
    protected $tokenService;

    /**
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
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param BakerTokenServiceInterface $tokenService
     *
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
        DataObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        BakerTokenServiceInterface $tokenService
    ) {
        $this->tokenService = $tokenService;
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
    }

    /**
     * Reset baker's tokens handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($bakerId = $this->getRequest()->getParam('baker_id')) {
            try {
                $this->tokenService->revokeBakerAccessToken($bakerId);
                $this->messageManager->addSuccess(__('You have revoked the baker\'s tokens.'));
                $resultRedirect->setPath('baker/index/edit', ['id' => $bakerId, '_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('baker/index/edit', ['id' => $bakerId, '_current' => true]);
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a baker to revoke.'));
            $resultRedirect->setPath('baker/index/index');
        }
        return $resultRedirect;
    }
}
