<?php
/**
 * Unit test for CND\Baker\Test\Unit\Model\Account\Redirect
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Model\Account;

use CND\Baker\Model\Account\Redirect;
use CND\Baker\Model\Url as BakerUrl;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Url\HostChecker;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Redirect
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Session
     */
    protected $bakerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Url
     */
    protected $bakerUrl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Controller\Result\Redirect
     */
    protected $resultRedirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Controller\Result\Forward
     */
    protected $resultForward;

    /**
     * @var ResultFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var HostChecker | \PHPUnit_Framework_MockObject_MockObject
     */
    private $hostChecker;

    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);

        $this->bakerSession = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getLastBakerId',
                'isLoggedIn',
                'getId',
                'setLastBakerId',
                'unsBeforeAuthUrl',
                'getBeforeAuthUrl',
                'setBeforeAuthUrl',
                'getAfterAuthUrl',
                'setAfterAuthUrl',
                'getBeforeRequestParams',
                'getBeforeModuleName',
                'getBeforeControllerName',
                'getBeforeAction',
            ])
            ->getMock();

        $this->scopeConfig = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->url = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->urlDecoder = $this->getMockForAbstractClass(\Magento\Framework\Url\DecoderInterface::class);

        $this->bakerUrl = $this->getMockBuilder(\CND\Baker\Model\Url::class)
            ->setMethods(['DashboardUrl', 'getAccountUrl', 'getLoginUrl', 'getLogoutUrl', 'getDashboardUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultForward = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->hostChecker = $this->getMockBuilder(HostChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \CND\Baker\Model\Account\Redirect::class,
            [
                'request'               => $this->request,
                'bakerSession'       => $this->bakerSession,
                'scopeConfig'           => $this->scopeConfig,
                'storeManager'          => $this->storeManager,
                'url'                   => $this->url,
                'urlDecoder'            => $this->urlDecoder,
                'bakerUrl'           => $this->bakerUrl,
                'resultFactory'         => $this->resultFactory,
                'hostChecker' => $this->hostChecker
            ]
        );
    }

    /**
     * @param int $bakerId
     * @param int $lastBakerId
     * @param string $referer
     * @param string $baseUrl
     * @param string $beforeAuthUrl
     * @param string $afterAuthUrl
     * @param string $accountUrl
     * @param string $loginUrl
     * @param string $logoutUrl
     * @param string $dashboardUrl
     * @param bool $bakerLoggedIn
     * @param bool $redirectToDashboard
     * @dataProvider getRedirectDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetRedirect(
        $bakerId,
        $lastBakerId,
        $referer,
        $baseUrl,
        $beforeAuthUrl,
        $afterAuthUrl,
        $accountUrl,
        $loginUrl,
        $logoutUrl,
        $dashboardUrl,
        $bakerLoggedIn,
        $redirectToDashboard
    ) {
        // Preparations for method updateLastBakerId()
        $this->bakerSession->expects($this->once())
            ->method('getLastBakerId')
            ->willReturn($bakerId);
        $this->bakerSession->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($bakerLoggedIn);
        $this->bakerSession->expects($this->any())
            ->method('getId')
            ->willReturn($lastBakerId);
        $this->bakerSession->expects($this->any())
            ->method('unsBeforeAuthUrl')
            ->willReturnSelf();
        $this->bakerSession->expects($this->any())
            ->method('setLastBakerId')
            ->with($lastBakerId)
            ->willReturnSelf();

        // Preparations for method prepareRedirectUrl()
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->bakerSession->expects($this->any())
            ->method('getBeforeAuthUrl')
            ->willReturn($beforeAuthUrl);
        $this->bakerSession->expects($this->any())
            ->method('setBeforeAuthUrl')
            ->willReturnSelf();
        $this->bakerSession->expects($this->any())
            ->method('getAfterAuthUrl')
            ->willReturn($afterAuthUrl);
        $this->bakerSession->expects($this->any())
            ->method('setAfterAuthUrl')
            ->with($beforeAuthUrl)
            ->willReturnSelf();
        $this->bakerSession->expects($this->any())
            ->method('getBeforeRequestParams')
            ->willReturn(false);

        $this->bakerUrl->expects($this->any())
            ->method('getAccountUrl')
            ->willReturn($accountUrl);
        $this->bakerUrl->expects($this->any())
            ->method('getLoginUrl')
            ->willReturn($loginUrl);
        $this->bakerUrl->expects($this->any())
            ->method('getLogoutUrl')
            ->willReturn($logoutUrl);
        $this->bakerUrl->expects($this->any())
            ->method('getDashboardUrl')
            ->willReturn($dashboardUrl);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with(BakerUrl::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD, ScopeInterface::SCOPE_STORE)
            ->willReturn($redirectToDashboard);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with(BakerUrl::REFERER_QUERY_PARAM_NAME)
            ->willReturn($referer);

        $this->urlDecoder->expects($this->any())
            ->method('decode')
            ->with($referer)
            ->willReturn($referer);

        $this->url->expects($this->any())
            ->method('isOwnOriginUrl')
            ->willReturn(true);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($beforeAuthUrl)
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->model->getRedirect();
    }

    /**
     * @return array
     */
    public function getRedirectDataProvider()
    {
        /**
         * Baker ID
         * Last baker ID
         * Referer
         * Base URL
         * BeforeAuth URL
         * AfterAuth URL
         * Account URL
         * Login URL
         * Logout URL
         * Dashboard URL
         * Is baker logged in flag
         * Redirect to Dashboard flag
         */
        return [
            // Loggend In, Redirect by Referer
            [1, 2, 'referer', 'base', '', '', 'account', '', '', '', true, false],
            [1, 2, 'http://referer.com/', 'http://base.com/', '', '', 'account', '', '', 'dashboard', true, false],
            // Loggend In, Redirect by AfterAuthUrl
            [1, 2, 'referer', 'base', '', 'defined', 'account', '', '', '', true, true],
            // Not logged In, Redirect by LoginUrl
            [1, 2, 'referer', 'base', '', '', 'account', 'login', '', '', false, true],
            // Logout, Redirect to Dashboard
            [1, 2, 'referer', 'base', 'logout', '', 'account', 'login', 'logout', 'dashboard', false, true],
            // Default redirect
            [1, 2, 'referer', 'base', 'defined', '', 'account', 'login', 'logout', 'dashboard', true, true],
        ];
    }

    public function testBeforeRequestParams()
    {
        $requestParams = [
            'param1' => 'value1',
        ];

        $module = 'module';
        $controller = 'controller';
        $action = 'action';

        $this->bakerSession->expects($this->exactly(2))
            ->method('getBeforeRequestParams')
            ->willReturn($requestParams);
        $this->bakerSession->expects($this->once())
            ->method('getBeforeModuleName')
            ->willReturn($module);
        $this->bakerSession->expects($this->once())
            ->method('getBeforeControllerName')
            ->willReturn($controller);
        $this->bakerSession->expects($this->once())
            ->method('getBeforeAction')
            ->willReturn($action);

        $this->resultForward->expects($this->once())
            ->method('setParams')
            ->with($requestParams)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('setModule')
            ->with($module)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('setController')
            ->with($controller)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with($action)
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_FORWARD)
            ->willReturn($this->resultForward);

        $result = $this->model->getRedirect();
        $this->assertSame($this->resultForward, $result);
    }
}
