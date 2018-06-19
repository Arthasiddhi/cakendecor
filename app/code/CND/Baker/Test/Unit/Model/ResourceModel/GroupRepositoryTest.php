<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\GroupRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRegistry;

    /**
     * @var \CND\Baker\Model\GroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupFactory;

    /**
     * @var \CND\Baker\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupModel;

    /**
     * @var \CND\Baker\Api\Data\GroupInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupDataFactory;

    /**
     * @var \CND\Baker\Api\Data\GroupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $group;

    /**
     * @var \CND\Baker\Model\ResourceModel\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \CND\Baker\Api\Data\GroupSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var \CND\Baker\Api\Data\GroupSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResults;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassRepository;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \CND\Baker\Model\ResourceModel\GroupRepository
     */
    protected $model;

    protected function setUp()
    {
        $this->setupGroupObjects();
        $this->dataObjectProcessor = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->searchResultsFactory = $this->createPartialMock(
            \CND\Baker\Api\Data\GroupSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResults = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->taxClassRepository = $this->getMockForAbstractClass(
            \Magento\Tax\Api\TaxClassRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->model = new \CND\Baker\Model\ResourceModel\GroupRepository(
            $this->groupRegistry,
            $this->groupFactory,
            $this->groupDataFactory,
            $this->groupResourceModel,
            $this->dataObjectProcessor,
            $this->searchResultsFactory,
            $this->taxClassRepository,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessorMock
        );
    }

    private function setupGroupObjects()
    {
        $this->groupRegistry = $this->createMock(\CND\Baker\Model\GroupRegistry::class);
        $this->groupFactory = $this->createPartialMock(\CND\Baker\Model\GroupFactory::class, ['create']);
        $this->groupModel = $this->getMockBuilder(\CND\Baker\Model\Group::class)
            ->setMethods(
                [
                    'getTaxClassId',
                    'getTaxClassName',
                    'getId',
                    'getCode',
                    'setDataUsingMethod',
                    'setCode',
                    'setTaxClassId',
                    'usesAsDefault',
                    'delete',
                    'getCollection',
                    'getData',
                ]
            )
            ->setMockClassName('groupModel')
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupDataFactory = $this->createPartialMock(
            \CND\Baker\Api\Data\GroupInterfaceFactory::class,
            ['create']
        );
        $this->group = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\GroupInterface::class,
            [],
            'group',
            false
        );

        $this->groupResourceModel = $this->createMock(\CND\Baker\Model\ResourceModel\Group::class);
    }

    public function testSave()
    {
        $groupId = 0;

        $taxClass = $this->getMockForAbstractClass(\Magento\Tax\Api\Data\TaxClassInterface::class, [], '', false);

        $this->group->expects($this->once())
            ->method('getCode')
            ->willReturn('Code');
        $this->group->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->group->expects($this->once())
            ->method('getTaxClassId')
            ->willReturn(17);

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');
        $this->group->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();

        $this->taxClassRepository->expects($this->once())
            ->method('get')
            ->with(17)
            ->willReturn($taxClass);
        $taxClass->expects($this->once())
            ->method('getClassType')
            ->willReturn('CUSTOMER');

        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->group, \CND\Baker\Api\Data\GroupInterface::class)
            ->willReturn(['attributeCode' => 'attributeData']);
        $this->groupModel->expects($this->once())
            ->method('setDataUsingMethod')
            ->with('attributeCode', 'attributeData');
        $this->groupResourceModel->expects($this->once())
            ->method('save')
            ->with($this->groupModel);
        $this->groupRegistry->expects($this->once())
            ->method('remove')
            ->with($groupId);
        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->group);

        $this->assertSame($this->group, $this->model->save($this->group));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function testSaveWithException()
    {
        $taxClass = $this->getMockForAbstractClass(\Magento\Tax\Api\Data\TaxClassInterface::class, [], '', false);

        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->groupModel);

        $this->group->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->group->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(false);
        $this->group->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->group->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(17);

        $this->groupModel->expects($this->once())
            ->method('setCode')
            ->with('Code');
        $this->groupModel->expects($this->once())
            ->method('setTaxClassId')
            ->with(234);

        $this->taxClassRepository->expects($this->once())
            ->method('get')
            ->with(234)
            ->willReturn($taxClass);
        $taxClass->expects($this->once())
            ->method('getClassType')
            ->willReturn('CUSTOMER');

        $this->groupResourceModel->expects($this->once())
            ->method('save')
            ->with($this->groupModel)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Baker Group already exists.')
            ));

        $this->model->save($this->group);
    }

    public function testGetById()
    {
        $groupId = 86;

        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);

        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->group);

        $this->group->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');

        $this->assertSame($this->group, $this->model->getById($groupId));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $groupId = 86;

        $groupExtension = $this->createMock(\CND\Baker\Api\Data\GroupExtensionInterface::class);
        $collection = $this->createMock(\CND\Baker\Model\ResourceModel\Group\Collection::class);
        $searchCriteria = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $searchResults = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);

        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, \CND\Baker\Api\Data\GroupInterface::class);
        $collection->expects($this->once())
            ->method('addTaxClass');
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->groupModel]));

        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->group);

        $this->group->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');
        $this->groupModel->expects($this->once())
            ->method('getData')
            ->willReturn([]);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('extractExtensionAttributes')
            ->with(\CND\Baker\Api\Data\GroupInterface::class, [])
            ->willReturn(['extension_attributes' => $groupExtension]);
        $this->group->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($groupExtension);
        $collection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(9);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(9);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->group])
            ->willReturnSelf();

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testDeleteById()
    {
        $groupId = 6;
        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(false);
        $this->groupModel->expects($this->once())
            ->method('delete');
        $this->groupRegistry
            ->expects($this->once())
            ->method('remove')
            ->with($groupId);

        $this->assertTrue($this->model->deleteById($groupId));
    }

    public function testDelete()
    {
        $groupId = 6;
        $this->group->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(false);
        $this->groupModel->expects($this->once())
            ->method('delete');
        $this->groupRegistry
            ->expects($this->once())
            ->method('remove')
            ->with($groupId);

        $this->assertTrue($this->model->delete($this->group));
    }
}
