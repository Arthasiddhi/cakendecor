<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Baker;

use CND\Baker\Model\Baker\NotificationStorage;

class NotificationStorageTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->notificationStorage = $objectManager->getObject(
            NotificationStorage::class,
            ['cache' => $this->cacheMock]
        );
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->notificationStorage, 'serializer', $this->serializerMock);
    }

    public function testAdd()
    {
        $bakerId = 1;
        $notificationType = 'some_type';
        $data = [
            'baker_id' => $bakerId,
            'notification_type' => $notificationType
        ];
        $serializedData = 'serialized data';
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $this->getCacheKey($notificationType, $bakerId)
            );
        $this->notificationStorage->add($notificationType, $bakerId);
    }

    public function testIsExists()
    {
        $bakerId = 1;
        $notificationType = 'some_type';
        $this->cacheMock->expects($this->once())
            ->method('test')
            ->with($this->getCacheKey($notificationType, $bakerId))
            ->willReturn(true);
        $this->assertTrue($this->notificationStorage->isExists($notificationType, $bakerId));
    }

    public function testRemove()
    {
        $bakerId = 1;
        $notificationType = 'some_type';
        $this->cacheMock->expects($this->once())
            ->method('remove')
            ->with($this->getCacheKey($notificationType, $bakerId));
        $this->notificationStorage->remove($notificationType, $bakerId);
    }

    /**
     * Get cache key
     *
     * @param string $notificationType
     * @param string $bakerId
     * @return string
     */
    private function getCacheKey($notificationType, $bakerId)
    {
        return 'notification_' . $notificationType . '_' . $bakerId;
    }
}
