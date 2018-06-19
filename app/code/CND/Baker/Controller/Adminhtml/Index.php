<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\AddressInterfaceFactory;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Controller\RegistryConstants;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\Message\Error;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Index
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CND_Baker::manage';

    /**
     * @var \Magento\Framework\Validator
     * @deprecated 100.2.0
     */
    protected $_validator;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \CND\Baker\Model\BakerFactory
     * @deprecated 100.2.0
     */
    protected $_bakerFactory = null;

    /**
     * @var \CND\Baker\Model\AddressFactory
     * @deprecated 100.2.0
     */
    protected $_addressFactory = null;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \CND\Baker\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var BakerRepositoryInterface
     */
    protected $_bakerRepository;

    /**
     * @var  \CND\Baker\Helper\View
     */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\Math\Random
     * @deprecated 100.2.0
     */
    protected $_random;

    /**
     * @var ObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     * @deprecated 100.2.0
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var BakerInterfaceFactory
     */
    protected $bakerDataFactory;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \CND\Baker\Model\Baker\Mapper
     */
    protected $bakerMapper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 100.2.0
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @deprecated 100.2.0
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_bakerFactory = $bakerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_formFactory = $formFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_viewHelper = $viewHelper;
        $this->_random = $random;
        $this->_bakerRepository = $bakerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->addressMapper = $addressMapper;
        $this->bakerAccountManagement = $bakerAccountManagement;
        $this->addressRepository = $addressRepository;
        $this->bakerDataFactory = $bakerDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->bakerMapper = $bakerMapper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->_objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Baker initialization
     *
     * @return string baker id
     */
    protected function initCurrentBaker()
    {
        $bakerId = (int)$this->getRequest()->getParam('id');

        if ($bakerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $bakerId);
        }

        return $bakerId;
    }

    /**
     * Prepare baker default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultBakerTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Bakers'));
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * Helper function that handles mass actions by taking in a callable for handling a single baker action.
     *
     * @param callable $singleAction A single action callable that takes a baker ID as input
     * @param int[] $bakerIds Array of baker Ids to perform the action upon
     * @return int Number of bakers successfully acted upon
     * @deprecated 100.2.0
     */
    protected function actUponMultipleBakers(callable $singleAction, $bakerIds)
    {
        if (!is_array($bakerIds)) {
            $this->messageManager->addError(__('Please select baker(s).'));
            return 0;
        }
        $bakersUpdated = 0;
        foreach ($bakerIds as $bakerId) {
            try {
                $singleAction($bakerId);
                $bakersUpdated++;
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $bakersUpdated;
    }
}
