<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Ui\Component\Listing;

use CND\Baker\Ui\Component\Listing\AttributeRepository;

class AttributeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Api\BakerMetadataManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerMetadataManagement;

    /** @var \CND\Baker\Api\AddressMetadataManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressMetadataManagement;

    /** @var \CND\Baker\Api\BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerMetadata;

    /** @var \CND\Baker\Api\AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressMetadata;

    /** @var \CND\Baker\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attribute;

    /** @var \CND\Baker\Api\Data\OptionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $option;

    /** @var \CND\Baker\Model\Indexer\Attribute\Filter|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeFilter;

    /** @var AttributeRepository */
    protected $component;

    protected function setUp()
    {
        $this->bakerMetadataManagement = $this->getMockForAbstractClass(
            \CND\Baker\Api\BakerMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadataManagement = $this->getMockForAbstractClass(
            \CND\Baker\Api\AddressMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->bakerMetadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\BakerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\AddressMetadataInterface::class,
            [],
            '',
            false
        );
        $this->attribute = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->option = $this->createMock(\CND\Baker\Api\Data\OptionInterface::class);

        $this->attributeFilter = $this->createMock(\CND\Baker\Model\Indexer\Attribute\Filter::class);

        $this->component = new AttributeRepository(
            $this->bakerMetadataManagement,
            $this->addressMetadataManagement,
            $this->bakerMetadata,
            $this->addressMetadata,
            $this->attributeFilter
        );
    }

    public function testGetList()
    {
        $attributeCode = 'attribute_code';
        $billingPrefix = 'billing_';

        $this->bakerMetadata->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn([]);
        $this->addressMetadata->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn([$this->attribute]);
        $this->addressMetadataManagement->expects($this->once())
            ->method('canBeFilterableInGrid')
            ->with($this->attribute)
            ->willReturn(true);
        $this->addressMetadataManagement->expects($this->once())
            ->method('canBeSearchableInGrid')
            ->with($this->attribute)
            ->willReturn(true);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('frontend-input');
        $this->attribute->expects($this->once())
            ->method('getFrontendLabel')
            ->willReturn('frontend-label');
        $this->attribute->expects($this->once())
            ->method('getBackendType')
            ->willReturn('backend-type');
        $this->attribute->expects($this->once())
            ->method('getOptions')
            ->willReturn([$this->option]);
        $this->attribute->expects($this->once())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getIsVisibleInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([]);
        $this->attribute->expects($this->once())
            ->method('isRequired')
            ->willReturn(false);
        $this->option->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label');
        $this->option->expects($this->once())
            ->method('getValue')
            ->willReturn('Value');
        $this->attributeFilter->expects($this->once())
            ->method('filter')
            ->willReturnArgument(0);

        $this->assertEquals(
            [
                $billingPrefix . $attributeCode => [
                    'attribute_code' => 'billing_attribute_code',
                    'frontend_input' => 'frontend-input',
                    'frontend_label' => 'frontend-label',
                    'backend_type' => 'backend-type',
                    'options' => [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ],
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                    'validation_rules' => [],
                    'required'=> false,
                    'entity_type_code' => 'baker_address',
                ]
            ],
            $this->component->getList()
        );
    }
}
