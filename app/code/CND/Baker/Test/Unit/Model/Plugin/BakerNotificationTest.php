<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Plugin;

use Magento\Backend\App\AbstractAction;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Model\Baker\NotificationStorage;
use CND\Baker\Model\Plugin\BakerNotification;
use CND\Baker\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class BakerNotificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
    private $session;

    /** @var \CND\Baker\Model\Baker\NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationStorage;

    /** @var BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $bakerRepository;

    /** @var State|\PHPUnit_Framework_MockObject_MockObject */
    private $appState;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    private $abstractAction;

    /** @var BakerNotification */
    private $plugin;

    /** @var int */
    private static $bakerId = 1;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->notificationStorage = $this->getMockBuilder(NotificationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerRepository = $this->getMockForAbstractClass(BakerRepositoryInterface::class);
        $this->abstractAction = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->appState->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->request->method('isPost')->willReturn(true);
        $this->session->method('getBakerId')->willReturn(self::$bakerId);
        $this->notificationStorage->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$bakerId)
            ->willReturn(true);

        $this->plugin = new BakerNotification(
            $this->session,
            $this->notificationStorage,
            $this->appState,
            $this->bakerRepository,
            $this->logger
        );
    }

    public function testBeforeDispatch()
    {
        $bakerGroupId =1;

        $bakerMock = $this->getMockForAbstractClass(BakerInterface::class);
        $bakerMock->method('getGroupId')->willReturn($bakerGroupId);
        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with(self::$bakerId)
            ->willReturn($bakerMock);
        $this->session->expects($this->once())->method('setBakerData')->with($bakerMock);
        $this->session->expects($this->once())->method('setBakerGroupId')->with($bakerGroupId);
        $this->session->expects($this->once())->method('regenerateId');
        $this->notificationStorage->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$bakerId);

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }

    public function testBeforeDispatchWithNoBakerFound()
    {
        $this->bakerRepository->method('getById')
            ->with(self::$bakerId)
            ->willThrowException(new NoSuchEntityException());
        $this->logger->expects($this->once())
            ->method('error');

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }
}
