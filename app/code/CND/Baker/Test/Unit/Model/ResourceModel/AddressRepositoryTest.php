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
class AddressRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryData;

    /**
     * @var \CND\Baker\Model\AddressFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactory;

    /**
     * @var \CND\Baker\Model\AddressRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRegistry;

    /**
     * @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressResourceModel;

    /**
     * @var \CND\Baker\Api\Data\AddressSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \CND\Baker\Model\ResourceModel\Address\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \CND\Baker\Model\Baker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baker;

    /**
     * @var \CND\Baker\Model\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $address;

    /**
     * @var \CND\Baker\Model\ResourceModel\AddressRepository
     */
    protected $repository;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->addressFactory = $this->createPartialMock(\CND\Baker\Model\AddressFactory::class, ['create']);
        $this->addressRegistry = $this->createMock(\CND\Baker\Model\AddressRegistry::class);
        $this->bakerRegistry = $this->createMock(\CND\Baker\Model\BakerRegistry::class);
        $this->addressResourceModel = $this->createMock(\CND\Baker\Model\ResourceModel\Address::class);
        $this->directoryData = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->addressSearchResultsFactory = $this->createPartialMock(
            \CND\Baker\Api\Data\AddressSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->addressCollectionFactory = $this->createPartialMock(
            \CND\Baker\Model\ResourceModel\Address\CollectionFactory::class,
            ['create']
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->baker = $this->createMock(\CND\Baker\Model\Baker::class);
        $this->address = $this->createPartialMock(\CND\Baker\Model\Address::class, [
                'getId',
                'getCountryId',
                'getFirstname',
                'getLastname',
                'getStreetLine',
                'getCity',
                'getTelephone',
                'getRegionId',
                'getRegion',
                'updateData',
                'setBaker',
                'getCountryModel',
                'getShouldIgnoreValidation',
                'validate',
                'save',
                'getDataModel',
                'getBakerId',
            ]);

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->repository = new \CND\Baker\Model\ResourceModel\AddressRepository(
            $this->addressFactory,
            $this->addressRegistry,
            $this->bakerRegistry,
            $this->addressResourceModel,
            $this->directoryData,
            $this->addressSearchResultsFactory,
            $this->addressCollectionFactory,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $bakerId = 34;
        $addressId = 53;
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $addressCollection =
            $this->createMock(\CND\Baker\Model\ResourceModel\Address\Collection::class);
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);
        $this->address->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($addressId);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn(null);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($bakerAddress);
        $this->address->expects($this->once())
            ->method('setBaker')
            ->with($this->baker);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->address->expects($this->once())
            ->method('save');
        $this->addressRegistry->expects($this->once())
            ->method('push')
            ->with($this->address);
        $this->baker->expects($this->exactly(2))
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method("removeItemByKey")
            ->with($addressId);
        $addressCollection->expects($this->once())
            ->method("addItem")
            ->with($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($bakerAddress);

        $this->repository->save($bakerAddress);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveWithException()
    {
        $bakerId = 34;
        $addressId = 53;
        $errors[] = __('Please enter the state/province.');
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($bakerAddress);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($bakerAddress);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage region is a required field.
     */
    public function testSaveWithInvalidRegion()
    {
        $bakerId = 34;
        $addressId = 53;
        $errors[] = __('region is a required field.');
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($bakerAddress);

        $this->address->expects($this->never())
            ->method('getRegionId')
            ->willReturn(null);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($bakerAddress);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage regionId is a required field.
     */
    public function testSaveWithInvalidRegionId()
    {
        $bakerId = 34;
        $addressId = 53;
        $errors[] = __('regionId is a required field.');
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $bakerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($bakerAddress);
        $this->address->expects($this->never())
            ->method('getRegion')
            ->willReturn('');
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($bakerAddress);
    }

    public function testGetById()
    {
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with(12)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($bakerAddress);

        $this->assertSame($bakerAddress, $this->repository->getById(12));
    }

    public function testGetList()
    {
        $collection = $this->createMock(\CND\Baker\Model\ResourceModel\Address\Collection::class);
        $searchResults = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressSearchResultsInterface::class,
            [],
            '',
            false
        );
        $searchCriteria = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $this->addressSearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $this->addressCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, \CND\Baker\Api\Data\AddressInterface::class);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection)
            ->willReturnSelf();

        $collection->expects($this->once())->method('getSize')->willReturn(23);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(23);
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->address]);
        $this->address->expects($this->once())
            ->method('getId')
            ->willReturn(12);
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with(12)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($bakerAddress);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$bakerAddress]);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);

        $this->assertSame($searchResults, $this->repository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $addressId = 12;
        $bakerId = 43;

        $addressCollection = $this->createMock(\CND\Baker\Model\ResourceModel\Address\Collection::class);
        $bakerAddress = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $bakerAddress->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);
        $this->address->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);

        $this->baker->expects($this->once())
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method('clear');
        $this->addressResourceModel->expects($this->once())
            ->method('delete')
            ->with($this->address);
        $this->addressRegistry->expects($this->once())
            ->method('remove')
            ->with($addressId);

        $this->assertTrue($this->repository->delete($bakerAddress));
    }

    public function testDeleteById()
    {
        $addressId = 12;
        $bakerId = 43;

        $this->address->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $addressCollection = $this->createMock(\CND\Baker\Model\ResourceModel\Address\Collection::class);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($this->baker);
        $this->baker->expects($this->once())
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method('removeItemByKey')
            ->with($addressId);
        $this->addressResourceModel->expects($this->once())
            ->method('delete')
            ->with($this->address);
        $this->addressRegistry->expects($this->once())
            ->method('remove')
            ->with($addressId);

        $this->assertTrue($this->repository->deleteById($addressId));
    }
}
