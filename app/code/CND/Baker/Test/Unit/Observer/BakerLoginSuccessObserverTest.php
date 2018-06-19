<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Observer;

use CND\Baker\Model\AuthenticationInterface;
use Magento\Framework\Event\Observer;
use CND\Baker\Observer\BakerLoginSuccessObserver;

/**
 * Class BakerLoginSuccessObserverTest
 */
class BakerLoginSuccessObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authenticationMock;

    /**
     * @var \CND\Baker\Model\Baker
     */
    protected $bakerModelMock;

    /**
     * @var BakerLoginSuccessObserver
     */
    protected $bakerLoginSuccessObserver;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->authenticationMock = $this->createMock(AuthenticationInterface::class);

        $this->bakerModelMock = $this->createPartialMock(\CND\Baker\Model\Baker::class, ['getId']);
        $this->bakerLoginSuccessObserver = new BakerLoginSuccessObserver(
            $this->authenticationMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $bakerId = 1;
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getData']);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('model')
            ->willReturn($this->bakerModelMock);
        $this->bakerModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $this->authenticationMock->expects($this->once())
            ->method('unlock')
            ->with($bakerId);
        $this->bakerLoginSuccessObserver->execute($observerMock);
    }
}
