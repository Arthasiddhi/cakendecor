<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Observer;

use CND\Baker\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use CND\Baker\Observer\LogLastLoginAtObserver;

/**
 * Class LogLastLoginAtObserverTest
 */
class LogLastLoginAtObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LogLastLoginAtObserver
     */
    protected $logLastLoginAtObserver;

    /**
     * @var Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->loggerMock = $this->createMock(\CND\Baker\Model\Logger::class);
        $this->logLastLoginAtObserver = new LogLastLoginAtObserver($this->loggerMock);
    }

    /**
     * @return void
     */
    public function testLogLastLoginAt()
    {
        $id = 1;

        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getBaker']);
        $bakerMock = $this->createMock(\CND\Baker\Model\Baker::class);

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getBaker')
            ->willReturn($bakerMock);
        $bakerMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->loggerMock->expects($this->once())
            ->method('log');

        $this->logLastLoginAtObserver->execute($observerMock);
    }
}
