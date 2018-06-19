<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Model\BakerExtractor;

class BakerExtractorTest extends \PHPUnit\Framework\TestCase
{
    /** @var BakerExtractor */
    protected $bakerExtractor;

    /** @var \CND\Baker\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var \CND\Baker\Api\Data\BakerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \CND\Baker\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerGroupManagement;

    /** @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectHelper;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \CND\Baker\Model\Metadata\Form|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerForm;

    /** @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerData;

    /** @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    /** @var \CND\Baker\Api\Data\GroupInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerGroup;

    protected function setUp()
    {
        $this->formFactory = $this->getMockForAbstractClass(
            \CND\Baker\Model\Metadata\FormFactory::class,
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->bakerFactory = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\BakerInterfaceFactory::class,
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->bakerGroupManagement = $this->getMockForAbstractClass(
            \CND\Baker\Api\GroupManagementInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $this->bakerForm = $this->createMock(\CND\Baker\Model\Metadata\Form::class);
        $this->bakerData = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\BakerInterface::class,
            [],
            '',
            false
        );
        $this->store = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            '',
            false
        );
        $this->bakerGroup = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\GroupInterface::class,
            [],
            '',
            false
        );
        $this->bakerExtractor = new BakerExtractor(
            $this->formFactory,
            $this->bakerFactory,
            $this->storeManager,
            $this->bakerGroupManagement,
            $this->dataObjectHelper
        );
    }

    public function testExtract()
    {
        $bakerData = [
            'firstname' => 'firstname',
            'lastname' => 'firstname',
            'email' => 'email.example.com',
        ];

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('baker', 'form-code')
            ->willReturn($this->bakerForm);
        $this->bakerForm->expects($this->once())
            ->method('extractData')
            ->with($this->request)
            ->willReturn($bakerData);
        $this->bakerForm->expects($this->once())
            ->method('compactData')
            ->with($bakerData)
            ->willReturn($bakerData);
        $this->bakerForm->expects($this->once())
            ->method('getAllowedAttributes')
            ->willReturn(['group_id' => 'attribute object']);
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->bakerData);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->bakerData, $bakerData, \CND\Baker\Api\Data\BakerInterface::class)
            ->willReturn($this->bakerData);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);
        $this->bakerGroupManagement->expects($this->once())
            ->method('getDefaultGroup')
            ->with(1)
            ->willReturn($this->bakerGroup);
        $this->bakerGroup->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->bakerData->expects($this->once())
            ->method('setGroupId')
            ->with(1);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->bakerData->expects($this->once())
            ->method('setWebsiteId')
            ->with(1);
        $this->bakerData->expects($this->once())
            ->method('setStoreId')
            ->with(1);

        $this->assertSame($this->bakerData, $this->bakerExtractor->extract('form-code', $this->request));
    }
}
