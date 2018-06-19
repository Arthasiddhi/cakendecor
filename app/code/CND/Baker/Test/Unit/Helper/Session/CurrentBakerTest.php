<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Helper\Session;

class CurrentBakerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerInterfaceFactoryMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerDataMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var int
     */
    protected $bakerId = 100;

    /**
     * @var int
     */
    protected $bakerGroupId = 500;

    /**
     * Test setup
     */
    protected function setUp()
    {
        $this->bakerSessionMock = $this->createMock(\CND\Baker\Model\Session::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->bakerInterfaceFactoryMock = $this->createPartialMock(\CND\Baker\Api\Data\BakerInterfaceFactory::class, ['create', 'setGroupId']);
        $this->bakerDataMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $this->bakerRepositoryMock = $this->createMock(\CND\Baker\Api\BakerRepositoryInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->viewMock = $this->createMock(\Magento\Framework\App\View::class);

        $this->currentBaker = new \CND\Baker\Helper\Session\CurrentBaker(
            $this->bakerSessionMock,
            $this->layoutMock,
            $this->bakerInterfaceFactoryMock,
            $this->bakerRepositoryMock,
            $this->requestMock,
            $this->moduleManagerMock,
            $this->viewMock
        );
    }

    /**
     * test getBaker method, method returns depersonalized baker Data
     */
    public function testGetBakerDepersonalizeBakerData()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue(true));
        $this->viewMock->expects($this->once())->method('isLayoutLoaded')->will($this->returnValue(true));
        $this->moduleManagerMock->expects(
            $this->once()
        )->method(
                'isEnabled'
            )->with(
                $this->equalTo('Magento_PageCache')
            )->will(
                $this->returnValue(true)
            );
        $this->bakerSessionMock->expects(
            $this->once()
        )->method(
                'getBakerGroupId'
            )->will(
                $this->returnValue($this->bakerGroupId)
            );
        $this->bakerInterfaceFactoryMock->expects(
            $this->once()
        )->method(
                'create'
            )->will(
                $this->returnValue($this->bakerDataMock)
            );
        $this->bakerDataMock->expects(
            $this->once()
        )->method(
                'setGroupId'
            )->with(
                $this->equalTo($this->bakerGroupId)
            )->will(
                $this->returnSelf()
            );
        $this->assertEquals($this->bakerDataMock, $this->currentBaker->getBaker());
    }

    /**
     * test get baker method, method returns baker from service
     */
    public function testGetBakerLoadBakerFromService()
    {
        $this->moduleManagerMock->expects(
            $this->once()
        )->method(
                'isEnabled'
            )->with(
                $this->equalTo('Magento_PageCache')
            )->will(
                $this->returnValue(false)
            );
        $this->bakerSessionMock->expects(
            $this->once()
        )->method(
                'getId'
            )->will(
                $this->returnValue($this->bakerId)
            );
        $this->bakerRepositoryMock->expects(
            $this->once()
        )->method(
                'getById'
            )->with(
                $this->equalTo($this->bakerId)
            )->will(
                $this->returnValue($this->bakerDataMock)
            );
        $this->assertEquals($this->bakerDataMock, $this->currentBaker->getBaker());
    }
}
