<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Observer;

use CND\Baker\Observer\UpgradeBakerPasswordObserver;

class UpgradeBakerPasswordObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpgradeBakerPasswordObserver
     */
    protected $model;

    /**
     * @var \Magento\Framework\Encryption\Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepository;

    /**
     * @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRegistry;

    protected function setUp()
    {
        $this->bakerRepository = $this->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->bakerRegistry = $this->getMockBuilder(\CND\Baker\Model\BakerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\Encryptor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new UpgradeBakerPasswordObserver(
            $this->encryptorMock,
            $this->bakerRegistry,
            $this->bakerRepository
        );
    }

    public function testUpgradeBakerPassword()
    {
        $bakerId = '1';
        $password = 'password';
        $passwordHash = 'hash:salt:999';
        $model = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $baker = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();
        $bakerSecure = $this->getMockBuilder(\CND\Baker\Model\Data\BakerSecure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPasswordHash', 'setPasswordHash'])
            ->getMock();
        $model->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($bakerId);
        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($baker);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($bakerSecure);
        $bakerSecure->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);
        $this->encryptorMock->expects($this->once())
            ->method('validateHashVersion')
            ->with($passwordHash)
            ->willReturn(false);
        $this->encryptorMock->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($passwordHash);
        $bakerSecure->expects($this->once())
            ->method('setPasswordHash')
            ->with($passwordHash);
        $this->bakerRepository->expects($this->once())
            ->method('save')
            ->with($baker);
        $event = new \Magento\Framework\DataObject();
        $event->setData(['password' => 'password', 'model' => $model]);
        $observerMock = new \Magento\Framework\Event\Observer();
        $observerMock->setEvent($event);
        $this->model->execute($observerMock);
    }
}
