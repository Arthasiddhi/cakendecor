<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\Data;

use CND\Baker\Api\Data\BakerInterface;
use \Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Baker extends \Magento\Framework\Api\AbstractExtensibleObject implements BakerInterface
{
    /**
     * @var \CND\Baker\Api\BakerMetadataInterface
     */
    protected $metadataService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param \CND\Baker\Api\BakerMetadataInterface $metadataService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        \CND\Baker\Api\BakerMetadataInterface $metadataService,
        $data = []
    ) {
        $this->metadataService = $metadataService;
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
        }
        return $this->customAttributesCodes;
    }

    /**
     * @return string|null
     */
    public function getDefaultBilling()
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping()
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation()
    {
        return $this->_get(self::CONFIRMATION);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn()
    {
        return $this->_get(self::CREATED_IN);
    }

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob()
    {
        return $this->_get(self::DOB);
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->_get(self::GENDER);
    }

    /**
     * Get group id
     *
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }
    /**
     * Get suffix
     *
     * @return int|null
     */
    public function getLocationId()
    {
        return $this->_get(self::LOCATION_ID);
    }

    /**
     * Get suffix
     *
     * @return int|null
     */
    public function getServiceId()
    {
        return $this->_get(self::SERVICE_ID);
    }

    /**
     * Get tax Vat.
     *
     * @return string|null
     */
    public function getTaxvat()
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Get addresses
     *
     * @return \CND\Baker\Api\Data\AddressInterface[]|null
     */
    public function getAddresses()
    {
        return $this->_get(self::KEY_ADDRESSES);
    }
    /**
     * Get addresses
     *
     * @return \CND\Baker\Api\Data\LocationInterface[]|null
     */
    public function getLocations()
    {
        return $this->_get(self::KEY_LOCATIONS);
    }

    /**
     * Get addresses
     *
     * @return \CND\Baker\Api\Data\ServiceInterface[]|null
     */
    public function getServices()
    {
        return $this->_get(self::KEY_SERVICES);
    }
    /**
     * Get disable auto group change flag.
     *
     * @return int|null
     */
    public function getDisableAutoGroupChange()
    {
        return $this->_get(self::DISABLE_AUTO_GROUP_CHANGE);
    }

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling)
    {
        return $this->setData(self::DEFAULT_BILLING, $defaultBilling);
    }

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping)
    {
        return $this->setData(self::DEFAULT_SHIPPING, $defaultShipping);
    }

    /**
     * Set confirmation
     *
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation)
    {
        return $this->setData(self::CONFIRMATION, $confirmation);
    }

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set created in area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn)
    {
        return $this->setData(self::CREATED_IN, $createdIn);
    }

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob)
    {
        return $this->setData(self::DOB, $dob);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return string
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * Set baker ID
     *
     * @param int $locationId
     * @return $this
     */
    public function setLocationId($locationId)
    {
        return $this->setData(self::LOCATION_ID, $locationId);
    }

    /**
     * Set baker ID
     *
     * @param int $serviceId
     * @return $this
     */
    public function setServiceId($serviceId)
    {
        return $this->setData(self::SERVICE_ID,$serviceId);
    }
    /**
     * Set gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat)
    {
        return $this->setData(self::TAXVAT, $taxvat);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Set customer addresses.
     *
     * @param \CND\Baker\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null)
    {
        return $this->setData(self::KEY_ADDRESSES, $addresses);
    }


    /**
     * Set customer addresses.
     *
     * @param \CND\Baker\Api\Data\LocationInterface[] $locations
     * @return $this
     */
    public function setLocations(array $locations = null)
    {
        return $this->setData(self::KEY_LOCATIONS, $locations);

    }

    /**
     * @param string $business
     * @return $this
     */
    public function setBusiness($business)
    {
        return $this->setData(self::BUSINESS,$business);
    }

    /**
     * @return string
     */
    public function getBusiness()
    {
        return $this->_get(self::BUSINESS);
    }

    /**
     * @param boolean $delivery
     * @return $this
     */
    public function setDelivery($delivery)
    {
        return $this->setData(self::DELIVERY,$delivery);
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->_get(self::DELIVERY);
    }


    /**
     * Set customer addresses.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface[] $services
     * @return $this
     */
    public function setServices(array $services = null)
    {
        return $this->setData(self::KEY_SERVICES,$services);

    }

    /**
     * Set disable auto group change flag.
     *
     * @param int $disableAutoGroupChange
     * @return $this
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange)
    {
        return $this->setData(self::DISABLE_AUTO_GROUP_CHANGE, $disableAutoGroupChange);
    }

    /**
     * {@inheritdoc}
     *
     * @return \CND\Baker\Api\Data\CustomerExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \CND\Baker\Api\Data\CustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\CND\Baker\Api\Data\BakerExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
