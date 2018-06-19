<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Baker address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Address extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \CND\Baker\Model\Session
     */
    protected $_bakerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \CND\Baker\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \CND\Baker\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \CND\Baker\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \CND\Baker\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $_dataProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_bakerSession = $bakerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_formFactory = $formFactory;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->_dataProcessor = $dataProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve baker session object
     *
     * @return \CND\Baker\Model\Session
     */
    protected function _getSession()
    {
        return $this->_bakerSession;
    }

    /**
     * Check baker authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create(\Magento\Framework\UrlInterface::class);
        return $urlBuilder->getUrl($route, $params);
    }
}
