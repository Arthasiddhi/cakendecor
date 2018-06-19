<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

class BakerManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\BakerManagement
     */
    protected $model;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakersFactoryMock;

    protected function setUp()
    {
        $this->bakersFactoryMock = $this->createPartialMock(
            \CND\Baker\Model\ResourceModel\Baker\CollectionFactory::class,
            ['create']
        );
        $this->model = new \CND\Baker\Model\BakerManagement(
            $this->bakersFactoryMock
        );
    }

    public function testGetCount()
    {
        $bakersMock = $this->createMock(\CND\Baker\Model\ResourceModel\Baker\Collection::class);

        $this->bakersFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($bakersMock);
        $bakersMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
