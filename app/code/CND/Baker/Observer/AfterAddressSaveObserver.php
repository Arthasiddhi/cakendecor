<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Observer;

use CND\Baker\Api\GroupManagementInterface;
use CND\Baker\Helper\Address as HelperAddress;
use CND\Baker\Model\Address;
use CND\Baker\Model\Address\AbstractAddress;
use CND\Baker\Model\Session as BakerSession;
use CND\Baker\Model\Vat;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Baker Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation processed flag code
     */
    const VIV_PROCESSED_FLAG = 'viv_after_address_save_processed';

    /**
     * @var HelperAddress
     */
    protected $_bakerAddress;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Vat
     */
    protected $_bakerVat;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var BakerSession
     */
    private $bakerSession;

    /**
     * @param Vat $bakerVat
     * @param HelperAddress $bakerAddress
     * @param Registry $coreRegistry
     * @param GroupManagementInterface $groupManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param AppState $appState
     * @param BakerSession $bakerSession
     */
    public function __construct(
        Vat $bakerVat,
        HelperAddress $bakerAddress,
        Registry $coreRegistry,
        GroupManagementInterface $groupManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Escaper $escaper,
        AppState $appState,
        BakerSession $bakerSession
    ) {
        $this->_bakerVat = $bakerVat;
        $this->_bakerAddress = $bakerAddress;
        $this->_coreRegistry = $coreRegistry;
        $this->_groupManagement = $groupManagement;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->appState = $appState;
        $this->bakerSession = $bakerSession;
    }

    /**
     * Address after save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $bakerAddress Address */
        $bakerAddress = $observer->getBakerAddress();
        $baker = $bakerAddress->getBaker();

        if (!$this->_bakerAddress->isVatValidationEnabled($baker->getStore())
            || $this->_coreRegistry->registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($bakerAddress)
        ) {
            return;
        }

        try {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, true);

            if ($bakerAddress->getVatId() == ''
                || !$this->_bakerVat->isCountryInEU($bakerAddress->getCountry())
            ) {
                $defaultGroupId = $this->_groupManagement->getDefaultGroup($baker->getStore())->getId();
                if (!$baker->getDisableAutoGroupChange() && $baker->getGroupId() != $defaultGroupId) {
                    $baker->setGroupId($defaultGroupId);
                    $baker->save();
                    $this->bakerSession->setBakerGroupId($defaultGroupId);
                }
            } else {
                $result = $this->_bakerVat->checkVatNumber(
                    $bakerAddress->getCountryId(),
                    $bakerAddress->getVatId()
                );

                $newGroupId = $this->_bakerVat->getBakerGroupIdBasedOnVatNumber(
                    $bakerAddress->getCountryId(),
                    $result,
                    $baker->getStore()
                );

                if (!$baker->getDisableAutoGroupChange() && $baker->getGroupId() != $newGroupId) {
                    $baker->setGroupId($newGroupId);
                    $baker->save();
                    $this->bakerSession->setBakerGroupId($newGroupId);
                }

                $bakerAddress->setVatValidationResult($result);

                if ($this->appState->getAreaCode() == Area::AREA_FRONTEND) {
                    if ($result->getIsValid()) {
                        $this->addValidMessage($bakerAddress, $result);
                    } elseif ($result->getRequestSuccess()) {
                        $this->addInvalidMessage($bakerAddress);
                    } else {
                        $this->addErrorMessage($bakerAddress);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, false, true);
        }
    }

    /**
     * Check whether specified address should be processed in after_save event handler
     *
     * @param Address $address
     * @return bool
     */
    protected function _canProcessAddress($address)
    {
        if ($address->getForceProcess()) {
            return true;
        }

        if ($this->_coreRegistry->registry(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS) != $address->getId()
        ) {
            return false;
        }

        $configAddressType = $this->_bakerAddress->getTaxCalculationAddressType();
        if ($configAddressType == AbstractAddress::TYPE_SHIPPING) {
            return $this->_isDefaultShipping($address);
        }

        return $this->_isDefaultBilling($address);
    }

    /**
     * Check whether specified billing address is default for its baker
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getBaker()->getDefaultBilling()
        || $address->getIsPrimaryBilling()
        || $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for its baker
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getBaker()->getDefaultShipping()
        || $address->getIsPrimaryShipping()
        || $address->getIsDefaultShipping();
    }

    /**
     * Add success message for valid VAT ID
     *
     * @param Address $bakerAddress
     * @param DataObject $validationResult
     * @return $this
     */
    protected function addValidMessage($bakerAddress, $validationResult)
    {
        $message = [
            (string)__('Your VAT ID was successfully validated.'),
        ];

        $baker = $bakerAddress->getBaker();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$baker->getDisableAutoGroupChange()
        ) {
            $bakerVatClass = $this->_bakerVat->getBakerVatClass(
                $bakerAddress->getCountryId(),
                $validationResult
            );
            $message[] = $bakerVatClass == Vat::VAT_CLASS_DOMESTIC
                ? (string)__('You will be charged tax.')
                : (string)__('You will not be charged tax.');
        }

        $this->messageManager->addSuccess(implode(' ', $message));

        return $this;
    }

    /**
     * Add error message for invalid VAT ID
     *
     * @param Address $bakerAddress
     * @return $this
     */
    protected function addInvalidMessage($bakerAddress)
    {
        $vatId = $this->escaper->escapeHtml($bakerAddress->getVatId());
        $message = [
            (string)__('The VAT ID entered (%1) is not a valid VAT ID.', $vatId),
        ];

        $baker = $bakerAddress->getBaker();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$baker->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $this->messageManager->addError(implode(' ', $message));

        return $this;
    }

    /**
     * Add error message
     *
     * @param Address $bakerAddress
     * @return $this
     */
    protected function addErrorMessage($bakerAddress)
    {
        $message = [
            (string)__('Your Tax ID cannot be validated.'),
        ];

        $baker = $bakerAddress->getBaker();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$baker->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $message[] = (string)__('If you believe this is an error, please contact us at %1', $email);

        $this->messageManager->addError(implode(' ', $message));

        return $this;
    }
}
