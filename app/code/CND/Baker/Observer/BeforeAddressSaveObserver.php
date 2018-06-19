<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Observer;

use CND\Baker\Helper\Address as HelperAddress;
use CND\Baker\Model\Address\AbstractAddress;
use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use CND\Baker\Model\Address;

/**
 * Baker Observer Model
 */
class BeforeAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation currently saved address flag
     */
    const VIV_CURRENTLY_SAVED_ADDRESS = 'currently_saved_address';

    /**
     * @var HelperAddress
     */
    protected $_bakerAddress;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param HelperAddress $bakerAddress
     * @param Registry $coreRegistry
     */
    public function __construct(
        HelperAddress $bakerAddress,
        Registry $coreRegistry
    ) {
        $this->_bakerAddress = $bakerAddress;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Address before save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry(self::VIV_CURRENTLY_SAVED_ADDRESS)) {
            $this->_coreRegistry->unregister(self::VIV_CURRENTLY_SAVED_ADDRESS);
        }

        /** @var $bakerAddress Address */
        $bakerAddress = $observer->getBakerAddress();
        if ($bakerAddress->getId()) {
            $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, $bakerAddress->getId());
        } else {
            $configAddressType = $this->_bakerAddress->getTaxCalculationAddressType();
            $forceProcess = $configAddressType == AbstractAddress::TYPE_SHIPPING
                ? $bakerAddress->getIsDefaultShipping()
                : $bakerAddress->getIsDefaultBilling();
            if ($forceProcess) {
                $bakerAddress->setForceProcess(true);
            } else {
                $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
            }
        }
    }
}
