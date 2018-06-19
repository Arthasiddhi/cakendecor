<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Metadata;

use CND\Baker\Api\AddressMetadataManagementInterface;
use CND\Baker\Model\Attribute;
use CND\Baker\Api\Data\AttributeMetadataInterface;
use CND\Baker\Model\Metadata\AddressMetadataManagement;

class AddressMetadataManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressMetadataManagement */
    protected $model;

    /** @var \CND\Baker\Model\Metadata\AttributeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeResolverMock;

    protected function setUp()
    {
        $this->attributeResolverMock = $this->getMockBuilder(\CND\Baker\Model\Metadata\AttributeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AddressMetadataManagement(
            $this->attributeResolverMock
        );
    }

    public function testCanBeSearchableInGrid()
    {
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $modelMock */
        $modelMock = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeSearchableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeSearchableInGrid($attributeMock));
    }

    public function testCanBeFilterableInGrid()
    {
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $modelMock */
        $modelMock = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeFilterableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeFilterableInGrid($attributeMock));
    }
}
