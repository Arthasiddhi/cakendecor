<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model\App\Action;

use CND\Baker\Model\Context;

/**
 * Class ContextPluginTest
 */
class ContextPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\App\Action\ContextPlugin
     */
    protected $plugin;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \Magento\Framework\App\Http\Context $httpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->bakerSessionMock = $this->createMock(\CND\Baker\Model\Session::class);
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->subjectMock = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->plugin = new \CND\Baker\Model\App\Action\ContextPlugin(
            $this->bakerSessionMock,
            $this->httpContextMock
        );
    }

    /**
     * Test aroundDispatch
     */
    public function testBeforeDispatch()
    {
        $this->bakerSessionMock->expects($this->once())
            ->method('getBakerGroupId')
            ->will($this->returnValue(1));
        $this->bakerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->will(
                $this->returnValueMap(
                    [
                        [Context::CONTEXT_GROUP, 'UAH', $this->httpContextMock],
                        [Context::CONTEXT_AUTH, 0, $this->httpContextMock],
                    ]
                )
            );
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
