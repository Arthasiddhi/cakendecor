<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Metadata;

use CND\Baker\Model\Metadata\ElementFactory;

class ElementFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $_objectManager;

    /** @var \CND\Baker\Model\Data\AttributeMetadata | \PHPUnit_Framework_MockObject_MockObject */
    private $_attributeMetadata;

    /** @var string */
    private $_entityTypeCode = 'baker_address';

    /** @var ElementFactory */
    private $_elementFactory;

    protected function setUp()
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_attributeMetadata = $this->createMock(\CND\Baker\Model\Data\AttributeMetadata::class);
        $this->_elementFactory = new ElementFactory($this->_objectManager, new \Magento\Framework\Stdlib\StringUtils());
    }

    /** TODO fix when Validation is implemented MAGETWO-17341 */
    public function testAttributePostcodeDataModelClass()
    {
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getDataModel'
        )->will(
            $this->returnValue(\CND\Baker\Model\Attribute\Data\Postcode::class)
        );

        $dataModel = $this->createMock(\CND\Baker\Model\Metadata\Form\Text::class);
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue($dataModel));

        $actual = $this->_elementFactory->create($this->_attributeMetadata, '95131', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }

    public function testAttributeEmptyDataModelClass()
    {
        $this->_attributeMetadata->expects($this->once())->method('getDataModel')->will($this->returnValue(''));
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue('text')
        );

        $dataModel = $this->createMock(\CND\Baker\Model\Metadata\Form\Text::class);
        $params = [
            'entityTypeCode' => $this->_entityTypeCode,
            'value' => 'Some Text',
            'isAjax' => false,
            'attribute' => $this->_attributeMetadata,
        ];
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \CND\Baker\Model\Metadata\Form\Text::class,
            $params
        )->will(
            $this->returnValue($dataModel)
        );

        $actual = $this->_elementFactory->create($this->_attributeMetadata, 'Some Text', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }
}
