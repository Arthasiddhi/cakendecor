<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Account;

/**
 * Test class for \CND\Baker\Block\Account\AuthorizationLink
 */
class AuthorizationLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \CND\Baker\Model\Url
     */
    protected $_bakerUrl;

    /**
     * @var \CND\Baker\Block\Account\AuthorizationLink
     */
    protected $_block;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $this->_bakerUrl = $this->getMockBuilder(\CND\Baker\Model\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLogoutUrl', 'getLoginUrl'])
            ->getMock();

        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);
        $this->_block = $this->_objectManager->getObject(
            \CND\Baker\Block\Account\AuthorizationLink::class,
            [
                'context' => $context,
                'httpContext' => $this->httpContext,
                'bakerUrl' => $this->_bakerUrl,
            ]
        );
    }

    public function testGetLabelLoggedIn()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        $this->assertEquals('Sign Out', $this->_block->getLabel());
    }

    public function testGetLabelLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(false));

        $this->assertEquals('Sign In', $this->_block->getLabel());
    }

    public function testGetHrefLoggedIn()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        $this->_bakerUrl->expects($this->once())->method('getLogoutUrl')->will($this->returnValue('logout url'));

        $this->assertEquals('logout url', $this->_block->getHref());
    }

    public function testGetHrefLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(false));

        $this->_bakerUrl->expects($this->once())->method('getLoginUrl')->will($this->returnValue('login url'));

        $this->assertEquals('login url', $this->_block->getHref());
    }
}
