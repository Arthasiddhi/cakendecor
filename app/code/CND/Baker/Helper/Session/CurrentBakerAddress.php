<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Helper\Session;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\Data\AddressInterface;

/**
 * Class CurrentBakerAddress
 */
class CurrentBakerAddress
{
    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param CurrentBaker $currentBaker
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        CurrentBaker $currentBaker,
        AccountManagementInterface $accountManagement
    ) {
        $this->currentBaker = $currentBaker;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Returns default billing address form current baker
     *
     * @return AddressInterface|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->accountManagement->getDefaultBillingAddress($this->currentBaker->getBakerId());
    }

    /**
     * Returns default shipping address for current baker
     *
     * @return AddressInterface|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->accountManagement->getDefaultShippingAddress(
            $this->currentBaker->getBakerId()
        );
    }
}
