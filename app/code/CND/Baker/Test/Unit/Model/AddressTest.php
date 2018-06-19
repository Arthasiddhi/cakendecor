<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    const ORIG_CUSTOMER_ID = 1;
    const ORIG_PARENT_ID = 2;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CND\Baker\Model\Address
     */
    protected $address;

    /**
     * @var \CND\Baker\Model\Baker | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $baker;

    /**
     * @var \CND\Baker\Model\BakerFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerFactory;

    /**
     * @var \CND\Baker\Model\ResourceModel\Address | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID));
        $this->baker->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $this->bakerFactory = $this->getMockBuilder(\CND\Baker\Model\BakerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->bakerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->baker));

        $this->resource = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->objectManager->getObject(
            \CND\Baker\Model\Address::class,
            [
                'bakerFactory' => $this->bakerFactory,
                'resource' => $this->resource,
            ]
        );
    }

    public function testBakerId()
    {
        $this->address->setParentId(self::ORIG_PARENT_ID);
        $this->assertEquals(self::ORIG_PARENT_ID, $this->address->getBakerId());

        $this->address->setBakerId(self::ORIG_CUSTOMER_ID);
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getBakerId());
    }

    public function testBaker()
    {
        $this->address->unsetData('cusomer_id');
        $this->assertFalse($this->address->getBaker());

        $this->address->setBakerId(self::ORIG_CUSTOMER_ID);

        $baker = $this->address->getBaker();
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $baker->getId());

        /** @var \CND\Baker\Model\Baker $baker */
        $baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID + 1));

        $this->address->setBaker($baker);
        $this->assertEquals(self::ORIG_CUSTOMER_ID + 1, $this->address->getBakerId());
    }

    public function testGetAttributes()
    {
        $resultValue = 'test';

        $this->resource->expects($this->any())
            ->method('loadAllAttributes')
            ->will($this->returnSelf());
        $this->resource->expects($this->any())
            ->method('getSortedAttributes')
            ->will($this->returnValue($resultValue));

        $this->assertEquals($resultValue, $this->address->getAttributes());
    }

    public function testRegionId()
    {
        $this->address->setRegionId(1);
        $this->assertEquals(1, $this->address->getRegionId());
    }

    public function testGetEntityTypeId()
    {
        $mockEntityType = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntityType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID));

        $this->resource->expects($this->any())
            ->method('getEntityType')
            ->will($this->returnValue($mockEntityType));

        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getEntityTypeId());
    }
}
