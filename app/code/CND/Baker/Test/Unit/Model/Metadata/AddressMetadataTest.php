<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Metadata;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\Data\AttributeMetadataInterface;
use CND\Baker\Model\Attribute;
use CND\Baker\Model\AttributeMetadataConverter;
use CND\Baker\Model\AttributeMetadataDataProvider;
use CND\Baker\Model\Metadata\AddressMetadata;
use CND\Baker\Model\ResourceModel\Form\Attribute\Collection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class AddressMetadataTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressMetadata */
    protected $model;

    /** @var AttributeMetadataConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeConverterMock;

    /** @var AttributeMetadataDataProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeProviderMock;

    protected function setUp()
    {
        $this->attributeConverterMock = $this->getMockBuilder(\CND\Baker\Model\AttributeMetadataConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeProviderMock = $this->getMockBuilder(
            \CND\Baker\Model\AttributeMetadataDataProvider::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AddressMetadata(
            $this->attributeConverterMock,
            $this->attributeProviderMock
        );
    }

    public function testGetAttributes()
    {
        $formCode = 'formcode';
        $attributeCode = 'attr';

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributes = [$attributeMock];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Form\Attribute\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeProviderMock->expects($this->once())
            ->method('loadAttributesCollection')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $formCode)
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));

        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$attributeCode => $metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($result, $this->model->getAttributes($formCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with formCode = formcode
     */
    public function testGetAttributesWithException()
    {
        $formCode = 'formcode';
        $attributes = [];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Form\Attribute\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeProviderMock->expects($this->once())
            ->method('loadAttributesCollection')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $formCode)
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));

        $this->model->getAttributes($formCode);
    }

    public function testGetAttributeMetadata()
    {
        $attributeCode = 'attr';
        $attributeId = 12;

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    public function testGetAttributeMetadataWithCodeId()
    {
        $attributeCode = 'id';

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with entityType = baker_address, attributeCode = id
     */
    public function testGetAttributeMetadataWithoutAttribute()
    {
        $attributeCode = 'id';

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn(null);

        $this->model->getAttributeMetadata($attributeCode);
    }

    public function testGetAllAttributesMetadata()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($result, $this->model->getAllAttributesMetadata());
    }

    public function testGetAllAttributesMetadataWithoutEntity()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn(null);

        $result = [];

        $this->assertEquals($result, $this->model->getAllAttributesMetadata());
    }

    public function testGetCustomAttributesMetadata()
    {
        $attributeCode = 'attr';
        $attributeId = 12;
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $metadataMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(false);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }

    public function testGetCustomAttributesMetadataWithSystemAttribute()
    {
        $attributeCode = 'attr';
        $attributeId = 12;
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $metadataMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(true);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }

    public function testGetCustomAttributesMetadataWithoutAttributes()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }
}
