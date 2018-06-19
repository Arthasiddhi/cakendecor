<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\ResourceModel\BakerRepository;
use CND\Baker\Model\BakerAuthUpdate;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class Authentication
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authentication implements AuthenticationInterface
{
    /**
     * Configuration path to baker lockout threshold
     */
    const LOCKOUT_THRESHOLD_PATH = 'baker/password/lockout_threshold';

    /**
     * Configuration path to baker max login failures number
     */
    const MAX_FAILURES_PATH = 'baker/password/lockout_failures';

    /**
     * @var BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var BakerAuthUpdate
     */
    private $bakerAuthUpdate;

    /**
     * @param BakerRepositoryInterface $bakerRepository
     * @param BakerRegistry $bakerRegistry
     * @param ConfigInterface $backendConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Encryptor $encryptor
     */
    public function __construct(
        BakerRepositoryInterface $bakerRepository,
        BakerRegistry $bakerRegistry,
        ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Encryptor $encryptor
    ) {
        $this->bakerRepository = $bakerRepository;
        $this->bakerRegistry = $bakerRegistry;
        $this->backendConfig = $backendConfig;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function processAuthenticationFailure($bakerId)
    {
        $now = new \DateTime();
        $lockThreshold = $this->getLockThreshold();
        $maxFailures =  $this->getMaxFailures();
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($bakerId);

        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$bakerSecure->getFailuresNum() + 1;

        $firstFailureDate = $bakerSecure->getFirstFailure();
        if ($firstFailureDate) {
            $firstFailureDate = new \DateTime($firstFailureDate);
        }

        $lockThreshInterval = new \DateInterval('PT' . $lockThreshold . 'S');
        $lockExpires = $bakerSecure->getLockExpires();
        $lockExpired = ($lockExpires !== null) && ($now > new \DateTime($lockExpires));
        // set first failure date when this is the first failure or the lock is expired
        if (1 === $failuresNum || !$firstFailureDate || $lockExpired) {
            $bakerSecure->setFirstFailure($this->dateTime->formatDate($now));
            $failuresNum = 1;
            $bakerSecure->setLockExpires(null);
            // otherwise lock baker
        } elseif ($failuresNum >= $maxFailures) {
            $bakerSecure->setLockExpires($this->dateTime->formatDate($now->add($lockThreshInterval)));
        }

        $bakerSecure->setFailuresNum($failuresNum);
        $this->getBakerAuthUpdate()->saveAuth($bakerId);
    }

    /**
     * {@inheritdoc}
     */
    public function unlock($bakerId)
    {
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($bakerId);
        $bakerSecure->setFailuresNum(0);
        $bakerSecure->setFirstFailure(null);
        $bakerSecure->setLockExpires(null);
        $this->getBakerAuthUpdate()->saveAuth($bakerId);
    }

    /**
     * Get lock threshold
     *
     * @return int
     */
    protected function getLockThreshold()
    {
        return $this->backendConfig->getValue(self::LOCKOUT_THRESHOLD_PATH) * 60;
    }

    /**
     * Get max failures
     *
     * @return int
     */
    protected function getMaxFailures()
    {
        return $this->backendConfig->getValue(self::MAX_FAILURES_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked($bakerId)
    {
        $currentBaker = $this->bakerRegistry->retrieve($bakerId);
        return $currentBaker->isBakerLocked();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($bakerId, $password)
    {
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($bakerId);
        $hash = $bakerSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            $this->processAuthenticationFailure($bakerId);
            if ($this->isLocked($bakerId)) {
                throw new UserLockedException(__('The account is locked.'));
            }
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return true;
    }

    /**
     * Get baker authentication update model
     *
     * @return \CND\Baker\Model\BakerAuthUpdate
     * @deprecated 100.1.1
     */
    private function getBakerAuthUpdate()
    {
        if ($this->bakerAuthUpdate === null) {
            $this->bakerAuthUpdate =
                \Magento\Framework\App\ObjectManager::getInstance()->get(BakerAuthUpdate::class);
        }
        return $this->bakerAuthUpdate;
    }
}
