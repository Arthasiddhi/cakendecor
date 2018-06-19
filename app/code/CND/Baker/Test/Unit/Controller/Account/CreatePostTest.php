<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Controller\Account;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Helper\Address;
use CND\Baker\Model\Url;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Controller\Account\CreatePost
     */
    protected $model;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \CND\Baker\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerUrl;

    /**
     * @var \CND\Baker\Model\Registration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registration;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepository;

    /**
     * @var \CND\Baker\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \CND\Baker\Model\BakerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerExtractorMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerDetailsMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerDetailsFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \CND\Baker\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * This test can be unskipped when the Unit test object manager helper is enabled to return correct DataBuilders
         * For now the \CND\Baker\Test\Unit\Controller\AccountTest sufficiently covers the SUT
         */
        $this->markTestSkipped('Cannot be unit tested with the auto generated builder dependencies');
        $this->bakerSessionMock = $this->createMock(\CND\Baker\Model\Session::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\Webapi\Response::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->bakerMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $this->bakerDetailsMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $this->bakerDetailsFactoryMock = $this->createMock(\CND\Baker\Api\Data\BakerInterfaceFactory::class);

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->bakerRepository = $this->createMock(\CND\Baker\Api\BakerRepositoryInterface::class);
        $this->accountManagement = $this->createMock(\CND\Baker\Api\AccountManagementInterface::class);
        $this->addressHelperMock = $this->createMock(\CND\Baker\Helper\Address::class);
        $formFactoryMock = $this->createMock(\CND\Baker\Model\Metadata\FormFactory::class);

        $this->subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $subscriberFactoryMock = $this->createPartialMock(\Magento\Newsletter\Model\SubscriberFactory::class, ['create']);
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->subscriberMock));

        $regionFactoryMock = $this->createMock(\CND\Baker\Api\Data\RegionInterfaceFactory::class);
        $addressFactoryMock = $this->createMock(\CND\Baker\Api\Data\AddressInterfaceFactory::class);
        $this->bakerUrl = $this->createMock(\CND\Baker\Model\Url::class);
        $this->registration = $this->createMock(\CND\Baker\Model\Registration::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->bakerExtractorMock = $this->createMock(\CND\Baker\Model\BakerExtractor::class);
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->model = $objectManager->getObject(
            \CND\Baker\Controller\Account\CreatePost::class,
            [
                'context' => $contextMock,
                'bakerSession' => $this->bakerSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'accountManagement' => $this->accountManagement,
                'addressHelper' => $this->addressHelperMock,
                'urlFactory' => $urlFactoryMock,
                'formFactory' => $formFactoryMock,
                'subscriberFactory' => $subscriberFactoryMock,
                'regionDataFactory' => $regionFactoryMock,
                'addressDataFactory' => $addressFactoryMock,
                'bakerDetailsFactory' => $this->bakerDetailsFactoryMock,
                'bakerUrl' => $this->bakerUrl,
                'registration' => $this->registration,
                'escape' => $escaperMock,
                'bakerExtractor' => $this->bakerExtractorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreatePostActionRegistrationDisabled()
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/', [])
            ->will($this->returnValue(false));

        $this->bakerRepository->expects($this->never())
            ->method('save');

        $this->model->execute();
    }

    public function testRegenerateIdOnExecution()
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('regenerateId');
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->bakerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->bakerMock);
        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->willReturn($this->bakerMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->model->execute();
    }

    /**
     * @param $bakerId
     * @param $bakerEmail
     * @param $password
     * @param $confirmationStatus
     * @param $vatValidationEnabled
     * @param $addressType
     * @param $successMessage
     *
     * @dataProvider getSuccessMessageDataProvider
     */
    public function testSuccessMessage(
        $bakerId,
        $bakerEmail,
        $password,
        $confirmationStatus,
        $vatValidationEnabled,
        $addressType,
        $successMessage
    ) {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $this->bakerUrl->expects($this->once())
            ->method('getEmailConfirmationUrl')
            ->will($this->returnValue($bakerEmail));

        $this->bakerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->bakerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($bakerId));
        $this->bakerMock->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue($bakerEmail));

        $this->bakerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('baker_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->bakerMock));

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['password', null, $password],
                ['password_confirmation', null, $password],
                ['is_subscribed', false, true],
            ]);

        $this->bakerMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->bakerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->bakerMock));
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($bakerId))
            ->will($this->returnValue($confirmationStatus));

        $this->subscriberMock->expects($this->once())
            ->method('subscribeBakerById')
            ->with($this->equalTo($bakerId));

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->addressHelperMock->expects($this->any())
            ->method('isVatValidationEnabled')
            ->will($this->returnValue($vatValidationEnabled));
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($addressType));

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider()
    {
        return [
            [
                1,
                'baker@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'An account confirmation is required',
            ],
            [
                1,
                'baker@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'Thank you for registering with',
            ],
            [
                1,
                'baker@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                true,
                Address::TYPE_SHIPPING,
                'enter you shipping address for proper VAT calculation',
            ],
            [
                1,
                'baker@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                true,
                Address::TYPE_BILLING,
                'enter you billing address for proper VAT calculation',
            ],
        ];
    }

    /**
     * @param $bakerId
     * @param $password
     * @param $confirmationStatus
     * @param $successUrl
     * @param $isSetFlag
     * @param $successMessage
     *
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $bakerId,
        $password,
        $confirmationStatus,
        $successUrl,
        $isSetFlag,
        $successMessage
    ) {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->bakerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->bakerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($bakerId));

        $this->bakerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('baker_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->bakerMock));

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['password', null, $password],
                ['password_confirmation', null, $password],
                ['is_subscribed', false, true],
            ]);

        $this->bakerMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->bakerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->bakerMock));
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($bakerId))
            ->will($this->returnValue($confirmationStatus));

        $this->subscriberMock->expects($this->once())
            ->method('subscribeBakerById')
            ->with($this->equalTo($bakerId));

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap([
                ['*/*/index', ['_secure' => true], $successUrl],
                ['*/*/create', ['_secure' => true], $successUrl],
            ]);
        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($this->equalTo($successUrl))
            ->will($this->returnValue($successUrl));
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD),
                $this->equalTo(ScopeInterface::SCOPE_STORE)
            )
            ->will($this->returnValue($isSetFlag));
        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->model->execute();
    }

    public function getSuccessRedirectDataProvider()
    {
        return [
            [
                1,
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                true,
                'Thank you for registering with',
            ],
            [
                1,
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                false,
                'Thank you for registering with',
            ],
        ];
    }
}
