<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Controller\Account;

use CND\Baker\Controller\Account\Confirm;
use CND\Baker\Helper\Address;
use CND\Baker\Model\Url;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConfirmTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Confirm
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \CND\Baker\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAccountManagementMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepositoryMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerDataMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \CND\Baker\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultMock;

    protected function setUp()
    {
        $this->bakerSessionMock = $this->createMock(\CND\Baker\Model\Session::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->responseMock = $this->createPartialMock(\Magento\Framework\App\Response\Http::class, ['setRedirect', '__wakeup']);
        $viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $this->urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->bakerAccountManagementMock =
            $this->getMockForAbstractClass(\CND\Baker\Api\AccountManagementInterface::class);
        $this->bakerDataMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);

        $this->bakerRepositoryMock =
            $this->getMockForAbstractClass(\CND\Baker\Api\BakerRepositoryInterface::class);

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->addressHelperMock = $this->createMock(\CND\Baker\Helper\Address::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->redirectResultMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);

        $resultFactoryMock = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectResultMock);

        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($viewMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            \CND\Baker\Controller\Account\Confirm::class,
            [
                'context' => $this->contextMock,
                'bakerSession' => $this->bakerSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'bakerAccountManagement' => $this->bakerAccountManagementMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'addressHelper' => $this->addressHelperMock,
                'urlFactory' => $urlFactoryMock,
            ]
        );
    }

    public function testIsLoggedIn()
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->redirectResultMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->model->execute());
    }

    /**
     * @dataProvider getParametersDataProvider
     */
    public function testNoBakerIdInRequest($bakerId, $key)
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with($this->equalTo('id'), false)
            ->will($this->returnValue($bakerId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with($this->equalTo('key'), false)
            ->will($this->returnValue($key));

        $exception = new \Exception('Bad request.');
        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($this->equalTo($exception), $this->equalTo('There was an error confirming the account'));

        $testUrl = 'http://example.com';
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($testUrl));

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($this->equalTo($testUrl))
            ->will($this->returnValue($testUrl));

        $this->redirectResultMock->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo($testUrl))
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->model->execute());
    }

    /**
     * @return array
     */
    public function getParametersDataProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    /**
     * @param $bakerId
     * @param $key
     * @param $vatValidationEnabled
     * @param $addressType
     * @param $successMessage
     *
     * @dataProvider getSuccessMessageDataProvider
     */
    public function testSuccessMessage($bakerId, $key, $vatValidationEnabled, $addressType, $successMessage)
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $bakerId],
                ['key', false, $key],
            ]);

        $this->bakerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($bakerId)
            ->will($this->returnValue($this->bakerDataMock));

        $email = 'test@example.com';
        $this->bakerDataMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));

        $this->bakerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($this->equalTo($email), $this->equalTo($key))
            ->will($this->returnValue($this->bakerDataMock));

        $this->bakerSessionMock->expects($this->any())
            ->method('setBakerDataAsLoggedIn')
            ->with($this->equalTo($this->bakerDataMock))
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->addressHelperMock->expects($this->once())
            ->method('isVatValidationEnabled')
            ->will($this->returnValue($vatValidationEnabled));
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($addressType));

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $cookieMetadataManager = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $cookieMetadataFactory = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadata = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataFactory->expects($this->once())
            ->method('createCookieMetadata')
            ->willReturn($cookieMetadata);
        $cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with('/');
        $cookieMetadataManager->expects($this->once())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $cookieMetadata);

        $refClass = new \ReflectionClass(Confirm::class);
        $cookieMetadataManagerProperty = $refClass->getProperty('cookieMetadataManager');
        $cookieMetadataManagerProperty->setAccessible(true);
        $cookieMetadataManagerProperty->setValue($this->model, $cookieMetadataManager);

        $cookieMetadataFactoryProperty = $refClass->getProperty('cookieMetadataFactory');
        $cookieMetadataFactoryProperty->setAccessible(true);
        $cookieMetadataFactoryProperty->setValue($this->model, $cookieMetadataFactory);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider()
    {
        return [
            [1, 1, false, null, __('Thank you for registering with')],
            [1, 1, true, Address::TYPE_BILLING, __('enter your billing address for proper VAT calculation')],
            [1, 1, true, Address::TYPE_SHIPPING, __('enter your shipping address for proper VAT calculation')],
        ];
    }

    /**
     * @param $bakerId
     * @param $key
     * @param $backUrl
     * @param $successUrl
     * @param $resultUrl
     * @param $isSetFlag
     * @param $successMessage
     *
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $bakerId,
        $key,
        $backUrl,
        $successUrl,
        $resultUrl,
        $isSetFlag,
        $successMessage
    ) {
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $bakerId],
                ['key', false, $key],
                ['back_url', false, $backUrl],
            ]);

        $this->bakerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($bakerId)
            ->will($this->returnValue($this->bakerDataMock));

        $email = 'test@example.com';
        $this->bakerDataMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));

        $this->bakerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($this->equalTo($email), $this->equalTo($key))
            ->will($this->returnValue($this->bakerDataMock));

        $this->bakerSessionMock->expects($this->any())
            ->method('setBakerDataAsLoggedIn')
            ->with($this->equalTo($this->bakerDataMock))
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($successUrl));

        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($this->equalTo($resultUrl))
            ->willReturn($resultUrl);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);

        $cookieMetadataManager = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(false);

        $refClass = new \ReflectionClass(Confirm::class);
        $refProperty = $refClass->getProperty('cookieMetadataManager');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $cookieMetadataManager);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessRedirectDataProvider()
    {
        return [
            [
                1,
                1,
                'http://example.com/back',
                null,
                'http://example.com/back',
                true,
                __('Thank you for registering with'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                true,
                __('Thank you for registering with'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                false,
                __('Thank you for registering with'),
            ],
        ];
    }
}
