<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block;

use CND\Baker\Block\BakerData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class BakerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
    }

    public function testGetExpirableSectionLifetimeReturnsConfigurationValue()
    {
        $block = new BakerData(
            $this->contextMock,
            [],
            []
        );

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('baker/online_bakers/section_data_lifetime', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('10');

        $actualResult = $block->getExpirableSectionLifetime();
        $this->assertSame(10, $actualResult);
    }

    public function testGetExpirableSectionNames()
    {
        $expectedResult = ['cart'];
        $block = new BakerData(
            $this->contextMock,
            [],
            $expectedResult
        );

        $this->assertEquals($expectedResult, $block->getExpirableSectionNames());
    }
}
