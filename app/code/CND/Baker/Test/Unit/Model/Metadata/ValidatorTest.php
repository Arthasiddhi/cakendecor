<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Metadata;

use CND\Baker\Model\Data\AttributeMetadata;
use CND\Baker\Model\Metadata\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var string */
    protected $entityType;

    /** @var \CND\Baker\Model\Metadata\ElementFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $attrDataFactoryMock;

    protected function setUp()
    {
        $this->attrDataFactoryMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\ElementFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->validator = new \CND\Baker\Model\Metadata\Validator($this->attrDataFactoryMock);
    }

    public function testValidateDataWithNoDataModel()
    {
        $attribute = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->attrDataFactoryMock->expects($this->never())->method('create');
        $this->assertTrue($this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testValidateData($isValid)
    {
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->assertEquals($isValid, $this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    public function testIsValidWithNoModel()
    {
        $attribute = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->attrDataFactoryMock->expects($this->never())->method('create');
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData(['something']);
        $this->assertTrue($this->validator->isValid(['entity']));
        $this->validator->setData([]);
        $this->assertTrue($this->validator->isValid(new \Magento\Framework\DataObject([])));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testIsValid($isValid)
    {
        $data = ['something'];
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData($data);
        $this->assertEquals($isValid, $this->validator->isValid(['ENTITY']));
        $this->validator->setData([]);
        $this->assertEquals($isValid, $this->validator->isValid(new \Magento\Framework\DataObject($data)));
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | AttributeMetadata
     */
    protected function getMockAttribute()
    {
        $attribute = $this->getMockBuilder(
            \CND\Baker\Model\Data\AttributeMetadata::class
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup', 'getAttributeCode', 'getDataModel']
        )->getMock();
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue('ATTR_CODE'));
        $attribute->expects($this->any())->method('getDataModel')->will($this->returnValue('DATA_MODEL'));
        return $attribute;
    }

    /**
     * @param bool $isValid
     * @param AttributeMetadata $attribute
     * @return void
     */
    protected function mockDataModel($isValid, AttributeMetadata $attribute)
    {
        $dataModel = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form\Text::class
        )->disableOriginalConstructor()->getMock();
        $dataModel->expects($this->any())->method('validateValue')->will($this->returnValue($isValid));
        $this->attrDataFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->equalTo($attribute),
            $this->equalTo(null),
            $this->equalTo('ENTITY_TYPE')
        )->will(
            $this->returnValue($dataModel)
        );
    }
}
