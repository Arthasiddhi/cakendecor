<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BakerGroupConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \CND\Baker\Model\BakerGroupConfig
     */
    private $bakerGroupConfig;

    /**
     * @var \Magento\Config\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \CND\Baker\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepositoryMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(\CND\Baker\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bakerGroupConfig = $this->objectManagerHelper->getObject(
            \CND\Baker\Model\BakerGroupConfig::class,
            [
                'config' => $this->configMock,
                'groupRepository' => $this->groupRepositoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetDefaultBakerGroup()
    {
        $bakerGroupId = 1;

        $bakerGroupMock = $this->getMockBuilder(\CND\Baker\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($bakerGroupMock);
        $this->configMock->expects($this->once())->method('setDataByPath')
            ->with(\CND\Baker\Model\GroupManagement::XML_PATH_DEFAULT_ID, $bakerGroupId)->willReturnSelf();
        $this->configMock->expects($this->once())->method('save');

        $this->assertEquals($bakerGroupId, $this->bakerGroupConfig->setDefaultBakerGroup($bakerGroupId));
    }
}
