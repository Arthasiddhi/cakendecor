<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Observer;

use CND\Baker\Model\AuthenticationInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BakerLoginSuccessObserver
 */
class BakerLoginSuccessObserver implements ObserverInterface
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        AuthenticationInterface $authentication
    ) {
        $this->authentication = $authentication;
    }

    /**
     * Unlock baker on success login attempt.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \CND\Baker\Model\Baker $baker */
        $baker = $observer->getEvent()->getData('model');
        $this->authentication->unlock($baker->getId());
        return $this;
    }
}
