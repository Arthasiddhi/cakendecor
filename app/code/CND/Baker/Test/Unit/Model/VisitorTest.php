<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class VisitorTest
 * @package CND\Baker\Model
 */
class VisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\Visitor
     */
    protected $visitor;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    protected function setUp()
    {
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->session = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId', 'getVisitorData', 'setVisitorData'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resource = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Visitor::class)
            ->setMethods([
                'beginTransaction',
                '__sleep',
                '__wakeup',
                'getIdFieldName',
                'save',
                'addCommitCallback',
                'commit',
                'clean',
            ])->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('visitor_id'));
        $this->resource->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());

        $arguments = $this->objectManagerHelper->getConstructArguments(
            \CND\Baker\Model\Visitor::class,
            [
                'registry' => $this->registry,
                'session' => $this->session,
                'resource' => $this->resource
            ]
        );

        $this->visitor = $this->objectManagerHelper->getObject(\CND\Baker\Model\Visitor::class, $arguments);
    }

    public function testInitByRequest()
    {
        $oldSessionId = 'asdfhasdfjhkj2198sadf8sdf897';
        $newSessionId = 'bsdfhasdfjhkj2198sadf8sdf897';
        $this->session->expects($this->any())->method('getSessionId')
            ->will($this->returnValue($newSessionId));
        $this->session->expects($this->atLeastOnce())->method('getVisitorData')
            ->willReturn(['session_id' => $oldSessionId]);
        $this->visitor->initByRequest(null);
        $this->assertEquals($newSessionId, $this->visitor->getSessionId());
    }

    public function testSaveByRequest()
    {
        $this->session->expects($this->once())->method('setVisitorData')->will($this->returnSelf());
        $this->assertSame($this->visitor, $this->visitor->saveByRequest(null));
    }

    public function testIsModuleIgnored()
    {
        $this->visitor = $this->objectManagerHelper->getObject(
            \CND\Baker\Model\Visitor::class,
            [
                'registry' => $this->registry,
                'session' => $this->session,
                'resource' => $this->resource,
                'ignores' => ['test_route_name' => true]
            ]
        );
        $request = new \Magento\Framework\DataObject(['route_name' => 'test_route_name']);
        $action =  new \Magento\Framework\DataObject(['request' => $request]);
        $event =  new \Magento\Framework\DataObject(['controller_action' => $action]);
        $observer = new \Magento\Framework\DataObject(['event' => $event]);
        $this->assertTrue($this->visitor->isModuleIgnored($observer));
    }

    public function testBindBakerLogin()
    {
        $baker = new \Magento\Framework\DataObject(['id' => '1']);
        $observer = new \Magento\Framework\DataObject([
            'event' => new \Magento\Framework\DataObject(['baker' => $baker]),
        ]);

        $this->visitor->bindBakerLogin($observer);
        $this->assertTrue($this->visitor->getDoBakerLogin());
        $this->assertEquals($baker->getId(), $this->visitor->getBakerId());

        $this->visitor->unsetData();
        $this->visitor->setBakerId('2');
        $this->visitor->bindBakerLogin($observer);
        $this->assertNull($this->visitor->getDoBakerLogin());
        $this->assertEquals('2', $this->visitor->getBakerId());
    }

    public function testBindBakerLogout()
    {
        $observer = new \Magento\Framework\DataObject();

        $this->visitor->setBakerId('1');
        $this->visitor->bindBakerLogout($observer);
        $this->assertTrue($this->visitor->getDoBakerLogout());

        $this->visitor->unsetData();
        $this->visitor->bindBakerLogout($observer);
        $this->assertNull($this->visitor->getDoBakerLogout());
    }

    public function testBindQuoteCreate()
    {
        $quote = new \Magento\Framework\DataObject(['id' => '1', 'is_checkout_cart' => true]);
        $observer = new \Magento\Framework\DataObject([
            'event' => new \Magento\Framework\DataObject(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteCreate($observer);
        $this->assertTrue($this->visitor->getDoQuoteCreate());
    }

    public function testBindQuoteDestroy()
    {
        $quote = new \Magento\Framework\DataObject(['id' => '1']);
        $observer = new \Magento\Framework\DataObject([
            'event' => new \Magento\Framework\DataObject(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteDestroy($observer);
        $this->assertTrue($this->visitor->getDoQuoteDestroy());
    }

    public function testClean()
    {
        $this->resource->expects($this->once())->method('clean')->with($this->visitor)->will($this->returnSelf());
        $this->visitor->clean();
    }
}
