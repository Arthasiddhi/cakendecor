<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use CND\Baker\Block\BakerScopeData;
use Magento\Framework\Json\EncoderInterface;

class BakerScopeDataTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Block\BakerScopeData */
    private $model;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $encoderMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->encoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->getMock();

        $this->contextMock->expects($this->exactly(2))
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->model = new BakerScopeData(
            $this->contextMock,
            $this->encoderMock,
            []
        );
    }

    public function testGetWebsiteId()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $this->assertEquals($storeId, $this->model->getWebsiteId());
    }
}
