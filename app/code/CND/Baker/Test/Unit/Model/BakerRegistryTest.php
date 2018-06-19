<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for BakerRegistry
 *
 */
class BakerRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    private $bakerRegistry;

    /**
     * @var \CND\Baker\Model\BakerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerFactory;

    /**
     * @var \CND\Baker\Model\Baker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baker;

    /**#@+
     * Sample baker data
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'baker@example.com';
    const WEBSITE_ID = 1;

    protected function setUp()
    {
        $this->bakerFactory = $this->getMockBuilder(\CND\Baker\Model\BakerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->bakerRegistry = $objectManager->getObject(
            \CND\Baker\Model\BakerRegistry::class,
            ['bakerFactory' => $this->bakerFactory]
        );
        $this->baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getEmail',
                    'getWebsiteId',
                    '__wakeup',
                    'setEmail',
                    'setWebsiteId',
                    'loadByEmail',
                ]
            )
            ->getMock();
    }

    public function testRetrieve()
    {
        $this->baker->expects($this->once())
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->baker));
        $actual = $this->bakerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->baker, $actual);
        $actualCached = $this->bakerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->baker, $actualCached);
    }

    public function testRetrieveByEmail()
    {
        $this->baker->expects($this->once())
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->baker->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(self::CUSTOMER_EMAIL));
        $this->baker->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $this->baker->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->baker));
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->baker));
        $actual = $this->bakerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->baker, $actual);
        $actualCached = $this->bakerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->baker, $actualCached);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $this->baker->expects($this->once())
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->baker));
        $this->bakerRegistry->retrieve(self::CUSTOMER_ID);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveByEmailException()
    {
        $this->baker->expects($this->once())
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(null));
        $this->baker->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(null));
        $this->baker->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->baker));
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->baker));
        $this->bakerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
    }

    public function testRemove()
    {
        $this->baker->expects($this->exactly(2))
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->bakerFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->baker));
        $actual = $this->bakerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->baker, $actual);
        $this->bakerRegistry->remove(self::CUSTOMER_ID);
        $actual = $this->bakerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->baker, $actual);
    }

    public function testRemoveByEmail()
    {
        $this->baker->expects($this->exactly(2))
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->baker->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(self::CUSTOMER_EMAIL));
        $this->baker->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $this->baker->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->baker));
        $this->baker->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->baker));
        $this->bakerFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->baker));
        $actual = $this->bakerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->baker, $actual);
        $this->bakerRegistry->removeByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $actual = $this->bakerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->baker, $actual);
    }
}
