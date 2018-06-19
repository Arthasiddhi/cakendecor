<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\Data\AddressInterface;
use CND\Baker\Api\Data\AddressInterfaceFactory;
use CND\Baker\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;

/**
 * Baker address model
 *
 * @api
 * @method int getParentId() getParentId()
 * @method \CND\Baker\Model\Address setParentId() setParentId(int $parentId)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends \CND\Baker\Model\Address\AbstractAddress
{
    /**
     * Baker entity
     *
     * @var Baker
     */
    protected $_baker;

    /**
     * @var BakerFactory
     */
    protected $_bakerFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \CND\Baker\Model\Address\CustomAttributeListInterface
     */
    private $attributeList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param BakerFactory $bakerFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \CND\Baker\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        BakerFactory $bakerFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->_bakerFactory = $bakerFactory;
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\CND\Baker\Model\ResourceModel\Address::class);
    }

    /**
     * Update Model with the data from Data Interface
     *
     * @param AddressInterface $address
     * @return $this
     * Use Api/RepositoryInterface for the operations in the Data Interfaces. Don't rely on Address Model
     */
    public function updateData(AddressInterface $address)
    {
        // Set all attributes
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($address, \CND\Baker\Api\Data\AddressInterface::class);

        foreach ($attributes as $attributeCode => $attributeData) {
            if (AddressInterface::REGION === $attributeCode) {
                $this->setRegion($address->getRegion()->getRegion());
                $this->setRegionCode($address->getRegion()->getRegionCode());
                $this->setRegionId($address->getRegion()->getRegionId());
            } else {
                $this->setDataUsingMethod($attributeCode, $attributeData);
            }
        }
        // Need to explicitly set this due to discrepancy in the keys between model and data object
        $this->setIsDefaultBilling($address->isDefaultBilling());
        $this->setIsDefaultShipping($address->isDefaultShipping());
        if (!$this->getAttributeSetId()) {
            $this->setAttributeSetId(AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS);
        }
        $customAttributes = $address->getCustomAttributes();
        if ($customAttributes !== null) {
            foreach ($customAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataModel($defaultBillingAddressId = null, $defaultShippingAddressId = null)
    {
        if ($this->getBakerId() || $this->getParentId()) {
            if ($this->getBaker()->getDefaultBillingAddress()) {
                $defaultBillingAddressId = $this->getBaker()->getDefaultBillingAddress()->getId();
            }
            if ($this->getBaker()->getDefaultShippingAddress()) {
                $defaultShippingAddressId = $this->getBaker()->getDefaultShippingAddress()->getId();
            }
        }
        return parent::getDataModel($defaultBillingAddressId, $defaultShippingAddressId);
    }

    /**
     * Retrieve address baker identifier
     *
     * @return int
     */
    public function getBakerId()
    {
        return $this->_getData('baker_id') ? $this->_getData('baker_id') : $this->getParentId();
    }

    /**
     * Declare address baker identifier
     *
     * @param int $id
     * @return $this
     */
    public function setBakerId($id)
    {
        $this->setParentId($id);
        $this->setData('baker_id', $id);
        return $this;
    }

    /**
     * Retrieve address baker
     *
     * @return Baker|false
     */
    public function getBaker()
    {
        if (!$this->getBakerId()) {
            return false;
        }
        if (empty($this->_baker)) {
            $this->_baker = $this->_createBaker()->load($this->getBakerId());
        }
        return $this->_baker;
    }

    /**
     * Specify address baker
     *
     * @param Baker $baker
     * @return $this
     */
    public function setBaker(Baker $baker)
    {
        $this->_baker = $baker;
        $this->setBakerId($baker->getId());
        return $this;
    }

    /**
     * Retrieve address entity attributes
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        $attributes = $this->getData('attributes');
        if ($attributes === null) {
            $attributes = $this->_getResource()->loadAllAttributes($this)->getSortedAttributes();
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Get attributes created by default
     *
     * @return string[]
     */
    public function getDefaultAttributeCodes()
    {
        return $this->_getResource()->getDefaultAttributes();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->setId(null);
    }

    /**
     * Return Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Return Region ID
     *
     * @return int
     */
    public function getRegionId()
    {
        return (int)$this->getData('region_id');
    }

    /**
     * Set Region ID. $regionId is automatically converted to integer
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->setData('region_id', (int)$regionId);
        return $this;
    }

    /**
     * @return Baker
     */
    protected function _createBaker()
    {
        return $this->_bakerFactory->create();
    }

    /**
     * Return Entity Type ID
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->getEntityType()->getId();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        $indexer = $this->indexerRegistry->get(Baker::CUSTOMER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() == StateInterface::STATUS_VALID) {
            $this->_getResource()->addCommitCallback([$this, 'reindex']);
        }
        return parent::afterSave();
    }

    /**
     * Init indexing process after baker delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->reindex();
        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after baker save
     *
     * @return void
     */
    public function reindex()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(Baker::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexRow($this->getBakerId());
    }

    /**
     * {@inheritdoc}
     * @since 100.0.6
     */
    protected function getCustomAttributesCodes()
    {
        return array_keys($this->getAttributeList()->getAttributes());
    }

    /**
     * Get new AttributeList dependency for application code.
     * @return \CND\Baker\Model\Address\CustomAttributeListInterface
     * @deprecated 100.0.6
     */
    private function getAttributeList()
    {
        if (!$this->attributeList) {
            $this->attributeList = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \CND\Baker\Model\Address\CustomAttributeListInterface::class
            );
        }
        return $this->attributeList;
    }

    /**
     * Retrieve attribute set id for baker address.
     *
     * @return int
     * @since 100.2.0
     */
    public function getAttributeSetId()
    {
        return parent::getAttributeSetId() ?: AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS;
    }
}
