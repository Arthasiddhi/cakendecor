<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepersonalizePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \CND\Baker\Model\BakerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerFactoryMock;

    /**
     * @var \CND\Baker\Model\Baker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerMock;

    /**
     * @var \CND\Baker\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * @var \Magento\PageCache\Model\DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $depersonalizeCheckerMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->sessionMock = $this->createPartialMock(
            \Magento\Framework\Session\Generic::class,
            ['clearStorage', 'setData', 'getData']
        );
        $this->bakerSessionMock = $this->createPartialMock(
            \CND\Baker\Model\Session::class,
            ['getBakerGroupId', 'setBakerGroupId', 'clearStorage', 'setBaker']
        );
        $this->bakerFactoryMock = $this->createPartialMock(
            \CND\Baker\Model\BakerFactory::class,
            ['create']
        );
        $this->bakerMock = $this->createPartialMock(
            \CND\Baker\Model\Baker::class,
            ['setGroupId', '__wakeup']
        );
        $this->visitorMock = $this->createMock(\CND\Baker\Model\Visitor::class);
        $this->bakerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->bakerMock));
        $this->depersonalizeCheckerMock = $this->createMock(\Magento\PageCache\Model\DepersonalizeChecker::class);

        $this->plugin = new \CND\Baker\Model\Layout\DepersonalizePlugin(
            $this->depersonalizeCheckerMock,
            $this->sessionMock,
            $this->bakerSessionMock,
            $this->bakerFactoryMock,
            $this->visitorMock
        );
    }

    public function testBeforeGenerateXml()
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->bakerSessionMock->expects($this->once())->method('getBakerGroupId');
        $this->sessionMock
            ->expects($this->once())
            ->method('getData')
            ->with($this->equalTo(\Magento\Framework\Data\Form\FormKey::FORM_KEY));
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    public function testBeforeGenerateXmlNoDepersonalize()
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->bakerSessionMock->expects($this->never())->method('getBakerGroupId');
        $this->sessionMock
            ->expects($this->never())
            ->method('getData');
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    public function testAfterGenerateXml()
    {
        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->visitorMock->expects($this->once())->method('setSkipRequestLogging')->with($this->equalTo(true));
        $this->visitorMock->expects($this->once())->method('unsetData');
        $this->sessionMock->expects($this->once())->method('clearStorage');
        $this->bakerSessionMock->expects($this->once())->method('clearStorage');
        $this->bakerSessionMock->expects($this->once())->method('setBakerGroupId')->with($this->equalTo(null));
        $this->bakerMock->expects($this->once())->method('setGroupId')->with($this->equalTo(null))->willReturnSelf();
        $this->sessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(\Magento\Framework\Data\Form\FormKey::FORM_KEY),
                $this->equalTo(null)
            );
        $this->bakerSessionMock
            ->expects($this->once())
            ->method('setBaker')
            ->with($this->equalTo($this->bakerMock));
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->visitorMock->expects($this->never())->method('setSkipRequestLogging');
        $this->visitorMock->expects($this->never())->method('unsetData');
        $this->sessionMock->expects($this->never())->method('clearStorage');
        $this->bakerSessionMock->expects($this->never())->method('clearStorage');
        $this->bakerSessionMock->expects($this->never())->method('setBakerGroupId');
        $this->bakerMock->expects($this->never())->method('setGroupId');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->bakerSessionMock->expects($this->never())->method('setBaker');
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }
}
