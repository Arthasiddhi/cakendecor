<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Api\Data\OptionInterfaceFactory;
use CND\Baker\Api\Data\ValidationRuleInterfaceFactory;
use CND\Baker\Api\Data\AttributeMetadataInterfaceFactory;
use CND\Baker\Model\AttributeMetadataConverter;

/**
 * Class AttributeMetadataConverterTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package CND\Baker\Test\Unit\Model
 */
class AttributeMetadatConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OptionInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $validationRuleFactory;

    /**
     * @var AttributeMetadataInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /** @var  AttributeMetadataConverter */
    private $model;

    /** @var  \CND\Baker\Model\Attribute | \PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    public function setUp()
    {
        $this->optionFactory = $this->getMockBuilder(OptionInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationRuleFactory = $this->getMockBuilder(ValidationRuleInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataFactory = $this->getMockBuilder(AttributeMetadataInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper =  $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeMetadataConverter(
            $this->optionFactory,
            $this->validationRuleFactory,
            $this->attributeMetadataFactory,
            $this->dataObjectHelper
        );
    }

    /**
     * @return array
     */
    private function prepareValidateRules()
    {
        return [
            'one' => 'numeric',
            'two' => 'alphanumeric'
        ];
    }

    /**
     * @return array
     */
    private function prepareOptions()
    {
        return [
            [
                'label' => 'few_values',
                'value' => [
                    [1], [2]
                ]
            ],
            [
                'label' => 'one_value',
                'value' => 1
            ]
        ];
    }

    public function testCreateAttributeMetadataTestWithSource()
    {
        $validatedRules = $this->prepareValidateRules();
        $options = $this->prepareOptions();
        $optionDataObjectForSimpleValue1 = $this->getMockBuilder(\CND\Baker\Model\Data\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionDataObjectForSimpleValue2 = $this->getMockBuilder(\CND\Baker\Model\Data\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionObject1 = $this->createMock(\CND\Baker\Api\Data\OptionInterface::class);
        $optionObject2 = $this->createMock(\CND\Baker\Api\Data\OptionInterface::class);
        $this->optionFactory->expects($this->exactly(4))
            ->method('create')
            ->will(
                $this->onConsecutiveCalls(
                    $optionDataObjectForSimpleValue2,
                    $optionObject1,
                    $optionObject2,
                    $optionDataObjectForSimpleValue1
                )
            );
        $source = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn($options);
        $this->attribute->expects($this->once())
            ->method('usesSource')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getSource')
            ->willReturn($source);
        $optionDataObjectForSimpleValue1->expects($this->once())
            ->method('setValue')
            ->with(1);
        $optionDataObjectForSimpleValue2->expects($this->once())
            ->method('setLabel')
            ->with('few_values');
        $optionDataObjectForSimpleValue1->expects($this->once())
            ->method('setLabel')
            ->with('one_value');
        $this->dataObjectHelper->expects($this->exactly(2))
            ->method('populateWithArray')
            ->withConsecutive(
                [$optionObject1, ['1'], \CND\Baker\Api\Data\OptionInterface::class],
                [$optionObject2, ['2'], \CND\Baker\Api\Data\OptionInterface::class]
            );
        $validationRule1 = $this->createMock(\CND\Baker\Api\Data\ValidationRuleInterface::class);
        $validationRule2 = $this->createMock(\CND\Baker\Api\Data\ValidationRuleInterface::class);
        $this->validationRuleFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($validationRule1, $validationRule2));
        $validationRule1->expects($this->once())
            ->method('setValue')
            ->with('numeric');
        $validationRule1->expects($this->once())
            ->method('setName')
            ->with('one')
            ->willReturnSelf();
        $validationRule2->expects($this->once())
            ->method('setValue')
            ->with('alphanumeric');
        $validationRule2->expects($this->once())
            ->method('setName')
            ->with('two')
            ->willReturnSelf();

        $mockMethods = ['setAttributeCode', 'setFrontendInput'];
        $attributeMetaData = $this->getMockBuilder(\CND\Baker\Model\Data\AttributeMetadata::class)
            ->setMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();
        foreach ($mockMethods as $method) {
            $attributeMetaData->expects($this->once())->method($method)->willReturnSelf();
        }

        $this->attribute->expects($this->once())
            ->method('getValidateRules')
            ->willReturn($validatedRules);
        $this->attributeMetadataFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeMetaData);
        $frontend = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontend);
        $optionDataObjectForSimpleValue2->expects($this->once())
            ->method('setOptions')
            ->with([$optionObject1, $optionObject2]);
        $this->model->createMetadataAttribute($this->attribute);
    }
}
