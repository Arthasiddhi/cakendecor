<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Config\Source;

use CND\Baker\Api\GroupManagementInterface;
use CND\Baker\Model\Config\Source\Group;
use CND\Baker\Model\Baker\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupSourceLoggedInOnlyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupSource;

    /**
     * @var Group
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    protected function setUp()
    {
        $this->groupServiceMock = $this->createMock(GroupManagementInterface::class);
        $this->converterMock = $this->createMock(DataObject::class);
        $this->groupSource = $this->getMockBuilder(GroupSourceLoggedInOnlyInterface::class)
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            Group::class,
            [
                'groupManagement' => $this->groupServiceMock,
                'converter' => $this->converterMock,
                'groupSourceForLoggedInBakers' => $this->groupSource,
            ]
        );
    }

    public function testToOptionArray()
    {
        $expectedValue = ['General', 'Retail'];
        $this->groupServiceMock->expects($this->never())->method('getLoggedInGroups');
        $this->converterMock->expects($this->never())->method('toOptionArray');

        $this->groupSource->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expectedValue);

        array_unshift($expectedValue, ['value' => '', 'label' => __('-- Please Select --')]);
        $this->assertEquals($expectedValue, $this->model->toOptionArray());
    }
}
