<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Account;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testGetHref()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            \CND\Baker\Model\Url::class
        )->disableOriginalConstructor()->setMethods(
            ['getAccountUrl']
        )->getMock();
        $layout = $this->getMockBuilder(
            \Magento\Framework\View\Layout::class
        )->disableOriginalConstructor()->setMethods(
            ['helper']
        )->getMock();

        $block = $objectManager->getObject(
            \CND\Baker\Block\Account\Link::class,
            ['layout' => $layout, 'bakerUrl' => $helper]
        );
        $helper->expects($this->any())->method('getAccountUrl')->will($this->returnValue('account url'));

        $this->assertEquals('account url', $block->getHref());
    }
}
