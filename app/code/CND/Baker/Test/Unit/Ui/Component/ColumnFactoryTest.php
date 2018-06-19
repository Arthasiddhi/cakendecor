<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Ui\Component;

use CND\Baker\Ui\Component\ColumnFactory;

class ColumnFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Api\Data\OptionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeOption;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $componentFactory;

    /** @var \CND\Baker\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var \Magento\Ui\Component\Listing\Columns\ColumnInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $column;

    /** @var \CND\Baker\Ui\Component\Listing\Column\InlineEditUpdater|\PHPUnit_Framework_MockObject_MockObject */
    protected $inlineEditUpdater;

    /** @var ColumnFactory */
    protected $columnFactory;

    protected function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false
        );
        $this->componentFactory = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            ['create']
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->column = $this->getMockForAbstractClass(
            \Magento\Ui\Component\Listing\Columns\ColumnInterface::class,
            [],
            '',
            false
        );
        $this->attributeOption = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\OptionInterface::class,
            [],
            '',
            false
        );

        $this->inlineEditUpdater = $this->getMockBuilder(
            \CND\Baker\Ui\Component\Listing\Column\InlineEditUpdater::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->columnFactory = new ColumnFactory($this->componentFactory, $this->inlineEditUpdater);
    }

    public function testCreate()
    {
        $columnName = 'created_at';
        $config = [
            'data' => [
                'js_config' => [
                    'component' => 'Magento_Ui/js/grid/columns/column',
                ],
                'config' => [
                    'label' => __('Label'),
                    'dataType' => 'text',
                    'align' => 'left',
                    'visible' => true,
                    'options' =>  [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ],
                    'component' => 'Magento_Ui/js/grid/columns/column',
                ],
            ],
            'context' => $this->context,
        ];
        $attributeData = [
            'attribute_code' => 'billing_attribute_code',
            'frontend_input' => 'text',
            'frontend_label' => 'Label',
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
            'entity_type_code' => 'baker',
            'validation_rules' => [],
            'required' => false,
        ];
        $this->inlineEditUpdater->expects($this->once())
            ->method('applyEditing')
            ->with($this->column, 'text', []);
        $this->componentFactory->expects($this->once())
            ->method('create')
            ->with($columnName, 'column', $config)
            ->willReturn($this->column);

        $this->assertSame(
            $this->column,
            $this->columnFactory->create($attributeData, $columnName, $this->context)
        );
    }
}
