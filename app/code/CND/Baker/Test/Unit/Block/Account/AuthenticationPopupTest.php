<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Account;

use CND\Baker\Block\Account\AuthenticationPopup;
use CND\Baker\Model\Form;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AuthenticationPopupTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Block\Account\AuthenticationPopup */
    private $model;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilderMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock->method('escapeHtml')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeHtml' . $string;
                }
            );
        $escaperMock->method('escapeUrl')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeUrl' . $string;
                }
            );
        $this->contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();

        $this->model = new AuthenticationPopup(
            $this->contextMock,
            [],
            $this->serializerMock
        );
    }

    /**
     * @param mixed $isAutocomplete
     * @param string $baseUrl
     * @param string $registerUrl
     * @param string $forgotUrl
     * @param array $result
     * @throws \PHPUnit\Framework\Exception
     *
     * @dataProvider dataProviderGetConfig
     */
    public function testGetConfig($isAutocomplete, $baseUrl, $registerUrl, $forgotUrl, array $result)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Form::XML_PATH_ENABLE_AUTOCOMPLETE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($isAutocomplete);

        /** @var StoreInterface||\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['baker/account/create', [], $registerUrl],
                    ['baker/account/forgotpassword', [], $forgotUrl],
                ]
            );

        $this->assertEquals($result, $this->model->getConfig());
    }

    public function dataProviderGetConfig()
    {
        return [
            [
                0,
                'base',
                'reg',
                'forgot',
                [
                    'autocomplete' => 'escapeHtmloff',
                    'bakerRegisterUrl' => 'escapeUrlreg',
                    'bakerForgotPasswordUrl' => 'escapeUrlforgot',
                    'baseUrl' => 'escapeUrlbase',
                ],
            ],
            [
                1,
                '',
                'reg',
                'forgot',
                [
                    'autocomplete' => 'escapeHtmlon',
                    'bakerRegisterUrl' => 'escapeUrlreg',
                    'bakerForgotPasswordUrl' => 'escapeUrlforgot',
                    'baseUrl' => 'escapeUrl',
                ],
            ],
            [
                '',
                'base',
                '',
                'forgot',
                [
                    'autocomplete' => 'escapeHtmloff',
                    'bakerRegisterUrl' => 'escapeUrl',
                    'bakerForgotPasswordUrl' => 'escapeUrlforgot',
                    'baseUrl' => 'escapeUrlbase',
                ],
            ],
            [
                true,
                'base',
                'reg',
                '',
                [
                    'autocomplete' => 'escapeHtmlon',
                    'bakerRegisterUrl' => 'escapeUrlreg',
                    'bakerForgotPasswordUrl' => 'escapeUrl',
                    'baseUrl' => 'escapeUrlbase',
                ],
            ],
        ];
    }

    /**
     * @param mixed $isAutocomplete
     * @param string $baseUrl
     * @param string $registerUrl
     * @param string $forgotUrl
     * @param array $result
     * @throws \PHPUnit\Framework\Exception
     *
     * @dataProvider dataProviderGetConfig
     */
    public function testGetSerializedConfig($isAutocomplete, $baseUrl, $registerUrl, $forgotUrl, array $result)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Form::XML_PATH_ENABLE_AUTOCOMPLETE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($isAutocomplete);

        /** @var StoreInterface||\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['baker/account/create', [], $registerUrl],
                    ['baker/account/forgotpassword', [], $forgotUrl],
                ]
            );
        $this->serializerMock->expects($this->any())->method('serialize')
            ->willReturn(
                json_encode($this->model->getConfig())
            );

        $this->assertEquals(json_encode($result), $this->model->getSerializedConfig());
    }
}
