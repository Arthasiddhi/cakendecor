<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api\Data;

/**
 * Baker interface.
 * @api
 * @since 100.0.2
 */
interface BakerInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ID = 'id';
    const CONFIRMATION = 'confirmation';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CREATED_IN = 'created_in';
    const DOB = 'dob';
    const EMAIL = 'email';
    const FIRSTNAME = 'firstname';
    const GENDER = 'gender';
    const GROUP_ID = 'group_id';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const STORE_ID = 'store_id';
    const SUFFIX = 'suffix';
    const TAXVAT = 'taxvat';
    const WEBSITE_ID = 'website_id';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    const KEY_ADDRESSES = 'addresses';
    const KEY_LOCATIONS = 'locations';
    const KEY_SERVICES = 'services';
    const DISABLE_AUTO_GROUP_CHANGE = 'disable_auto_group_change';
    const LOCATION_ID = 'location_id';
    const SERVICE_ID = 'service_id';
    const BUSINESS='business';
    const DELIVERY="delivery";

    /**#@-*/

    /**
     * Get Baker id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Baker id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);


    /**
     * Get location ID
     *
     * @return int|null
     */
    public function getLocationId();

    /**
     * Set location ID
     *
     * @param int $locationId
     * @return $this
     */
    public function setLocationId($locationId);

    /**
     * Get location ID
     *
     * @return int|null
     */
    public function getServiceId();

    /**
     * Set location ID
     *
     * @param int $serviceId
     * @return $this
     */
    public function setServiceId($serviceId);

    /**
     * Set service ID
     *
     * @param string $business
     * @return $this
     */
    public function setBusiness($business);

    /**
     * Get Service ID
     *
     * @return string|null
     */
    public function getBusiness();

    /**
     * Set service ID
     *
     * @param boolean $delivery
     * @return $this
     */
    public function setDelivery($delivery);


    /**
     * Get Service ID
     *
     * @return boolean|null
     */
    public function getDelivery();





    /**
     * Get group id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Set group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId);

    /**
     * Get default billing address id
     *
     * @return string|null
     */
    public function getDefaultBilling();

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling);

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping();

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping);

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation();

    /**
     * Set confirmation
     *
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn();

    /**
     * Set created in area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn);

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob();

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob);

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get gender
     *
     * @return int|null
     */
    public function getGender();

    /**
     * Set gender
     *
     * @param int $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get tax Vat
     *
     * @return string|null
     */
    public function getTaxvat();

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get Baker addresses.
     *
     * @return \CND\Baker\Api\Data\AddressInterface[]|null
     */
    public function getAddresses();

    /**
     * Set Baker addresses.
     *
     * @param \CND\Baker\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null);
    /**
     * Get Baker addresses.
     *
     * @return \CND\Baker\Api\Data\AddressInterface[]|null
     */
    public function getLocations();

    /**
     * Set Baker addresses.
     *
     * @param \CND\Baker\Api\Data\LocationInterface[] $locations
     * @return $this
     */
    public function setLocations(array $locations = null);


    /**
     * Get Baker addresses.
     *
     * @return \CND\Baker\Api\Data\AddressInterface[]|null
     */
    public function getServices();

    /**
     * Set Baker addresses.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface[] $services
     * @return $this
     */
    public function setServices(array $services = null);

    /**
     * Get disable auto group change flag.
     *
     * @return int|null
     */
    public function getDisableAutoGroupChange();

    /**
     * Set disable auto group change flag.
     *
     * @param int $disableAutoGroupChange
     * @return $this
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \CND\Baker\Api\Data\BakerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \CND\Baker\Api\Data\BakerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\CND\Baker\Api\Data\BakerExtensionInterface $extensionAttributes);
}
