<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Api\GroupRepositoryInterface;
use CND\Baker\Model\Config\Share;
use CND\Baker\Model\ResourceModel\Address\CollectionFactory;
use CND\Baker\Model\ResourceModel\Baker as ResourceBaker;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Baker model
 *
 * @api
 * @method int getWebsiteId() getWebsiteId()
 * @method Baker setWebsiteId($value)
 * @method int getStoreId() getStoreId()
 * @method string getEmail() getEmail()
 * @method mixed getDisableAutoGroupChange()
 * @method Baker setDisableAutoGroupChange($value)
 * @method Baker setGroupId($value)
 * @method Baker setDefaultBilling($value)
 * @method Baker setDefaultShipping($value)
 * @method Baker setPasswordHash($string)
 * @method string getPasswordHash()
 * @method string getConfirmation()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Baker extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'baker/create_account/email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'baker/create_account/email_identity';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'baker/password/remind_email_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'baker/password/forgot_email_template';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'baker/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'baker/password/reset_password_template';

    /**
     * @deprecated
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    const XML_PATH_IS_CONFIRM = 'baker/create_account/confirm';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'baker/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'baker/create_account/email_confirmed_template';

    const XML_PATH_GENERATE_HUMAN_FRIENDLY_ID = 'baker/create_account/generate_human_friendly_id';

    const SUBSCRIBED_YES = 'yes';

    const SUBSCRIBED_NO = 'no';

    const ENTITY = 'baker';

    const CUSTOMER_GRID_INDEXER_ID = 'baker_grid';

    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'baker/password/reset_link_expiration_period';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'baker';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'baker';

    /**
     * List of errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Assoc array of baker attributes
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Baker addresses collection
     *
     * @var \CND\Baker\Model\ResourceModel\Address\Collection
     */
    protected $_addressesCollection;

    /**
     * Is model deletable
     *
     * @var boolean
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     */
    protected $_isReadonly = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var CollectionFactory
     */
    protected $_addressesFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var BakerInterfaceFactory
     */
    protected $bakerDataFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \CND\Baker\Api\BakerMetadataInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceBaker $resource
     * @param Share $configShare
     * @param AddressFactory $addressFactory
     * @param CollectionFactory $addressesFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param BakerInterfaceFactory $bakerDataFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param BakerMetadataInterface $metadataService
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param AccountConfirmation|null $accountConfirmation
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \CND\Baker\Model\ResourceModel\Baker $resource,
        \CND\Baker\Model\Config\Share $configShare,
        \CND\Baker\Model\AddressFactory $addressFactory,
        \CND\Baker\Model\ResourceModel\Address\CollectionFactory $addressesFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        BakerInterfaceFactory $bakerDataFactory,
        DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \CND\Baker\Api\BakerMetadataInterface $metadataService,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        AccountConfirmation $accountConfirmation = null
    ) {
        $this->metadataService = $metadataService;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_configShare = $configShare;
        $this->_addressFactory = $addressFactory;
        $this->_addressesFactory = $addressesFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_groupRepository = $groupRepository;
        $this->_encryptor = $encryptor;
        $this->dateTime = $dateTime;
        $this->bakerDataFactory = $bakerDataFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->indexerRegistry = $indexerRegistry;
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize baker model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\CND\Baker\Model\ResourceModel\Baker::class);
    }

    /**
     * Retrieve baker model with baker data
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    public function getDataModel()
    {
        $bakerData = $this->getData();
        $addressesData = [];
        /** @var \CND\Baker\Model\Address $address */
        foreach ($this->getAddresses() as $address) {
            $addressesData[] = $address->getDataModel();
        }
        $bakerDataObject = $this->bakerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bakerDataObject,
            $bakerData,
            \CND\Baker\Api\Data\BakerInterface::class
        );
        $bakerDataObject->setAddresses($addressesData)
            ->setId($this->getId());
        return $bakerDataObject;
    }

    /**
     * Update baker data
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @return $this
     */
    public function updateData($baker)
    {
        $bakerDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $baker,
            \CND\Baker\Api\Data\BakerInterface::class
        );

        foreach ($bakerDataAttributes as $attributeCode => $attributeData) {
            if ($attributeCode == 'password') {
                continue;
            }
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }

        $customAttributes = $baker->getCustomAttributes();
        if ($customAttributes !== null) {
            foreach ($customAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        $bakerId = $baker->getId();
        if ($bakerId) {
            $this->setId($bakerId);
        }

        // Need to use attribute set or future updates can cause data loss
        if (!$this->getAttributeSetId()) {
            $this->setAttributeSetId(
                BakerMetadataInterface::ATTRIBUTE_SET_ID_BAKER
            );
        }

        return $this;
    }

    /**
     * Retrieve baker sharing configuration model
     *
     * @return Share
     */
    public function getSharingConfig()
    {
        return $this->_configShare;
    }

    /**
     * Authenticate baker
     *
     * @param  string $login
     * @param  string $password
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * Use \CND\Baker\Api\AccountManagementInterface::authenticate
     */
    public function authenticate($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw new EmailNotConfirmedException(
                __('This account is not confirmed.')
            );
        }
        if (!$this->validatePassword($password)) {
            throw new InvalidEmailOrPasswordException(
                __('Invalid login or password.')
            );
        }
        $this->_eventManager->dispatch(
            'baker_baker_authenticated',
            ['model' => $this, 'password' => $password]
        );

        return true;
    }

    /**
     * Load baker by email
     *
     * @param   string $bakerEmail
     * @return  $this
     */
    public function loadByEmail($bakerEmail)
    {
        $this->_getResource()->loadByEmail($this, $bakerEmail);
        return $this;
    }

    /**
     * Change baker password
     *
     * @param   string $newPassword
     * @return  $this
     */
    public function changePassword($newPassword)
    {
        $this->_getResource()->changePassword($this, $newPassword);
        return $this;
    }

    /**
     * Get full baker name
     *
     * @return string
     */
    public function getName()
    {
        $name = '';

        if ($this->_config->getAttribute('baker', 'prefix')->getIsVisible() && $this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        if ($this->_config->getAttribute('baker', 'middlename')->getIsVisible() && $this->getMiddlename()) {
            $name .= ' ' . $this->getMiddlename();
        }
        $name .= ' ' . $this->getLastname();
        if ($this->_config->getAttribute('baker', 'suffix')->getIsVisible() && $this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * Add address to address collection
     *
     * @param   Address $address
     * @return  $this
     */
    public function addAddress(Address $address)
    {
        $this->getAddressesCollection()->addItem($address);
        return $this;
    }

    /**
     * Retrieve baker address by address id
     *
     * @param   int $addressId
     * @return  Address
     */
    public function getAddressById($addressId)
    {
        return $this->_createAddressInstance()->load($addressId);
    }

    /**
     * Getting baker address object from collection by identifier
     *
     * @param int $addressId
     * @return Address
     */
    public function getAddressItemById($addressId)
    {
        return $this->getAddressesCollection()->getItemById($addressId);
    }

    /**
     * Retrieve not loaded address collection
     *
     * @return \CND\Baker\Model\ResourceModel\Address\Collection
     */
    public function getAddressCollection()
    {
        return $this->_createAddressCollection();
    }

    /**
     * Baker addresses collection
     *
     * @return \CND\Baker\Model\ResourceModel\Address\Collection
     */
    public function getAddressesCollection()
    {
        if ($this->_addressesCollection === null) {
            $this->_addressesCollection = $this->getAddressCollection()->setBakerFilter(
                $this
            )->addAttributeToSelect(
                '*'
            );
            foreach ($this->_addressesCollection as $address) {
                $address->setBaker($this);
            }
        }

        return $this->_addressesCollection;
    }

    /**
     * Retrieve baker address array
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAddresses()
    {
        return $this->getAddressesCollection()->getItems();
    }

    /**
     * Retrieve all baker attributes
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_getResource()->loadAllAttributes($this)->getSortedAttributes();
        }
        return $this->_attributes;
    }

    /**
     * Get baker attribute model object
     *
     * @param   string $attributeCode
     * @return  \CND\Baker\Model\ResourceModel\Attribute | null
     */
    public function getAttribute($attributeCode)
    {
        $this->getAttributes();
        if (isset($this->_attributes[$attributeCode])) {
            return $this->_attributes[$attributeCode];
        }
        return null;
    }

    /**
     * Set plain and hashed password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->setData('password', $password);
        $this->setPasswordHash($this->hashPassword($password));
        return $this;
    }

    /**
     * Hash baker password
     *
     * @param string $password
     * @param bool|int|string $salt
     * @return string
     */
    public function hashPassword($password, $salt = true)
    {
        return $this->_encryptor->getHash($password, $salt);
    }

    /**
     * Validate password with salted hash
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        $hash = $this->getPasswordHash();
        if (!$hash) {
            return false;
        }
        return $this->_encryptor->validateHash($password, $hash);
    }

    /**
     * Encrypt password
     *
     * @param   string $password
     * @return  string
     */
    public function encryptPassword($password)
    {
        return $this->_encryptor->encrypt($password);
    }

    /**
     * Decrypt password
     *
     * @param   string $password
     * @return  string
     */
    public function decryptPassword($password)
    {
        return $this->_encryptor->decrypt($password);
    }

    /**
     * Retrieve default address by type(attribute)
     *
     * @param   string $attributeCode address type attribute code
     * @return  Address|false
     */
    public function getPrimaryAddress($attributeCode)
    {
        $primaryAddress = $this->getAddressesCollection()->getItemById($this->getData($attributeCode));

        return $primaryAddress ? $primaryAddress : false;
    }

    /**
     * Get baker default billing address
     *
     * @return Address
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getPrimaryAddress('default_billing');
    }

    /**
     * Get baker default billing address
     *
     * @return Address
     */
    public function getDefaultBillingAddress()
    {
        return $this->getPrimaryBillingAddress();
    }

    /**
     * Get default baker shipping address
     *
     * @return Address
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getPrimaryAddress('default_shipping');
    }

    /**
     * Get default baker shipping address
     *
     * @return Address
     */
    public function getDefaultShippingAddress()
    {
        return $this->getPrimaryShippingAddress();
    }

    /**
     * Retrieve ids of default addresses
     *
     * @return array
     */
    public function getPrimaryAddressIds()
    {
        $ids = [];
        if ($this->getDefaultBilling()) {
            $ids[] = $this->getDefaultBilling();
        }
        if ($this->getDefaultShipping()) {
            $ids[] = $this->getDefaultShipping();
        }
        return $ids;
    }

    /**
     * Retrieve all baker default addresses
     *
     * @return Address[]
     */
    public function getPrimaryAddresses()
    {
        $addresses = [];
        $primaryBilling = $this->getPrimaryBillingAddress();
        if ($primaryBilling) {
            $addresses[] = $primaryBilling;
            $primaryBilling->setIsPrimaryBilling(true);
        }

        $primaryShipping = $this->getPrimaryShippingAddress();
        if ($primaryShipping) {
            if ($primaryBilling && $primaryBilling->getId() == $primaryShipping->getId()) {
                $primaryBilling->setIsPrimaryShipping(true);
            } else {
                $primaryShipping->setIsPrimaryShipping(true);
                $addresses[] = $primaryShipping;
            }
        }
        return $addresses;
    }

    /**
     * Retrieve not default addresses
     *
     * @return Address[]
     */
    public function getAdditionalAddresses()
    {
        $addresses = [];
        $primatyIds = $this->getPrimaryAddressIds();
        foreach ($this->getAddressesCollection() as $address) {
            if (!in_array($address->getId(), $primatyIds)) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    /**
     * Check if address is primary
     *
     * @param Address $address
     * @return boolean
     */
    public function isAddressPrimary(Address $address)
    {
        if (!$address->getId()) {
            return false;
        }
        return $address->getId() == $this->getDefaultBilling() || $address->getId() == $this->getDefaultShipping();
    }

    /**
     * Send email with new account related information
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the transactional account email type.')
            );
        }

        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
        }

        $this->_sendEmailTemplate(
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['baker' => $this, 'back_url' => $backUrl, 'store' => $this->getStore()],
            $storeId
        );

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @return bool
     * @deprecated
     * @see AccountConfirmation::isConfirmationRequired
     */
    public function isConfirmationRequired()
    {
        $websiteId = $this->getWebsiteId() ? $this->getWebsiteId() : null;

        return $this->accountConfirmation->isConfirmationRequired($websiteId, $this->getId(), $this->getEmail());
    }

    /**
     * Generate random confirmation key
     *
     * @return string
     */
    public function getRandomConfirmationKey()
    {
        return md5(uniqid());
    }

    /**
     * Send email with new baker password
     *
     * @return $this
     */
    public function sendPasswordReminderEmail()
    {
        $this->_sendEmailTemplate(
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $this, 'store' => $this->getStore()],
            $this->getStoreId()
        );

        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = [], $storeId = null)
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $this->_scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        )->getTransport();
        $transport->sendMessage();

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @return $this
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $storeId = $this->getStoreId();
        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId();
        }

        $this->_sendEmailTemplate(
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $this, 'store' => $this->getStore()],
            $storeId
        );

        return $this;
    }

    /**
     * Retrieve baker group identifier
     *
     * @return int
     */
    public function getGroupId()
    {
        if (!$this->hasData('group_id')) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : $this->_storeManager->getStore()->getId();
            $groupId = $this->_scopeConfig->getValue(
                GroupManagement::XML_PATH_DEFAULT_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->setData('group_id', $groupId);
        }
        return $this->getData('group_id');
    }

    /**
     * Retrieve baker tax class identifier
     *
     * @return int
     */
    public function getTaxClassId()
    {
        if (!$this->getData('tax_class_id')) {
            $groupTaxClassId = $this->_groupRepository->getById($this->getGroupId())->getTaxClassId();
            $this->setData('tax_class_id', $groupTaxClassId);
        }
        return $this->getData('tax_class_id');
    }

    /**
     * Retrieve store where baker was created
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Retrieve shared store ids
     *
     * @return array
     */
    public function getSharedStoreIds()
    {
        $ids = $this->_getData('shared_store_ids');
        if ($ids === null) {
            $ids = [];
            if ((bool)$this->getSharingConfig()->isWebsiteScope()) {
                $ids = $this->_storeManager->getWebsite($this->getWebsiteId())->getStoreIds();
            } else {
                foreach ($this->_storeManager->getStores() as $store) {
                    $ids[] = $store->getId();
                }
            }
            $this->setData('shared_store_ids', $ids);
        }

        return $ids;
    }

    /**
     * Retrieve shared website ids
     *
     * @return int[]
     */
    public function getSharedWebsiteIds()
    {
        $ids = $this->_getData('shared_website_ids');
        if ($ids === null) {
            $ids = [];
            if ((bool)$this->getSharingConfig()->isWebsiteScope()) {
                $ids[] = $this->getWebsiteId();
            } else {
                foreach ($this->_storeManager->getWebsites() as $website) {
                    $ids[] = $website->getId();
                }
            }
            $this->setData('shared_website_ids', $ids);
        }
        return $ids;
    }

    /**
     * Set store to baker
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        $this->setWebsiteId($store->getWebsite()->getId());
        return $this;
    }

    /**
     * Validate baker attribute values.
     *
     * @deprecated 100.1.0
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Unset subscription
     *
     * @return $this
     */
    public function unsetSubscription()
    {
        if (isset($this->_isSubscribed)) {
            unset($this->_isSubscribed);
        }
        return $this;
    }

    /**
     * Clean all addresses
     *
     * @return void
     */
    public function cleanAllAddresses()
    {
        $this->_addressesCollection = null;
    }

    /**
     * Add error
     *
     * @param mixed $error
     * @return $this
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * Retrieve errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Reset errors array
     *
     * @return $this
     */
    public function resetErrors()
    {
        $this->_errors = [];
        return $this;
    }

    /**
     * Prepare baker for delete
     *
     * @return $this
     */
    public function beforeDelete()
    {
        //TODO : Revisit and figure handling permissions in MAGETWO-11084 Implementation: Service Context Provider
        return parent::beforeDelete();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        $indexer = $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID);
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
        $indexer = $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexRow($this->getId());
    }

    /**
     * Get baker created at date timestamp
     *
     * @return int|null
     */
    public function getCreatedAtTimestamp()
    {
        $date = $this->getCreatedAt();
        if ($date) {
            return (new \DateTime($date))->getTimestamp();
        }
        return null;
    }

    /**
     * Reset all model data
     *
     * @return $this
     */
    public function reset()
    {
        $this->setData([]);
        $this->setOrigData();
        $this->_attributes = null;

        return $this;
    }

    /**
     * Checks model is deletable
     *
     * @return boolean
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Set is deletable flag
     *
     * @param boolean $value
     * @return $this
     */
    public function setIsDeleteable($value)
    {
        $this->_isDeleteable = (bool)$value;
        return $this;
    }

    /**
     * Checks model is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is readonly flag
     *
     * @param boolean $value
     * @return $this
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (bool)$value;
        return $this;
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @return bool
     * @deprecated
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function canSkipConfirmation()
    {
        if (!$this->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->_registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($this->getEmail());
    }

    /**
     * Clone current object
     *
     * @return void
     */
    public function __clone()
    {
        $newAddressCollection = $this->getPrimaryAddresses();
        $newAddressCollection = array_merge($newAddressCollection, $this->getAdditionalAddresses());
        $this->setId(null);
        $this->cleanAllAddresses();
        foreach ($newAddressCollection as $address) {
            $this->addAddress(clone $address);
        }
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
     * Get either first store ID from a set website or the provided as default
     *
     * @param int|string|null $defaultStoreId
     *
     * @return int
     */
    protected function _getWebsiteStoreId($defaultStoreId = null)
    {
        if ($this->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->_storeManager->getWebsite($this->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param string $passwordLinkToken
     * @return $this
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function changeResetPasswordLinkToken($passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new AuthenticationException(
                __('Please enter a valid password reset token.')
            );
        }
        $this->_getResource()->changeResetPasswordLinkToken($this, $passwordLinkToken);
        return $this;
    }

    /**
     * Check if current reset password link token is expired
     *
     * @return boolean
     */
    public function isResetPasswordLinkTokenExpired()
    {
        $linkToken = $this->getRpToken();
        $linkTokenCreatedAt = $this->getRpTokenCreatedAt();

        if (empty($linkToken) || empty($linkTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = (new \DateTime())->getTimestamp();
        $tokenTimestamp = (new \DateTime($linkTokenCreatedAt))->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $dayDifference = floor(($currentTimestamp - $tokenTimestamp) / (24 * 60 * 60));
        if ($dayDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve baker reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @return Address
     */
    protected function _createAddressInstance()
    {
        return $this->_addressFactory->create();
    }

    /**
     * @return \CND\Baker\Model\ResourceModel\Address\Collection
     */
    protected function _createAddressCollection()
    {
        return $this->_addressesFactory->create();
    }

    /**
     * @return array
     */
    protected function getTemplateTypes()
    {
        /**
         * 'registered'   welcome email, when confirmation is disabled
         * 'confirmed'    welcome email, when confirmation is enabled
         * 'confirmation' email with confirmation link
         */
        $types = [
            'registered' => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            'confirmed' => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
        ];
        return $types;
    }

    /**
     * Check if baker is locked
     *
     * @return boolean
     * @since 100.1.0
     */
    public function isBakerLocked()
    {
        if ($this->getLockExpires()) {
            $lockExpires = new \DateTime($this->getLockExpires());
            if ($lockExpires > new \DateTime()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return Password Confirmation
     *
     * @return string
     * @since 100.1.0
     */
    public function getPasswordConfirm()
    {
        return (string) $this->getData('password_confirm');
    }

    /**
     * Return Password
     *
     * @return string
     * @since 100.1.0
     */
    public function getPassword()
    {
        return (string) $this->getData('password');
    }
}
