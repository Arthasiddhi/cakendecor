<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Helper;

use CND\Baker\Api\BakerMetadataInterface;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \CND\Baker\Helper\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerMetadataService;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerMetadataService = $this->createMock(\CND\Baker\Api\BakerMetadataInterface::class);

        $attributeMetadata = $this->createMock(\CND\Baker\Api\Data\AttributeMetadataInterface::class);
        $attributeMetadata->expects($this->any())->method('isVisible')->will($this->returnValue(true));
        $this->bakerMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributeMetadata));

        $this->object = new \CND\Baker\Helper\View($this->context, $this->bakerMetadataService);
    }

    /**
     * @dataProvider getBakerServiceDataProvider
     */
    public function testGetBakerName($prefix, $firstName, $middleName, $lastName, $suffix, $result)
    {
        $bakerData = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerData->expects($this->any())
            ->method('getPrefix')->will($this->returnValue($prefix));
        $bakerData->expects($this->any())
            ->method('getFirstname')->will($this->returnValue($firstName));
        $bakerData->expects($this->any())
            ->method('getMiddlename')->will($this->returnValue($middleName));
        $bakerData->expects($this->any())
            ->method('getLastname')->will($this->returnValue($lastName));
        $bakerData->expects($this->any())
            ->method('getSuffix')->will($this->returnValue($suffix));
        $this->assertEquals($result, $this->object->getBakerName($bakerData));
    }

    /**
     * @return array
     */
    public function getBakerServiceDataProvider()
    {
        return [
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name middle_name last_name suffix', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name middle_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name middle_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'first_name middle_name last_name', //result name
            ],
        ];
    }
}
