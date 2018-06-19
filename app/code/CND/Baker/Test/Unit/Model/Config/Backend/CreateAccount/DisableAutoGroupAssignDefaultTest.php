<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Config\Backend\CreateAccount;

class DisableAutoGroupAssignDefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\Config\Backend\CreateAccount\DisableAutoGroupAssignDefault
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    protected function setUp()
    {
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \CND\Baker\Model\Config\Backend\CreateAccount\DisableAutoGroupAssignDefault::class,
            [
                'eavConfig' => $this->eavConfigMock,
            ]
        );
    }

    public function testAfterSave()
    {
        $value = true;

        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['save', 'setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with('baker', 'disable_auto_group_change')
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('setData')
            ->with('default_value', $value);
        $attributeMock->expects($this->once())
            ->method('save');

        $this->model->setValue($value);

        $this->assertEquals($this->model, $this->model->afterSave());
    }
}
