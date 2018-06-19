<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

use CND\Baker\Model\Data\BakerSecure;
use CND\Baker\Model\Data\BakerSecureFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Registry for \CND\Baker\Model\Baker
 */
class BakerRegistry
{
    const REGISTRY_SEPARATOR = ':';

    /**
     * @var BakerFactory
     */
    private $bakerFactory;

    /**
     * @var BakerSecureFactory
     */
    private $bakerSecureFactory;

    /**
     * @var array
     */
    private $bakerRegistryById = [];

    /**
     * @var array
     */
    private $bakerRegistryByEmail = [];

    /**
     * @var array
     */
    private $bakerSecureRegistryById = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param BakerFactory $bakerFactory
     * @param BakerSecureFactory $bakerSecureFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BakerFactory $bakerFactory,
        BakerSecureFactory $bakerSecureFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->bakerFactory = $bakerFactory;
        $this->bakerSecureFactory = $bakerSecureFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Baker Model from registry given an id
     *
     * @param string $bakerId
     * @return Baker
     * @throws NoSuchEntityException
     */
    public function retrieve($bakerId)
    {
        if (isset($this->bakerRegistryById[$bakerId])) {
            return $this->bakerRegistryById[$bakerId];
        }
        /** @var Baker $baker */
        $baker = $this->bakerFactory->create()->load($bakerId);
        if (!$baker->getId()) {
            // baker does not exist
            throw NoSuchEntityException::singleField('bakerId', $bakerId);
        } else {
            $emailKey = $this->getEmailKey($baker->getEmail(), $baker->getWebsiteId());
            $this->bakerRegistryById[$bakerId] = $baker;
            $this->bakerRegistryByEmail[$emailKey] = $baker;
            return $baker;
        }
    }

    /**
     * Retrieve Baker Model from registry given an email
     *
     * @param string $bakerEmail Bakers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Baker
     * @throws NoSuchEntityException
     */
    public function retrieveByEmail($bakerEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($bakerEmail, $websiteId);
        if (isset($this->bakerRegistryByEmail[$emailKey])) {
            return $this->bakerRegistryByEmail[$emailKey];
        }

        /** @var Baker $baker */
        $baker = $this->bakerFactory->create();

        if (isset($websiteId)) {
            $baker->setWebsiteId($websiteId);
        }

        $baker->loadByEmail($bakerEmail);
        if (!$baker->getEmail()) {
            // baker does not exist
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'email',
                        'fieldValue' => $bakerEmail,
                        'field2Name' => 'websiteId',
                        'field2Value' => $websiteId
                    ]
                )
            );
        } else {
            $this->bakerRegistryById[$baker->getId()] = $baker;
            $this->bakerRegistryByEmail[$emailKey] = $baker;
            return $baker;
        }
    }

    /**
     * Retrieve BakerSecure Model from registry given an id
     *
     * @param int $bakerId
     * @return BakerSecure
     * @throws NoSuchEntityException
     */
    public function retrieveSecureData($bakerId)
    {
        if (isset($this->bakerSecureRegistryById[$bakerId])) {
            return $this->bakerSecureRegistryById[$bakerId];
        }
        /** @var Baker $baker */
        $baker = $this->retrieve($bakerId);
        /** @var $bakerSecure BakerSecure*/
        $bakerSecure = $this->bakerSecureFactory->create();
        $bakerSecure->setPasswordHash($baker->getPasswordHash());
        $bakerSecure->setRpToken($baker->getRpToken());
        $bakerSecure->setRpTokenCreatedAt($baker->getRpTokenCreatedAt());
        $bakerSecure->setDeleteable($baker->isDeleteable());
        $bakerSecure->setFailuresNum($baker->getFailuresNum());
        $bakerSecure->setFirstFailure($baker->getFirstFailure());
        $bakerSecure->setLockExpires($baker->getLockExpires());
        $this->bakerSecureRegistryById[$baker->getId()] = $bakerSecure;

        return $bakerSecure;
    }

    /**
     * Remove instance of the Baker Model from registry given an id
     *
     * @param int $bakerId
     * @return void
     */
    public function remove($bakerId)
    {
        if (isset($this->bakerRegistryById[$bakerId])) {
            /** @var Baker $baker */
            $baker = $this->bakerRegistryById[$bakerId];
            $emailKey = $this->getEmailKey($baker->getEmail(), $baker->getWebsiteId());
            unset($this->bakerRegistryByEmail[$emailKey]);
            unset($this->bakerRegistryById[$bakerId]);
            unset($this->bakerSecureRegistryById[$bakerId]);
        }
    }

    /**
     * Remove instance of the Baker Model from registry given an email
     *
     * @param string $bakerEmail Bakers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return void
     */
    public function removeByEmail($bakerEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($bakerEmail, $websiteId);
        if ($emailKey) {
            /** @var Baker $baker */
            $baker = $this->bakerRegistryByEmail[$emailKey];
            unset($this->bakerRegistryByEmail[$emailKey]);
            unset($this->bakerRegistryById[$baker->getId()]);
            unset($this->bakerSecureRegistryById[$baker->getId()]);
        }
    }

    /**
     * Create registry key
     *
     * @param string $bakerEmail
     * @param string $websiteId
     * @return string
     */
    protected function getEmailKey($bakerEmail, $websiteId)
    {
        return $bakerEmail . self::REGISTRY_SEPARATOR . $websiteId;
    }

    /**
     * Replace existing baker model with a new one.
     *
     * @param Baker $baker
     * @return $this
     */
    public function push(Baker $baker)
    {
        $this->bakerRegistryById[$baker->getId()] = $baker;
        $emailKey = $this->getEmailKey($baker->getEmail(), $baker->getWebsiteId());
        $this->bakerRegistryByEmail[$emailKey] = $baker;
        return $this;
    }
}
