<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Ui\Component\Listing\Column;

use CND\Baker\Ui\Component\Listing\Column\AttributeColumn;

class AttributeColumnTest extends \PHPUnit\Framework\TestCase
{
    /** @var AttributeColumn */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var \CND\Baker\Ui\Component\Listing\AttributeRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeRepository;

    /** @var \CND\Baker\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    public function setup()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->attributeRepository = $this->createMock(
            \CND\Baker\Ui\Component\Listing\AttributeRepository::class
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );

        $this->component = new AttributeColumn(
            $this->context,
            $this->uiComponentFactory,
            $this->attributeRepository
        );
        $this->component->setData('name', 'gender');
    }

    public function testPrepareDataSource()
    {
        $genderOptionId = 1;
        $genderOptionLabel = 'Male';

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'testName'
                    ],
                    [
                        'gender' => $genderOptionId
                    ]
                ]
            ]
        ];
        $expectedSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'testName'
                    ],
                    [
                        'gender' => $genderOptionLabel
                    ]
                ]
            ]
        ];

        $this->attributeRepository->expects($this->once())
            ->method('getMetadataByCode')
            ->with('gender')
            ->willReturn([
                'attribute_code' => 'billing_attribute_code',
                'frontend_input' => 'frontend-input',
                'frontend_label' => 'frontend-label',
                'backend_type' => 'backend-type',
                'options' => [
                    [
                        'label' => $genderOptionLabel,
                        'value' => $genderOptionId
                    ]
                ],
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
            ]);

        $dataSource = $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedSource, $dataSource);
    }
}
