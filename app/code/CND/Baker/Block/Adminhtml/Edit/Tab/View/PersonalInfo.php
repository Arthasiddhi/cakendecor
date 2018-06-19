<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit\Tab\View;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Controller\RegistryConstants;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use CND\Baker\Model\Baker;

/**
 * Adminhtml baker view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfo extends \Magento\Backend\Block\Template
{
    /**
     * Interval in minutes that shows how long baker will be marked 'Online'
     * since his last activity. Used only if it's impossible to get such setting
     * from configuration.
     */
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    /**
     * Baker
     *
     * @var \CND\Baker\Api\Data\BakerInterface
     */
    protected $baker;

    /**
     * Baker log
     *
     * @var \CND\Baker\Model\Log
     */
    protected $bakerLog;

    /**
     * Baker logger
     *
     * @var \CND\Baker\Model\Logger
     */
    protected $bakerLogger;

    /**
     * Baker registry
     *
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Baker group repository
     *
     * @var \CND\Baker\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Baker data factory
     *
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory
     */
    protected $bakerDataFactory;

    /**
     * Address helper
     *
     * @var \CND\Baker\Helper\Address
     */
    protected $addressHelper;

    /**
     * Date time
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \CND\Baker\Api\GroupRepositoryInterface $groupRepository
     * @param \CND\Baker\Api\Data\BakerInterfaceFactory $bakerDataFactory
     * @param \CND\Baker\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \CND\Baker\Model\Logger $bakerLogger
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \CND\Baker\Api\GroupRepositoryInterface $groupRepository,
        \CND\Baker\Api\Data\BakerInterfaceFactory $bakerDataFactory,
        \CND\Baker\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \CND\Baker\Model\Logger $bakerLogger,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->accountManagement = $accountManagement;
        $this->groupRepository = $groupRepository;
        $this->bakerDataFactory = $bakerDataFactory;
        $this->addressHelper = $addressHelper;
        $this->dateTime = $dateTime;
        $this->addressMapper = $addressMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->bakerLogger = $bakerLogger;

        parent::__construct($context, $data);
    }

    /**
     * Set baker registry
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     * @deprecated 100.1.0
     */
    public function setBakerRegistry(\CND\Baker\Model\BakerRegistry $bakerRegistry)
    {

        $this->bakerRegistry = $bakerRegistry;
    }

    /**
     * Get baker registry
     *
     * @return \CND\Baker\Model\BakerRegistry
     * @deprecated 100.1.0
     */
    public function getBakerRegistry()
    {

        if (!($this->bakerRegistry instanceof \CND\Baker\Model\BakerRegistry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \CND\Baker\Model\BakerRegistry::class
            );
        } else {
            return $this->bakerRegistry;
        }
    }

    /**
     * Retrieve baker object
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    public function getBaker()
    {
        if (!$this->baker) {
            $this->baker = $this->bakerDataFactory->create();
            $data = $this->_backendSession->getBakerData();
            $this->dataObjectHelper->populateWithArray(
                $this->baker,
                $data['account'],
                \CND\Baker\Api\Data\BakerInterface::class
            );
        }
        return $this->baker;
    }

    /**
     * Retrieve baker id
     *
     * @return string|null
     */
    public function getBakerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Retrieves baker log model
     *
     * @return \CND\Baker\Model\Log
     */
    protected function getBakerLog()
    {
        if (!$this->bakerLog) {
            $this->bakerLog = $this->bakerLogger->get(
                $this->getBaker()->getId()
            );
        }

        return $this->bakerLog;
    }

    /**
     * Returns baker's created date in the assigned store
     *
     * @return string
     */
    public function getStoreCreateDate()
    {
        $createdAt = $this->getBaker()->getCreatedAt();
        try {
            return $this->formatDate(
                $createdAt,
                \IntlDateFormatter::MEDIUM,
                true,
                $this->getStoreCreateDateTimezone()
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * Retrieve store default timezone from configuration
     *
     * @return string
     */
    public function getStoreCreateDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getBaker()->getStoreId()
        );
    }

    /**
     * Get baker creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->formatDate(
            $this->getBaker()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    /**
     * Check if account is confirmed
     *
     * @return \Magento\Framework\Phrase
     */
    public function getIsConfirmedStatus()
    {
        $id = $this->getBakerId();
        switch ($this->accountManagement->getConfirmationStatus($id)) {
            case AccountManagementInterface::ACCOUNT_CONFIRMED:
                return __('Confirmed');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED:
                return __('Confirmation Required');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED:
                return __('Confirmation Not Required');
        }
        return __('Indeterminate');
    }

    /**
     * Retrieve store
     *
     * @return null|string
     */
    public function getCreatedInStore()
    {
        return $this->_storeManager->getStore(
            $this->getBaker()->getStoreId()
        )->getName();
    }

    /**
     * Retrieve billing address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getBillingAddressHtml()
    {
        try {
            $address = $this->accountManagement->getDefaultBillingAddress($this->getBaker()->getId());
        } catch (NoSuchEntityException $e) {
            return __('The baker does not have default billing address.');
        }

        if ($address === null) {
            return __('The baker does not have default billing address.');
        }

        return $this->addressHelper->getFormatTypeRenderer(
            'html'
        )->renderArray(
            $this->addressMapper->toFlatArray($address)
        );
    }

    /**
     * Retrieve group name
     *
     * @return string|null
     */
    public function getGroupName()
    {
        $baker = $this->getBaker();
        if ($groupId = $baker->getId() ? $baker->getGroupId() : null) {
            if ($group = $this->getGroup($groupId)) {
                return $group->getCode();
            }
        }

        return null;
    }

    /**
     * Retrieve baker group by id
     *
     * @param int $groupId
     * @return \CND\Baker\Api\Data\GroupInterface|null
     */
    private function getGroup($groupId)
    {
        try {
            $group = $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            $group = null;
        }
        return $group;
    }

    /**
     * Returns timezone of the store to which baker assigned.
     *
     * @return string
     */
    public function getStoreLastLoginDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getBaker()->getStoreId()
        );
    }

    /**
     * Get baker's current status.
     *
     * Baker considered 'Offline' in the next cases:
     *
     * - baker has never been logged in;
     * - baker clicked 'Log Out' link\button;
     * - predefined interval has passed since baker's last activity.
     *
     * In all other cases baker considered 'Online'.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getCurrentStatus()
    {
        $lastLoginTime = $this->getBakerLog()->getLastLoginAt();

        // Baker has never been logged in.
        if (!$lastLoginTime) {
            return __('Offline');
        }

        $lastLogoutTime = $this->getBakerLog()->getLastLogoutAt();

        // Baker clicked 'Log Out' link\button.
        if ($lastLogoutTime && strtotime($lastLogoutTime) > strtotime($lastLoginTime)) {
            return __('Offline');
        }

        // Predefined interval has passed since baker's last activity.
        $interval = $this->getOnlineMinutesInterval();
        $currentTimestamp = (new \DateTime())->getTimestamp();
        $lastVisitTime = $this->getBakerLog()->getLastVisitAt();

        if ($lastVisitTime && $currentTimestamp - strtotime($lastVisitTime) > $interval * 60) {
            return __('Offline');
        }

        return __('Online');
    }

    /**
     * Get baker last login date.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getLastLoginDate()
    {
        $date = $this->getBakerLog()->getLastLoginAt();

        if ($date) {
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns baker last login date in store's timezone.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getStoreLastLoginDate()
    {
        $date = strtotime($this->getBakerLog()->getLastLoginAt());

        if ($date) {
            $date = $this->_localeDate->scopeDate($this->getBaker()->getStoreId(), $date, true);
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns interval that shows how long baker will be considered 'Online'.
     *
     * @return int Interval in minutes
     */
    protected function getOnlineMinutesInterval()
    {
        $configValue = $this->_scopeConfig->getValue(
            'baker/online_bakers/online_minutes_interval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return intval($configValue) > 0 ? intval($configValue) : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Get baker account lock status
     *
     * @return \Magento\Framework\Phrase
     */
    public function getAccountLock()
    {
        $bakerModel = $this->getBakerRegistry()->retrieve($this->getBakerId());
        $bakerStatus = __('Unlocked');
        if ($bakerModel->isBakerLocked()) {
            $bakerStatus = __('Locked');
        }
        return $bakerStatus;
    }
}
