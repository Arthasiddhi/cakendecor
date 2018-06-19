<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Address;

use CND\Baker\Model\AttributeChecker;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;

/**
 * Baker address edit block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Edit extends \Magento\Directory\Block\Data
{
    /**
     * @var \CND\Baker\Api\Data\AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var \CND\Baker\Model\Session
     */
    protected $_bakerSession;

    /**
     * @var \CND\Baker\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \CND\Baker\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AttributeChecker
     */
    private $attributeChecker;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \CND\Baker\Model\Session $bakerSession
     * @param \CND\Baker\Api\AddressRepositoryInterface $addressRepository
     * @param \CND\Baker\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \CND\Baker\Helper\Session\CurrentBaker $currentBaker
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AttributeChecker $attributeChecker
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \CND\Baker\Model\Session $bakerSession,
        \CND\Baker\Api\AddressRepositoryInterface $addressRepository,
        \CND\Baker\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \CND\Baker\Helper\Session\CurrentBaker $currentBaker,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = [],
        AttributeChecker $attributeChecker = null
    ) {
        $this->_bakerSession = $bakerSession;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentBaker = $currentBaker;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->attributeChecker = $attributeChecker ?: ObjectManager::getInstance()->get(AttributeChecker::class);

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Init address object
        if ($addressId = $this->getRequest()->getParam('id')) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getBakerId() != $this->_bakerSession->getBakerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $baker = $this->getBaker();
            $this->_address->setPrefix($baker->getPrefix());
            $this->_address->setFirstname($baker->getFirstname());
            $this->_address->setMiddlename($baker->getMiddlename());
            $this->_address->setLastname($baker->getLastname());
            $this->_address->setSuffix($baker->getSuffix());
        }

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_bakerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region' => $postedData['region'] ?? null,
            ];
            if (!empty($postedData['region_id'])) {
                $postedData['region']['region_id'] = $postedData['region_id'];
            }
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \CND\Baker\Api\Data\AddressInterface::class
            );
        }

        return $this;
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\CND\Baker\Block\Widget\Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = __('Edit Address');
        } else {
            $title = __('Add New Address');
        }
        return $title;
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getBakerAddressCount()) {
            return $this->getUrl('baker/address');
        } else {
            return $this->getUrl('baker/account/');
        }
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'baker/address/formPost',
            ['_secure' => true, 'id' => $this->getAddress()->getId()]
        );
    }

    /**
     * Return the associated address.
     *
     * @return \CND\Baker\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? 0 : $region->getRegionId();
    }

    /**
     * Retrieve the number of addresses associated with the baker given a baker Id.
     *
     * @return int
     */
    public function getBakerAddressCount()
    {
        return count($this->getBaker()->getAddresses());
    }

    /**
     * Determine if the address can be set as the default billing address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getBakerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    /**
     * Determine if the address can be set as the default shipping address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getBakerAddressCount();
        }
        return !$this->isDefaultShipping();
    }

    /**
     * Is the address the default billing address?
     *
     * @return bool
     */
    public function isDefaultBilling()
    {
        return (bool)$this->getAddress()->isDefaultBilling();
    }

    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
        return (bool)$this->getAddress()->isDefaultShipping();
    }

    /**
     * Retrieve the Baker Data using the baker Id from the baker session.
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    public function getBaker()
    {
        return $this->currentBaker->getBaker();
    }

    /**
     * Return back button Url, either to baker address or account.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        if ($this->getBakerAddressCount()) {
            return $this->getUrl('baker/address');
        } else {
            return $this->getUrl('baker/account/');
        }
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks whether it is allowed to show an attribute on the form.
     *
     * @param string $attributeCode
     * @param string $formName
     * @return bool
     */
    public function isAttributeAllowedOnForm($attributeCode, $formName)
    {
        return $this->attributeChecker->isAttributeAllowedOnForm($attributeCode, $formName);
    }
}
