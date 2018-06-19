<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Account;

use CND\Baker\Model\Context;

/**
 * Test class for \CND\Baker\Block\Account\RegisterLink
 */
class RegisterLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param bool $isAuthenticated
     * @param bool $isRegistrationAllowed
     * @param bool $result
     * @dataProvider dataProviderToHtml
     * @return void
     */
    public function testToHtml($isAuthenticated, $isRegistrationAllowed, $result)
    {
        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);

        $httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->any())
            ->method('getValue')
            ->with(Context::CONTEXT_AUTH)
            ->will($this->returnValue($isAuthenticated));

        $registrationMock = $this->getMockBuilder(\CND\Baker\Model\Registration::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();
        $registrationMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue($isRegistrationAllowed));

        /** @var \CND\Baker\Block\Account\RegisterLink $link */
        $link = $this->_objectManager->getObject(
            \CND\Baker\Block\Account\RegisterLink::class,
            [
                'context' => $context,
                'httpContext' => $httpContext,
                'registration' => $registrationMock,
            ]
        );

        $this->assertEquals($result, $link->toHtml() === '');
    }

    /**
     * @return array
     */
    public function dataProviderToHtml()
    {
        return [
            [true, true, true],
            [false, false, true],
            [true, false, true],
            [false, true, false],
        ];
    }

    public function testGetHref()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            \CND\Baker\Model\Url::class
        )->disableOriginalConstructor()->setMethods(
            ['getRegisterUrl']
        )->getMock();

        $helper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue('register url'));

        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);

        $block = $this->_objectManager->getObject(
            \CND\Baker\Block\Account\RegisterLink::class,
            ['context' => $context, 'bakerUrl' => $helper]
        );
        $this->assertEquals('register url', $block->getHref());
    }
}
