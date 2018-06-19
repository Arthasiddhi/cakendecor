<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Observer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\BakerRegistry;

class UpgradeBakerPasswordObserver implements ObserverInterface
{
    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var BakerRegistry
     */
    private $bakerRegistry;

    /**
     * @var BakerRepositoryInterface
     */
    private $bakerRepository;

    /**
     * @param EncryptorInterface $encryptor
     * @param BakerRegistry $bakerRegistry
     * @param BakerRepositoryInterface $bakerRepository
     */
    public function __construct(
        EncryptorInterface $encryptor,
        BakerRegistry $bakerRegistry,
        BakerRepositoryInterface $bakerRepository
    ) {
        $this->encryptor = $encryptor;
        $this->bakerRegistry = $bakerRegistry;
        $this->bakerRepository = $bakerRepository;
    }

    /**
     * Upgrade baker password hash when baker has logged in
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $password = $observer->getEvent()->getData('password');
        /** @var \CND\Baker\Model\Baker $model */
        $model = $observer->getEvent()->getData('model');
        $baker = $this->bakerRepository->getById($model->getId());
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($model->getId());

        if (!$this->encryptor->validateHashVersion($bakerSecure->getPasswordHash(), true)) {
            $bakerSecure->setPasswordHash($this->encryptor->getHash($password, true));
            $this->bakerRepository->save($baker);
        }
    }
}
