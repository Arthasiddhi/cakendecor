<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Model\AccountManagement;
use CND\Baker\Model\AccountConfirmation;
use CND\Baker\Model\AuthenticationInterface;
use CND\Baker\Model\EmailNotificationInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountManagement */
    protected $accountManagement;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \CND\Baker\Model\BakerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerFactory;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $random;

    /** @var \CND\Baker\Model\Metadata\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator;

    /** @var \CND\Baker\Api\Data\ValidationResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationResultsInterfaceFactory;

    /** @var \CND\Baker\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressRepository;

    /** @var \CND\Baker\Api\BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerMetadata;

    /** @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerRegistry;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptor;

    /** @var \CND\Baker\Model\Config\Share|\PHPUnit_Framework_MockObject_MockObject */
    protected $share;

    /** @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject */
    protected $string;

    /** @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerRepository;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportBuilder;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectProcessor;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \CND\Baker\Helper\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerViewHelper;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;

    /** @var \CND\Baker\Model\Baker|\PHPUnit_Framework_MockObject_MockObject */
    protected $baker;

    /** @var \Magento\Framework\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectFactory;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleDataObjectConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Model\Data\BakerSecure
     */
    protected $bakerSecure;

    /**
     * @var AuthenticationInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationMock;

    /**
     * @var EmailNotificationInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotificationMock;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $accountConfirmation;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Model\ResourceModel\Visitor\CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Session\SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->bakerFactory = $this->createPartialMock(\CND\Baker\Model\BakerFactory::class, ['create']);
        $this->manager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->random = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->validator = $this->createMock(\CND\Baker\Model\Metadata\Validator::class);
        $this->validationResultsInterfaceFactory = $this->createMock(
            \CND\Baker\Api\Data\ValidationResultsInterfaceFactory::class
        );
        $this->addressRepository = $this->createMock(\CND\Baker\Api\AddressRepositoryInterface::class);
        $this->bakerMetadata = $this->createMock(\CND\Baker\Api\BakerMetadataInterface::class);
        $this->bakerRegistry = $this->createMock(\CND\Baker\Model\BakerRegistry::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->encryptor = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->share = $this->createMock(\CND\Baker\Model\Config\Share::class);
        $this->string = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);
        $this->bakerRepository = $this->createMock(\CND\Baker\Api\BakerRepositoryInterface::class);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transportBuilder = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);
        $this->dataObjectProcessor = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->bakerViewHelper = $this->createMock(\CND\Baker\Helper\View::class);
        $this->dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->baker = $this->createMock(\CND\Baker\Model\Baker::class);
        $this->objectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->extensibleDataObjectConverter = $this->createMock(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailNotificationMock = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->setMethods(['setRpToken', 'addData', 'setRpTokenCreatedAt', 'setData', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->visitorCollectionFactory = $this->getMockBuilder(
            \CND\Baker\Model\ResourceModel\Visitor\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sessionManager = $this->getMockBuilder(\Magento\Framework\Session\SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saveHandler = $this->getMockBuilder(\Magento\Framework\Session\SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            \CND\Baker\Model\AccountManagement::class,
            [
                'bakerFactory' => $this->bakerFactory,
                'eventManager' => $this->manager,
                'storeManager' => $this->storeManager,
                'mathRandom' => $this->random,
                'validator' => $this->validator,
                'validationResultsDataFactory' => $this->validationResultsInterfaceFactory,
                'addressRepository' => $this->addressRepository,
                'bakerMetadataService' => $this->bakerMetadata,
                'bakerRegistry' => $this->bakerRegistry,
                'logger' => $this->logger,
                'encryptor' => $this->encryptor,
                'configShare' => $this->share,
                'stringHelper' => $this->string,
                'bakerRepository' => $this->bakerRepository,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'dataProcessor' => $this->dataObjectProcessor,
                'registry' => $this->registry,
                'bakerViewHelper' => $this->bakerViewHelper,
                'dateTime' => $this->dateTime,
                'bakerModel' => $this->baker,
                'objectFactory' => $this->objectFactory,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
                'dateTimeFactory' => $this->dateTimeFactory,
                'accountConfirmation' => $this->accountConfirmation,
                'sessionManager' => $this->sessionManager,
                'saveHandler' => $this->saveHandler,
                'visitorCollectionFactory' => $this->visitorCollectionFactory,
            ]
        );
        $reflection = new \ReflectionClass(get_class($this->accountManagement));
        $reflectionProperty = $reflection->getProperty('authentication');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->accountManagement, $this->authenticationMock);
        $reflectionProperty = $reflection->getProperty('emailNotification');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->accountManagement, $this->emailNotificationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateAccountWithPasswordHashWithExistingBaker()
    {
        $websiteId = 1;
        $storeId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->accountManagement->createAccountWithPasswordHash($baker, $hash);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testCreateAccountWithPasswordHashWithBakerWithoutStoreId()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $exception = new \Magento\Framework\Exception\AlreadyExistsException(
            new \Magento\Framework\Phrase('Exception message')
        );
        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($baker, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($baker, $hash);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateAccountWithPasswordHashWithLocalizedException()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $exception = new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('Exception message')
        );
        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($baker, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($baker, $hash);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateAccountWithPasswordHashWithAddressException()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($baker, $hash)
            ->willReturn($baker);
        $exception = new \Magento\Framework\Exception\InputException(
            new \Magento\Framework\Phrase('Exception message')
        );
        $this->addressRepository
            ->expects($this->atLeastOnce())
            ->method('save')
            ->with($address)
            ->willThrowException($exception);
        $this->bakerRepository
            ->expects($this->once())
            ->method('delete')
            ->with($baker);

        $this->accountManagement->createAccountWithPasswordHash($baker, $hash);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateAccountWithPasswordHashWithNewBakerAndLocalizedException()
    {
        $storeId = 1;
        $storeName = 'store_name';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $bakerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $bakerMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $bakerMock->expects($this->once())
            ->method('setCreatedIn')
            ->with($storeName)
            ->willReturnSelf();
        $bakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $bakerMock->expects($this->once())
            ->method('setAddresses')
            ->with(null)
            ->willReturnSelf();

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getName')
            ->willReturn($storeName);

        $this->storeManager->expects($this->exactly(2))
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $exception = new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('Exception message')
        );
        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($bakerMock, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($bakerMock, $hash);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithoutPassword()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';

        $datetime = $this->prepareDateTimeFactory();

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->bakerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($baker);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($baker);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->setMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $bakerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $bakerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn(null);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($bakerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willReturnSelf();

        $this->accountManagement->createAccount($baker);
    }

    /**
     * Data provider for testCreateAccountWithPasswordInputException test
     *
     * @return array
     */
    public function dataProviderCheckPasswordStrength()
    {
        return [
            [
                'testNumber' => 1,
                'password' => 'qwer',
                'minPasswordLength' => 5,
                'minCharacterSetsNum' => 1
            ],
            [
                'testNumber' => 2,
                'password' => 'wrfewqedf1',
                'minPasswordLength' => 5,
                'minCharacterSetsNum' => 3
            ]
        ];
    }

    /**
     * @param int $testNumber
     * @param string $password
     * @param int $minPasswordLength
     * @param int $minCharacterSetsNum
     * @dataProvider dataProviderCheckPasswordStrength
     */
    public function testCreateAccountWithPasswordInputException(
        $testNumber,
        $password,
        $minPasswordLength,
        $minCharacterSetsNum
    ) {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                            'default',
                            null,
                            $minPasswordLength,
                        ],
                        [
                            AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                            'default',
                            null,
                            $minCharacterSetsNum],
                    ]
                )
            );

        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));

        if ($testNumber == 1) {
            $this->expectException(
                \Magento\Framework\Exception\InputException::class,
                'Please enter a password with at least ' . $minPasswordLength . ' characters.'
            );
        }

        if ($testNumber == 2) {
            $this->expectException(
                \Magento\Framework\Exception\InputException::class,
                'Minimum of different classes of characters in password is ' . $minCharacterSetsNum .
                '. Classes of characters: Lower Case, Upper Case, Digits, Special Characters.'
            );
        }

        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $this->accountManagement->createAccount($baker, $password);
    }

    public function testCreateAccountInputExceptionExtraLongPassword()
    {
        $password = '257*chars*************************************************************************************'
            . '****************************************************************************************************'
            . '***************************************************************';

        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));

        $this->expectException(
            \Magento\Framework\Exception\InputException::class,
            'Please enter a password with at most 256 characters.'
        );

        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $this->accountManagement->createAccount($baker, $password);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithPassword()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $password = 'wrfewqedf1';
        $minPasswordLength = 5;
        $minCharacterSetsNum = 2;

        $datetime = $this->prepareDateTimeFactory();

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        $minPasswordLength,
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        $minCharacterSetsNum],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                        ScopeInterface::SCOPE_STORE,
                        $defaultStoreId,
                        $templateIdentifier,
                    ],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        1,
                        $sender
                    ]
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));
        $this->encryptor->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($hash);
        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->bakerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($baker);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($baker);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->setMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $bakerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $bakerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($hash);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($bakerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willReturnSelf();

        $this->accountManagement->createAccount($baker, $password);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPasswordReminderEmail()
    {
        $bakerId = 1;
        $bakerStoreId = 2;
        $bakerEmail = 'email@email.com';
        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $baker->expects($this->any())
            ->method('getStoreId')
            ->willReturn($bakerStoreId);
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($bakerStoreId);

        $this->storeManager->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->store);

        $this->storeManager->expects($this->at(1))
            ->method('getStore')
            ->with($bakerStoreId)
            ->willReturn($this->store);

        $this->bakerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecure);

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($baker, \CND\Baker\Api\Data\BakerInterface::class)
            ->willReturn($bakerData);

        $this->bakerViewHelper->expects($this->any())
            ->method('getBakerName')
            ->with($baker)
            ->willReturn($bakerName);

        $this->bakerSecure->expects($this->once())
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $this->bakerSecure->expects($this->once())
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfig->expects($this->at(1))
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($sender);

        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->getMock();

        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $bakerStoreId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with(['baker' => $this->bakerSecure, 'store' => $this->store])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($bakerEmail, $bakerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->assertEquals($this->accountManagement, $this->accountManagement->sendPasswordReminderEmail($baker));
    }

    /**
     * @param string $email
     * @param string $templateIdentifier
     * @param string $sender
     * @param int $storeId
     * @param int $bakerId
     * @param string $hash
     */
    protected function prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $bakerId, $hash)
    {
        $websiteId = 1;

        $datetime = $this->prepareDateTimeFactory();

        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';

        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $baker->expects($this->any())
            ->method('getEmail')
            ->willReturn($email);
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->bakerRepository->expects($this->once())
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($baker);
        $this->bakerRepository->expects($this->once())
            ->method('save')
            ->with($baker)
            ->willReturnSelf();

        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($hash);

        $this->bakerViewHelper->expects($this->any())
            ->method('getBakerName')
            ->with($baker)
            ->willReturn($bakerName);

        $this->bakerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($hash)
            ->willReturnSelf();
        $this->bakerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $this->bakerSecure->expects($this->any())
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $this->bakerSecure->expects($this->any())
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        $this->bakerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecure);

        $this->dataObjectProcessor->expects($this->any())
            ->method('buildOutputDataArray')
            ->with($baker, \CND\Baker\Api\Data\BakerInterface::class)
            ->willReturn($bakerData);

        $this->prepareEmailSend($email, $templateIdentifier, $sender, $storeId, $bakerName);
    }

    /**
     * @param $email
     * @param $templateIdentifier
     * @param $sender
     * @param $storeId
     * @param $bakerName
     */
    protected function prepareEmailSend($email, $templateIdentifier, $sender, $storeId, $bakerName)
    {
        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->getMock();

        $this->transportBuilder->expects($this->any())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setTemplateVars')
            ->with(['baker' => $this->bakerSecure, 'store' => $this->store])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('addTo')
            ->with($email, $bakerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->any())
            ->method('sendMessage');
    }

    public function testInitiatePasswordResetEmailReminder()
    {
        $bakerId = 1;

        $email = 'test@example.com';
        $template = AccountManagement::EMAIL_REMINDER;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $storeId = 1;

        mt_srand(mt_rand() + (100000000 * (float)microtime()) % PHP_INT_MAX);
        $hash = md5(uniqid(microtime() . mt_rand(0, mt_getrandmax()), true));

        $this->emailNotificationMock->expects($this->once())
            ->method('passwordReminder')
            ->willReturnSelf();

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $bakerId, $hash);

        $this->assertTrue($this->accountManagement->initiatePasswordReset($email, $template));
    }

    public function testInitiatePasswordResetEmailReset()
    {
        $storeId = 1;
        $bakerId = 1;

        $email = 'test@example.com';
        $template = AccountManagement::EMAIL_RESET;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        mt_srand(mt_rand() + (100000000 * (float)microtime()) % PHP_INT_MAX);
        $hash = md5(uniqid(microtime() . mt_rand(0, mt_getrandmax()), true));

        $this->emailNotificationMock->expects($this->once())
            ->method('passwordResetConfirmation')
            ->willReturnSelf();

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $bakerId, $hash);

        $this->assertTrue($this->accountManagement->initiatePasswordReset($email, $template));
    }

    public function testInitiatePasswordResetNoTemplate()
    {
        $storeId = 1;
        $bakerId = 1;

        $email = 'test@example.com';
        $template = null;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        mt_srand(mt_rand() + (100000000 * (float)microtime()) % PHP_INT_MAX);
        $hash = md5(uniqid(microtime() . mt_rand(0, mt_getrandmax()), true));

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $bakerId, $hash);

        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage(
            'Invalid value of "" provided for the template field. Possible values: email_reminder or email_reset.'
        );
        $this->accountManagement->initiatePasswordReset($email, $template);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the bakerId field
     */
    public function testValidateResetPasswordTokenBadBakerId()
    {
        $this->accountManagement->validateResetPasswordLinkToken(null, '');
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage resetPasswordLinkToken is a required field
     */
    public function testValidateResetPasswordTokenBadResetPasswordLinkToken()
    {
        $this->accountManagement->validateResetPasswordLinkToken(22, null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     * @expectedExceptionMessage Reset password token mismatch
     */
    public function testValidateResetPasswordTokenTokenMismatch()
    {
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->bakerSecure);

        $this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken');
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     * @expectedExceptionMessage Reset password token expired
     */
    public function testValidateResetPasswordTokenTokenExpired()
    {
        $this->reInitModel();
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->bakerSecure);

        $this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken');
    }

    /**
     * return bool
     */
    public function testValidateResetPasswordToken()
    {
        $this->reInitModel();

        $this->baker
            ->expects($this->once())
            ->method('getResetPasswordLinkExpirationPeriod')
            ->willReturn(100000);

        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->bakerSecure);

        $this->assertTrue($this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken'));
    }

    /**
     * reInit $this->accountManagement object
     */
    private function reInitModel()
    {
        $this->bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'setPasswordHash',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                ]
            )
            ->getMock();

        $this->bakerSecure
            ->expects($this->any())
            ->method('getRpToken')
            ->willReturn('newStringToken');

        $pastDateTime = '2016-10-25 00:00:00';

        $this->bakerSecure
            ->expects($this->any())
            ->method('getRpTokenCreatedAt')
            ->willReturn($pastDateTime);

        $this->baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResetPasswordLinkExpirationPeriod'])
            ->getMock();

        $this->prepareDateTimeFactory();

        $this->sessionManager = $this->getMockBuilder(\Magento\Framework\Session\SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['destroy', 'start', 'writeClose'])
            ->getMockForAbstractClass();
        $this->visitorCollectionFactory = $this->getMockBuilder(
            \CND\Baker\Model\ResourceModel\Visitor\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->saveHandler = $this->getMockBuilder(\Magento\Framework\Session\SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['destroy'])
            ->getMockForAbstractClass();

        $dateTime = '2017-10-25 18:57:08';
        $timestamp = '1508983028';
        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);

        $dateTimeMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $dateTimeMock
            ->expects($this->any())
            ->method('setTimestamp')
            ->willReturnSelf();

        $dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $dateTimeFactory->expects($this->any())->method('create')->willReturn($dateTimeMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            \CND\Baker\Model\AccountManagement::class,
            [
                'bakerFactory' => $this->bakerFactory,
                'bakerRegistry' => $this->bakerRegistry,
                'bakerRepository' => $this->bakerRepository,
                'bakerModel' => $this->baker,
                'dateTimeFactory' => $dateTimeFactory,
                'stringHelper' => $this->string,
                'scopeConfig' => $this->scopeConfig,
                'sessionManager' => $this->sessionManager,
                'visitorCollectionFactory' => $this->visitorCollectionFactory,
                'saveHandler' => $this->saveHandler,
                'encryptor' => $this->encryptor,
                'dataProcessor' => $this->dataObjectProcessor,
                'storeManager' => $this->storeManager,
                'transportBuilder' => $this->transportBuilder,
            ]
        );
        $reflection = new \ReflectionClass(get_class($this->accountManagement));
        $reflectionProperty = $reflection->getProperty('authentication');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->accountManagement, $this->authenticationMock);
    }

    /**
     * @return void
     */
    public function testChangePassword()
    {
        $bakerId = 7;
        $email = 'test@example.com';
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';
        $passwordHash = '1a2b3f4c';

        $this->reInitModel();
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);

        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($baker);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate');

        $this->bakerSecure->expects($this->once())
            ->method('setRpToken')
            ->with(null);
        $this->bakerSecure->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnSelf();
        $this->bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);

        $this->bakerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecure);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        7,
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        1
                    ],
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($newPassword)
            ->willReturn(7);

        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($baker);

        $this->sessionManager->expects($this->atLeastOnce())->method('start');
        $this->sessionManager->expects($this->atLeastOnce())->method('writeClose');
        $this->sessionManager->expects($this->atLeastOnce())->method('getSessionId');

        $visitor = $this->getMockBuilder(\CND\Baker\Model\Visitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId'])
            ->getMock();
        $visitor->expects($this->at(0))->method('getSessionId')->willReturn('session_id_1');
        $visitor->expects($this->at(1))->method('getSessionId')->willReturn('session_id_2');
        $visitorCollection = $this->getMockBuilder(
            \CND\Baker\Model\ResourceModel\Visitor\CollectionFactory::class
        )
            ->disableOriginalConstructor()->setMethods(['addFieldToFilter', 'getItems'])->getMock();
        $visitorCollection->expects($this->atLeastOnce())->method('addFieldToFilter')->willReturnSelf();
        $visitorCollection->expects($this->atLeastOnce())->method('getItems')->willReturn([$visitor, $visitor]);
        $this->visitorCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($visitorCollection);
        $this->saveHandler->expects($this->at(0))->method('destroy')->with('session_id_1');
        $this->saveHandler->expects($this->at(1))->method('destroy')->with('session_id_2');

        $this->assertTrue($this->accountManagement->changePassword($email, $currentPassword, $newPassword));
    }

    public function testResetPassword()
    {
        $bakerEmail = 'baker@example.com';
        $bakerId = '1';
        $resetToken = 'newStringToken';
        $newPassword = 'new_password';

        $this->reInitModel();
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker->expects($this->any())->method('getId')->willReturn($bakerId);
        $this->bakerRepository->expects($this->atLeastOnce())->method('get')->with($bakerEmail)
            ->willReturn($baker);
        $this->baker->expects($this->atLeastOnce())->method('getResetPasswordLinkExpirationPeriod')
            ->willReturn(100000);
        $this->string->expects($this->any())->method('strlen')->willReturnCallback(
            function ($string) {
                return strlen($string);
            }
        );
        $this->bakerRegistry->expects($this->atLeastOnce())->method('retrieveSecureData')
            ->willReturn($this->bakerSecure);

        $this->bakerSecure->expects($this->once())
            ->method('setRpToken')
            ->with(null);
        $this->bakerSecure->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null);
        $this->bakerSecure->expects($this->any())
            ->method('setPasswordHash')
            ->willReturn(null);

        $this->sessionManager->expects($this->atLeastOnce())->method('destroy');
        $this->sessionManager->expects($this->atLeastOnce())->method('start');
        $this->sessionManager->expects($this->atLeastOnce())->method('writeClose');
        $this->sessionManager->expects($this->atLeastOnce())->method('getSessionId');
        $visitor = $this->getMockBuilder(\CND\Baker\Model\Visitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId'])
            ->getMock();
        $visitor->expects($this->at(0))->method('getSessionId')->willReturn('session_id_1');
        $visitor->expects($this->at(1))->method('getSessionId')->willReturn('session_id_2');
        $visitorCollection = $this->getMockBuilder(
            \CND\Baker\Model\ResourceModel\Visitor\CollectionFactory::class
        )
            ->disableOriginalConstructor()->setMethods(['addFieldToFilter', 'getItems'])->getMock();
        $visitorCollection->expects($this->atLeastOnce())->method('addFieldToFilter')->willReturnSelf();
        $visitorCollection->expects($this->atLeastOnce())->method('getItems')->willReturn([$visitor, $visitor]);
        $this->visitorCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($visitorCollection);
        $this->saveHandler->expects($this->at(0))->method('destroy')->with('session_id_1');
        $this->saveHandler->expects($this->at(1))->method('destroy')->with('session_id_2');
        $this->assertTrue($this->accountManagement->resetPassword($bakerEmail, $resetToken, $newPassword));
    }

    /**
     * @return void
     */
    public function testChangePasswordException()
    {
        $email = 'test@example.com';
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';

        $exception = new NoSuchEntityException(
            new \Magento\Framework\Phrase('Exception message')
        );
        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($email)
            ->willThrowException($exception);

        $this->expectException(
            \Magento\Framework\Exception\InvalidEmailOrPasswordException::class,
            'Invalid login or password.'
        );

        $this->accountManagement->changePassword($email, $currentPassword, $newPassword);
    }

    /**
     * @return void
     */
    public function testAuthenticate()
    {
        $username = 'login';
        $password = '1234567';
        $passwordHash = '1a2b3f4c';

        $bakerData = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();

        $bakerModel = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerModel->expects($this->once())
            ->method('updateData')
            ->willReturn($bakerModel);

        $this->bakerRepository
            ->expects($this->once())
            ->method('get')
            ->with($username)
            ->willReturn($bakerData);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate');

        $bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->setMethods(['getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);

        $this->bakerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->willReturn($bakerSecure);

        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->willReturn($bakerModel);

        $this->manager->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    'baker_baker_authenticated',
                    ['model' => $bakerModel, 'password' => $password]
                ],
                [
                    'baker_data_object_login', ['baker' => $bakerData]
                ]
            );

        $this->assertEquals($bakerData, $this->accountManagement->authenticate($username, $password));
    }

    /**
     * @param int $isConfirmationRequired
     * @param string|null $confirmation
     * @param string $expected
     * @dataProvider dataProviderGetConfirmationStatus
     */
    public function testGetConfirmationStatus(
        $isConfirmationRequired,
        $confirmation,
        $expected
    ) {
        $websiteId = 1;
        $bakerId = 1;
        $bakerEmail = 'test1@example.com';

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerMock->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $bakerMock->expects($this->any())
            ->method('getConfirmation')
            ->willReturn($confirmation);
        $bakerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $bakerMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->accountConfirmation->expects($this->once())
            ->method('isConfirmationRequired')
            ->with($websiteId, $bakerId, $bakerEmail)
            ->willReturn($isConfirmationRequired);

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($bakerMock);

        $this->assertEquals($expected, $this->accountManagement->getConfirmationStatus($bakerId));
    }

    /**
     * @return array
     */
    public function dataProviderGetConfirmationStatus()
    {
        return [
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [1, null, AccountManagement::ACCOUNT_CONFIRMED],
            [1, 'test', AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateAccountWithPasswordHashForGuest()
    {
        $storeId = 1;
        $storeName = 'store_name';
        $websiteId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $storeMock->expects($this->once())
            ->method('getName')
            ->willReturn($storeName);

        $this->storeManager->expects($this->exactly(3))
            ->method('getStore')
            ->willReturn($storeMock);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();
        $bakerMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(null);
        $bakerMock->expects($this->exactly(3))
            ->method('getStoreId')
            ->willReturn(null);
        $bakerMock->expects($this->exactly(2))
            ->method('getWebsiteId')
            ->willReturn(null);
        $bakerMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $bakerMock->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId)
            ->willReturnSelf();
        $bakerMock->expects($this->once())
            ->method('setCreatedIn')
            ->with($storeName)
            ->willReturnSelf();
        $bakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $bakerMock->expects($this->once())
            ->method('setAddresses')
            ->with(null)
            ->willReturnSelf();

        $this->bakerRepository
            ->expects($this->once())
            ->method('save')
            ->with($bakerMock, $hash)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Exception message')));

        $this->accountManagement->createAccountWithPasswordHash($bakerMock, $hash);
    }

    public function testCreateAccountWithPasswordHashWithBakerAddresses()
    {
        $websiteId = 1;
        $addressId = 2;
        $bakerId = null;
        $storeId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $this->prepareDateTimeFactory();

        //Handle store
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        //Handle address - existing and non-existing. Non-Existing should return null when call getId method
        $existingAddress = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nonExistingAddress = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        //Ensure that existing address is not in use
        $this->addressRepository
            ->expects($this->atLeastOnce())
            ->method("save")
            ->withConsecutive(
                [$this->logicalNot($this->identicalTo($existingAddress))],
                [$this->identicalTo($nonExistingAddress)]
            );

        $existingAddress
            ->expects($this->any())
            ->method("getId")
            ->willReturn($addressId);
        //Expects that id for existing address should be unset
        $existingAddress
            ->expects($this->once())
            ->method("setId")
            ->with(null);
        //Handle Baker calls
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)->getMock();
        $baker
            ->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker
            ->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker
            ->expects($this->any())
            ->method("getId")
            ->willReturn($bakerId);
        //Return Baker from baker repository
        $this->bakerRepository
            ->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($baker);
        $this->bakerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($baker);
        $bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->setMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $bakerSecure->expects($this->once())
            ->method('setRpToken')
            ->with($hash);

        $bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($hash);

        $this->bakerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($bakerSecure);

        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($hash);

        $baker
            ->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn([$existingAddress, $nonExistingAddress]);

        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);

        $this->assertSame($baker, $this->accountManagement->createAccountWithPasswordHash($baker, $hash));
    }

    /**
     * @return string
     */
    private function prepareDateTimeFactory()
    {
        $dateTime = '2017-10-25 18:57:08';
        $timestamp = '1508983028';
        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);

        $dateTimeMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $this->dateTimeFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dateTimeMock);

        return $dateTime;
    }

    public function testCreateAccountUnexpectedValueException()
    {
        $websiteId = 1;
        $storeId = null;
        $defaultStoreId = 1;
        $bakerId = 1;
        $bakerEmail = 'email@email.com';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';
        $exception = new \UnexpectedValueException('Template file was not found');

        $datetime = $this->prepareDateTimeFactory();

        $address = $this->createMock(\CND\Baker\Api\Data\AddressInterface::class);
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $baker = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($bakerEmail);
        $baker->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $baker->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $baker->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker->expects($this->once())
            ->method('setAddresses')
            ->with(null);
        $this->bakerRepository->expects($this->once())
            ->method('get')
            ->with($bakerEmail)
            ->willReturn($baker);
        $this->share->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->bakerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($baker);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($baker);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $bakerSecure = $this->createPartialMock(
            \CND\Baker\Model\Data\BakerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $bakerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $bakerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $bakerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn(null);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($bakerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('error')->with($exception);

        $this->accountManagement->createAccount($baker);
    }
}
