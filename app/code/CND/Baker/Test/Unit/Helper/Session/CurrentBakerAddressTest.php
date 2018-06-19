<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Helper\Session;

class CurrentBakerAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Helper\Session\CurrentBakerAddress
     */
    protected $currentBakerAddress;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentBakerMock;

    /**
     * @var \CND\Baker\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAccountManagementMock;

    /**
     * @var \CND\Baker\Api\Data\AddressInterface
     */
    protected $bakerAddressDataMock;

    /**
     * @var int
     */
    protected $bakerCurrentId = 100;

    /**
     * Test setup
     */
    protected function setUp()
    {
        $this->currentBakerMock = $this->getMockBuilder(\CND\Baker\Helper\Session\CurrentBaker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerAccountManagementMock = $this->getMockBuilder(
            \CND\Baker\Api\AccountManagementInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->currentBakerAddress = new \CND\Baker\Helper\Session\CurrentBakerAddress(
            $this->currentBakerMock,
            $this->bakerAccountManagementMock
        );
    }

    /**
     * test getDefaultBillingAddress
     */
    public function testGetDefaultBillingAddress()
    {
        $this->currentBakerMock->expects($this->once())
            ->method('getBakerId')
            ->will($this->returnValue($this->bakerCurrentId));

        $this->bakerAccountManagementMock->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->will($this->returnValue($this->bakerAddressDataMock));
        $this->assertEquals(
            $this->bakerAddressDataMock,
            $this->currentBakerAddress->getDefaultBillingAddress()
        );
    }

    /**
     * test getDefaultShippingAddress
     */
    public function testGetDefaultShippingAddress()
    {
        $this->currentBakerMock->expects($this->once())
            ->method('getBakerId')
            ->will($this->returnValue($this->bakerCurrentId));
        $this->bakerAccountManagementMock->expects($this->once())
            ->method('getDefaultShippingAddress')
            ->will($this->returnValue($this->bakerAddressDataMock));
        $this->assertEquals(
            $this->bakerAddressDataMock,
            $this->currentBakerAddress->getDefaultShippingAddress()
        );
    }
}
