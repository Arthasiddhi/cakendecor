<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\AddressInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Api\Data\ValidationResultsInterfaceFactory;
use CND\Baker\Helper\View as BakerViewHelper;
use CND\Baker\Model\Config\Share as ConfigShare;
use CND\Baker\Model\Baker as BakerModel;
use CND\Baker\Model\Baker\CredentialsValidator;
use CND\Baker\Model\Metadata\Validator;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use CND\Baker\Model\ResourceModel\Visitor\CollectionFactory;

/**
 * Handle various baker account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement implements AccountManagementInterface
{
    /**
     * Configuration paths for email templates and identities
     *
     * @deprecated
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'baker/create_account/email_template';

    /**
     * @deprecated
     */
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'baker/create_account/email_no_password_template';

    /**
     * @deprecated
     */
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'baker/create_account/email_identity';

    /**
     * @deprecated
     */
    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'baker/password/remind_email_template';

    /**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'baker/password/forgot_email_template';

    /**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'baker/password/forgot_email_identity';

    /**
     * @deprecated
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    const XML_PATH_IS_CONFIRM = 'baker/create_account/confirm';

    /**
     * @deprecated
     */
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'baker/create_account/email_confirmation_template';

    /**
     * @deprecated
     */
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'baker/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to baker password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'baker/password/minimum_password_length';

    /**
     * Configuration path to baker password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'baker/password/required_character_classes_number';

    /**
     * @deprecated
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'baker/password/reset_password_template';

    /**
     * @deprecated
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var BakerFactory
     */
    private $bakerFactory;

    /**
     * @var \CND\Baker\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var BakerMetadataInterface
     */
    private $bakerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var BakerRegistry
     */
    private $bakerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var BakerRepositoryInterface
     */
    private $bakerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var BakerViewHelper
     */
    protected $bakerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var BakerModel
     */
    protected $bakerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @param BakerFactory $bakerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param BakerMetadataInterface $bakerMetadataService
     * @param BakerRegistry $bakerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param BakerRepositoryInterface $bakerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param BakerViewHelper $bakerViewHelper
     * @param DateTime $dateTime
     * @param BakerModel $bakerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param DateTimeFactory $dateTimeFactory
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        BakerFactory $bakerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        BakerMetadataInterface $bakerMetadataService,
        BakerRegistry $bakerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        BakerRepositoryInterface $bakerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        BakerViewHelper $bakerViewHelper,
        DateTime $dateTime,
        BakerModel $bakerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null
    ) {
        $this->bakerFactory = $bakerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->bakerMetadataService = $bakerMetadataService;
        $this->bakerRegistry = $bakerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->bakerRepository = $bakerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->bakerViewHelper = $bakerViewHelper;
        $this->dateTime = $dateTime;
        $this->bakerModel = $bakerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->credentialsValidator =
            $credentialsValidator ?: ObjectManager::getInstance()->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->sessionManager = $sessionManager
            ?: ObjectManager::getInstance()->get(SessionManagerInterface::class);
        $this->saveHandler = $saveHandler
            ?: ObjectManager::getInstance()->get(SaveHandlerInterface::class);
        $this->visitorCollectionFactory = $visitorCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \CND\Baker\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '')
    {
        $baker = $this->bakerRepository->get($email, $websiteId);
        if (!$baker->getConfirmation()) {
            throw new InvalidTransitionException(__('No confirmation needed.'));
        }

        try {
            $this->getEmailNotification()->newAccount(
                $baker,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate($email, $confirmationKey)
    {
        $baker = $this->bakerRepository->get($email);
        return $this->activateBaker($baker, $confirmationKey);
    }

    /**
     * {@inheritdoc}
     */
    public function activateById($bakerId, $confirmationKey)
    {
        $baker = $this->bakerRepository->getById($bakerId);
        return $this->activateBaker($baker, $confirmationKey);
    }

    /**
     * Activate a baker account using a key that was sent in a confirmation email.
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @param string $confirmationKey
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function activateBaker($baker, $confirmationKey)
    {
        // check if baker is inactive
        if (!$baker->getConfirmation()) {
            throw new InvalidTransitionException(__('Account already active'));
        }

        if ($baker->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException(__('Invalid confirmation token'));
        }

        $baker->setConfirmation(null);
        $this->bakerRepository->save($baker);
        $this->getEmailNotification()->newAccount($baker, 'confirmed', '', $this->storeManager->getStore()->getId());
        return $baker;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        try {
            $baker = $this->bakerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $bakerId = $baker->getId();
        if ($this->getAuthentication()->isLocked($bakerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($bakerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($baker->getConfirmation() && $this->isConfirmationRequired($baker)) {
            throw new EmailNotConfirmedException(__('This account is not confirmed.'));
        }

        $bakerModel = $this->bakerFactory->create()->updateData($baker);
        $this->eventManager->dispatch(
            'baker_baker_authenticated',
            ['model' => $bakerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('baker_data_object_login', ['baker' => $baker]);

        return $baker;
    }

    /**
     * {@inheritdoc}
     */
    public function validateResetPasswordLinkToken($bakerId, $resetPasswordLinkToken)
    {
        $this->validateResetPasswordToken($bakerId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load baker by email
        $baker = $this->bakerRepository->get($email, $websiteId);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($baker, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($baker);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->getEmailNotification()->passwordResetConfirmation($baker);
                    break;
                default:
                    throw new InputException(__(
                        'Invalid value of "%value" provided for the %fieldName field. '.
                        'Possible values: %template1 or %template2.',
                        [
                            'value' => $template,
                            'fieldName' => 'template',
                            'template1' => AccountManagement::EMAIL_REMINDER,
                            'template2' => AccountManagement::EMAIL_RESET
                        ]
                    ));
            }

            return true;
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        $baker = $this->bakerRepository->get($email);
        //Validate Token and new password strength
        $this->validateResetPasswordToken($baker->getId(), $resetToken);
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($baker->getId());
        $bakerSecure->setRpToken(null);
        $bakerSecure->setRpTokenCreatedAt(null);
        $bakerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->sessionManager->destroy();
        $this->destroyBakerSessions($baker->getId());
        $this->bakerRepository->save($baker);

        return true;
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'Please enter a password with at least %1 characters.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(__('The password can\'t begin or end with a space.'));
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($bakerId)
    {
        // load baker by id
        $baker = $this->bakerRepository->getById($bakerId);
        if ($this->isConfirmationRequired($baker)) {
            if (!$baker->getConfirmation()) {
                return self::ACCOUNT_CONFIRMED;
            }
            return self::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccount(BakerInterface $baker, $password = null, $redirectUrl = '')
    {
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $bakerEmail = $baker->getEmail();
            try {
                $this->credentialsValidator->checkPasswordDifferentFromEmail($bakerEmail, $password);
            } catch (InputException $e) {
                throw new LocalizedException(__('Password cannot be the same as email address.'));
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($baker, $hash, $redirectUrl);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(BakerInterface $baker, $hash, $redirectUrl = '')
    {
        // This logic allows an existing baker to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($baker->getId()) {
            $baker = $this->bakerRepository->get($baker->getEmail());
            $websiteId = $baker->getWebsiteId();

            if ($this->isBakerInStore($websiteId, $baker->getStoreId())) {
                throw new InputException(__('This baker already exists in this store.'));
            }
            // Existing password hash will be used from secured baker data registry when saving baker
        }

        // Make sure we have a storeId to associate this baker with.
        if (!$baker->getStoreId()) {
            if ($baker->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($baker->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $baker->setStoreId($storeId);
        }

        // Associate website_id with baker
        if (!$baker->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($baker->getStoreId())->getWebsiteId();
            $baker->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($baker->getId() === null) {
            $storeName = $this->storeManager->getStore($baker->getStoreId())->getName();
            $baker->setCreatedIn($storeName);
        }

        $bakerAddresses = $baker->getAddresses() ?: [];
        $baker->setAddresses(null);
        try {
            // If baker exists existing hash will be used by Repository
            $baker = $this->bakerRepository->save($baker, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A baker with the same email already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($bakerAddresses as $address) {
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setBakerId($baker->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setBakerId($baker->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->bakerRegistry->remove($baker->getId());
        } catch (InputException $e) {
            $this->bakerRepository->delete($baker);
            throw $e;
        }
        $baker = $this->bakerRepository->getById($baker->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($baker, $newLinkToken);
        $this->sendEmailConfirmation($baker, $redirectUrl);

        return $baker;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBillingAddress($bakerId)
    {
        $baker = $this->bakerRepository->getById($bakerId);
        return $this->getAddressById($baker, $baker->getDefaultBilling());
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShippingAddress($bakerId)
    {
        $baker = $this->bakerRepository->getById($bakerId);
        return $this->getAddressById($baker, $baker->getDefaultShipping());
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param BakerInterface $baker
     * @param string $redirectUrl
     * @return void
     */
    protected function sendEmailConfirmation(BakerInterface $baker, $redirectUrl)
    {
        try {
            $hash = $this->bakerRegistry->retrieveSecureData($baker->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($baker) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($baker, $templateType, $redirectUrl, $baker->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        try {
            $baker = $this->bakerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForBaker($baker, $currentPassword, $newPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function changePasswordById($bakerId, $currentPassword, $newPassword)
    {
        try {
            $baker = $this->bakerRepository->getById($bakerId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForBaker($baker, $currentPassword, $newPassword);
    }

    /**
     * Change baker password
     *
     * @param BakerInterface $baker
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function changePasswordForBaker($baker, $currentPassword, $newPassword)
    {
        try {
            $this->getAuthentication()->authenticate($baker->getId(), $currentPassword);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
        }
        $bakerEmail = $baker->getEmail();
        $this->credentialsValidator->checkPasswordDifferentFromEmail($bakerEmail, $newPassword);
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($baker->getId());
        $bakerSecure->setRpToken(null);
        $bakerSecure->setRpTokenCreatedAt(null);
        $this->checkPasswordStrength($newPassword);
        $bakerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->destroyBakerSessions($baker->getId());
        $this->bakerRepository->save($baker);

        return true;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @return Backend
     */
    private function getEavValidator()
    {
        if ($this->eavValidator === null) {
            $this->eavValidator = ObjectManager::getInstance()->get(Backend::class);
        }
        return $this->eavValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(BakerInterface $baker)
    {
        $validationResults = $this->validationResultsDataFactory->create();

        $oldAddresses = $baker->getAddresses();
        $bakerModel = $this->bakerFactory->create()->updateData(
            $baker->setAddresses([])
        );
        $baker->setAddresses($oldAddresses);

        $result = $this->getEavValidator()->isValid($bakerModel);
        if ($result === false && is_array($this->getEavValidator()->getMessages())) {
            return $validationResults->setIsValid(false)->setMessages(
                call_user_func_array(
                    'array_merge',
                    $this->getEavValidator()->getMessages()
                )
            );
        }
        return $validationResults->setIsValid(true)->setMessages([]);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($bakerEmail, $websiteId = null)
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->bakerRepository->get($bakerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isBakerInStore($bakerWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($bakerWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Validate the Reset Password Token for a baker.
     *
     * @param int $bakerId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or baker id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If baker doesn't exist
     */
    private function validateResetPasswordToken($bakerId, $resetPasswordLinkToken)
    {
        if (empty($bakerId) || $bakerId < 0) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $bakerId, 'fieldName' => 'bakerId']
                )
            );
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('%fieldName is a required field.', $params));
        }

        $bakerSecureData = $this->bakerRegistry->retrieveSecureData($bakerId);
        $rpToken = $bakerSecureData->getRpToken();
        $rpTokenCreatedAt = $bakerSecureData->getRpTokenCreatedAt();

        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('Reset password token mismatch.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('Reset password token expired.'));
        }

        return true;
    }

    /**
     * Check if baker can be deleted.
     *
     * @param int $bakerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly($bakerId)
    {
        $baker = $this->bakerRegistry->retrieveSecureData($bakerId);
        return !$baker->getDeleteable();
    }

    /**
     * Send email with new account related information
     *
     * @param BakerInterface $baker
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     * @deprecated 100.1.0
     */
    protected function sendNewAccountEmail(
        $baker,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($baker->getStoreId());

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );

        return $this;
    }

    /**
     * Send email to baker when his password is reset
     *
     * @param BakerInterface $baker
     * @return $this
     * @deprecated 100.1.0
     */
    protected function sendPasswordResetNotificationEmail($baker)
    {
        return $this->sendPasswordResetConfirmationEmail($baker);
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param BakerInterface $baker
     * @param int|string|null $defaultStoreId
     * @return int
     * @deprecated 100.1.0
     */
    protected function getWebsiteStoreId($baker, $defaultStoreId = null)
    {
        if ($baker->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($baker->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * @return array
     * @deprecated 100.1.0
     */
    protected function getTemplateTypes()
    {
        /**
         * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
         *                                                  and password is not set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
         */
        $types = [
            self::NEW_ACCOUNT_EMAIL_REGISTERED             => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMED              => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMATION           => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
        ];
        return $types;
    }

    /**
     * Send corresponding email template
     *
     * @param BakerInterface $baker
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return $this
     * @deprecated 100.1.0
     */
    protected function sendEmailTemplate(
        $baker,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $baker->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars($templateParams)->setFrom(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId)
        )->addTo($email, $this->bakerViewHelper->getBakerName($baker))->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param BakerInterface $baker
     * @return bool
     * @deprecated
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function isConfirmationRequired($baker)
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $baker->getWebsiteId(),
            $baker->getId(),
            $baker->getEmail()
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param BakerInterface $baker
     * @return bool
     * @deprecated
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function canSkipConfirmation($baker)
    {
        if (!$baker->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($baker->getEmail());
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->bakerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param BakerInterface $baker
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     */
    public function changeResetPasswordLinkToken($baker, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $bakerSecure = $this->bakerRegistry->retrieveSecureData($baker->getId());
            $bakerSecure->setRpToken($passwordLinkToken);
            $bakerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->bakerRepository->save($baker);
        }
        return true;
    }

    /**
     * Send email with new baker password
     *
     * @param BakerInterface $baker
     * @return $this
     * @deprecated 100.1.0
     */
    public function sendPasswordReminderEmail($baker)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param BakerInterface $baker
     * @return $this
     * @deprecated 100.1.0
     */
    public function sendPasswordResetConfirmationEmail($baker)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Get address by id
     *
     * @param BakerInterface $baker
     * @param int $addressId
     * @return AddressInterface|null
     */
    protected function getAddressById(BakerInterface $baker, $addressId)
    {
        foreach ($baker->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * Create an object with data merged from Baker and BakerSecure
     *
     * @param BakerInterface $baker
     * @return Data\BakerSecure
     * @deprecated 100.1.0
     */
    protected function getFullBakerObject($baker)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedBakerData = $this->bakerRegistry->retrieveSecureData($baker->getId());
        $bakerData =
            $this->dataProcessor->buildOutputDataArray($baker, \CND\Baker\Api\Data\BakerInterface::class);
        $mergedBakerData->addData($bakerData);
        $mergedBakerData->setData('name', $this->bakerViewHelper->getBakerName($baker));
        return $mergedBakerData;
    }

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Destroy all active baker sessions by baker id (current session will not be destroyed).
     * Baker sessions which should be deleted are collecting  from the "baker_visitor" table considering
     * configured session lifetime.
     *
     * @param string|int $bakerId
     * @return void
     */
    private function destroyBakerSessions($bakerId)
    {
        $sessionLifetime = $this->scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $dateTime = $this->dateTimeFactory->create();
        $activeSessionsTime = $dateTime->setTimestamp($dateTime->getTimestamp() - $sessionLifetime)
            ->format(DateTime::DATETIME_PHP_FORMAT);
        /** @var \CND\Baker\Model\ResourceModel\Visitor\Collection $visitorCollection */
        $visitorCollection = $this->visitorCollectionFactory->create();
        $visitorCollection->addFieldToFilter('baker_id', $bakerId);
        $visitorCollection->addFieldToFilter('last_visit_at', ['from' => $activeSessionsTime]);
        $visitorCollection->addFieldToFilter('session_id', ['neq' => $this->sessionManager->getSessionId()]);
        /** @var \CND\Baker\Model\Visitor $visitor */
        foreach ($visitorCollection->getItems() as $visitor) {
            $sessionId = $visitor->getSessionId();
            $this->sessionManager->start();
            $this->saveHandler->destroy($sessionId);
            $this->sessionManager->writeClose();
        }
    }
}
