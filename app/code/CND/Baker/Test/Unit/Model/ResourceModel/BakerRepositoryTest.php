<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model\ResourceModel;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Model\Baker\NotificationStorage;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class BakerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\BakerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerFactory;

    /**
     * @var \CND\Baker\Model\Data\BakerSecureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSecureFactory;

    /**
     * @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\AddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepository;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerResourceModel;

    /**
     * @var \CND\Baker\Api\BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerMetadata;

    /**
     * @var \CND\Baker\Api\Data\BakerSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Api\ImageProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baker;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \CND\Baker\Model\ResourceModel\BakerRepository
     */
    protected $model;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    protected function setUp()
    {
        $this->bakerResourceModel =
            $this->createMock(\CND\Baker\Model\ResourceModel\Baker::class);
        $this->bakerRegistry = $this->createMock(\CND\Baker\Model\BakerRegistry::class);
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->bakerFactory  =
            $this->createPartialMock(\CND\Baker\Model\BakerFactory::class, ['create']);
        $this->bakerSecureFactory = $this->createPartialMock(
            \CND\Baker\Model\Data\BakerSecureFactory::class,
            ['create']
        );
        $this->addressRepository = $this->createMock(\CND\Baker\Model\ResourceModel\AddressRepository::class);
        $this->bakerMetadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\BakerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactory = $this->createPartialMock(
            \CND\Baker\Api\Data\BakerSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->eventManager = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->extensibleDataObjectConverter = $this->createMock(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
        $this->imageProcessor = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ImageProcessorInterface::class,
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
        $this->baker = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\BakerInterface::class,
            [],
            '',
            true,
            true,
            true,
            [
                '__toArray'
            ]
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $this->notificationStorage = $this->getMockBuilder(NotificationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \CND\Baker\Model\ResourceModel\BakerRepository(
            $this->bakerFactory,
            $this->bakerSecureFactory,
            $this->bakerRegistry,
            $this->addressRepository,
            $this->bakerResourceModel,
            $this->bakerMetadata,
            $this->searchResultsFactory,
            $this->eventManager,
            $this->storeManager,
            $this->extensibleDataObjectConverter,
            $this->dataObjectHelper,
            $this->imageProcessor,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessorMock,
            $this->notificationStorage
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSave()
    {
        $bakerId = 1;
        $storeId = 2;

        $region = $this->getMockForAbstractClass(\CND\Baker\Api\Data\RegionInterface::class, [], '', false);
        $address = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setBakerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $address2 = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setBakerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $bakerModel = $this->createPartialMock(\CND\Baker\Model\Baker::class, [
                'getId',
                'setId',
                'setStoreId',
                'getStoreId',
                'getAttributeSetId',
                'setAttributeSetId',
                'setRpToken',
                'setRpTokenCreatedAt',
                'getDataModel',
                'setPasswordHash',
                'setFailuresNum',
                'setFirstFailure',
                'setLockExpires',
                'save',
            ]);

        $origBaker = $this->baker;

        $this->baker->expects($this->atLeastOnce())
            ->method('__toArray')
            ->willReturn(['default_billing', 'default_shipping']);

        $bakerAttributesMetaData = $this->getMockForAbstractClass(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getId',
                'getEmail',
                'getWebsiteId',
                'getAddresses',
                'setAddresses'
            ]
        );
        $bakerSecureData = $this->createPartialMock(\CND\Baker\Model\Data\BakerSecure::class, [
                'getRpToken',
                'getRpTokenCreatedAt',
                'getPasswordHash',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
            ]);
        $this->baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($bakerModel);
        $bakerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->baker);
        $this->imageProcessor->expects($this->once())
            ->method('save')
            ->with($this->baker, BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->baker)
            ->willReturn($bakerAttributesMetaData);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method("remove")
            ->with($bakerId);
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(7);
        $address->expects($this->once())
            ->method('setRegion')
            ->with($region);
        $bakerAttributesMetaData->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn([$address]);
        $bakerAttributesMetaData->expects($this->at(1))
            ->method('setAddresses')
            ->with([]);
        $bakerAttributesMetaData->expects($this->at(2))
            ->method('setAddresses')
            ->with([$address]);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($bakerAttributesMetaData, [], \CND\Baker\Api\Data\BakerInterface::class)
            ->willReturn(['bakerData']);
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['bakerData']])
            ->willReturn($bakerModel);
        $bakerModel->expects($this->once())
            ->method('getStoreId')
            ->willReturn(null);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $bakerModel->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $bakerModel->expects($this->once())
            ->method('setId')
            ->with($bakerId);
        $bakerModel->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn(null);
        $bakerModel->expects($this->once())
            ->method('setAttributeSetId')
            ->with(\CND\Baker\Api\BakerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
        $bakerAttributesMetaData->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($bakerSecureData);
        $bakerSecureData->expects($this->once())
            ->method('getRpToken')
            ->willReturn('rpToken');
        $bakerSecureData->expects($this->once())
            ->method('getRpTokenCreatedAt')
            ->willReturn('rpTokenCreatedAt');
        $bakerSecureData->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn('passwordHash');
        $bakerSecureData->expects($this->once())
            ->method('getFailuresNum')
            ->willReturn('failuresNum');
        $bakerSecureData->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn('firstFailure');
        $bakerSecureData->expects($this->once())
            ->method('getLockExpires')
            ->willReturn('lockExpires');

        $bakerModel->expects($this->once())
            ->method('setRpToken')
            ->willReturnMap([
                ['rpToken', $bakerModel],
                [null, $bakerModel],
            ]);
        $bakerModel->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnMap([
                ['rpTokenCreatedAt', $bakerModel],
                [null, $bakerModel],
            ]);

        $bakerModel->expects($this->once())
            ->method('setPasswordHash')
            ->with('passwordHash');
        $bakerModel->expects($this->once())
            ->method('setFailuresNum')
            ->with('failuresNum');
        $bakerModel->expects($this->once())
            ->method('setFirstFailure')
            ->with('firstFailure');
        $bakerModel->expects($this->once())
            ->method('setLockExpires')
            ->with('lockExpires');
        $bakerModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $bakerModel->expects($this->once())
            ->method('save');
        $this->bakerRegistry->expects($this->once())
            ->method('push')
            ->with($bakerModel);
        $this->baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address, $address2]);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address);
        $bakerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $bakerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($bakerModel);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'baker_save_after_data_object',
                ['baker_data_object' => $this->baker, 'orig_baker_data_object' => $origBaker]
            );

        $this->model->save($this->baker);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveWithPasswordHash()
    {
        $bakerId = 1;
        $storeId = 2;
        $passwordHash = 'ukfa4sdfa56s5df02asdf4rt';

        $bakerSecureData = $this->createPartialMock(\CND\Baker\Model\Data\BakerSecure::class, [
                'getRpToken',
                'getRpTokenCreatedAt',
                'getPasswordHash',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
            ]);
        $region = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\RegionInterface::class,
            [],
            '',
            false
        );
        $address = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setBakerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $address2 = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setBakerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );

        $origBaker = $this->baker;

        $this->baker->expects($this->atLeastOnce())
            ->method('__toArray')
            ->willReturn(['default_billing', 'default_shipping']);

        $bakerModel = $this->createPartialMock(\CND\Baker\Model\Baker::class, [
                'getId',
                'setId',
                'setStoreId',
                'getStoreId',
                'getAttributeSetId',
                'setAttributeSetId',
                'setRpToken',
                'setRpTokenCreatedAt',
                'getDataModel',
                'setPasswordHash',
                'save',
            ]);
        $bakerAttributesMetaData = $this->getMockForAbstractClass(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getId',
                'getEmail',
                'getWebsiteId',
                'getAddresses',
                'setAddresses'
            ]
        );
        $bakerModel->expects($this->atLeastOnce())
            ->method('setRpToken')
            ->with(null);
        $bakerModel->expects($this->atLeastOnce())
            ->method('setRpTokenCreatedAt')
            ->with(null);
        $bakerModel->expects($this->atLeastOnce())
            ->method('setPasswordHash')
            ->with($passwordHash);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($bakerId);

        $this->bakerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($bakerSecureData);
        $bakerSecureData->expects($this->never())
            ->method('getRpToken')
            ->willReturn('rpToken');
        $bakerSecureData->expects($this->never())
            ->method('getRpTokenCreatedAt')
            ->willReturn('rpTokenCreatedAt');
        $bakerSecureData->expects($this->never())
            ->method('getPasswordHash')
            ->willReturn('passwordHash');
        $bakerSecureData->expects($this->once())
            ->method('getFailuresNum')
            ->willReturn('failuresNum');
        $bakerSecureData->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn('firstFailure');
        $bakerSecureData->expects($this->once())
            ->method('getLockExpires')
            ->willReturn('lockExpires');

        $this->baker->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($bakerModel);
        $bakerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->baker);
        $this->imageProcessor->expects($this->once())
            ->method('save')
            ->with($this->baker, BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->baker)
            ->willReturn($bakerAttributesMetaData);
        $address->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(7);
        $address->expects($this->once())
            ->method('setRegion')
            ->with($region);
        $bakerAttributesMetaData->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address]);
        $bakerAttributesMetaData->expects($this->at(1))
            ->method('setAddresses')
            ->with([]);
        $bakerAttributesMetaData->expects($this->at(2))
            ->method('setAddresses')
            ->with([$address]);
        $bakerAttributesMetaData
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($bakerAttributesMetaData, [], \CND\Baker\Api\Data\BakerInterface::class)
            ->willReturn(['bakerData']);
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['bakerData']])
            ->willReturn($bakerModel);
        $bakerModel->expects($this->once())
            ->method('getStoreId')
            ->willReturn(null);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $bakerModel->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $bakerModel->expects($this->once())
            ->method('setId')
            ->with($bakerId);
        $bakerModel->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn(null);
        $bakerModel->expects($this->once())
            ->method('setAttributeSetId')
            ->with(\CND\Baker\Api\BakerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
        $bakerModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($bakerId);
        $bakerModel->expects($this->once())
            ->method('save');
        $this->bakerRegistry->expects($this->once())
            ->method('push')
            ->with($bakerModel);
        $this->baker->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address, $address2]);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address);
        $bakerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $bakerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->bakerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($bakerModel);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'baker_save_after_data_object',
                ['baker_data_object' => $this->baker, 'orig_baker_data_object' => $origBaker]
            );

        $this->model->save($this->baker, $passwordHash);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $collection = $this->createMock(\CND\Baker\Model\ResourceModel\Baker\Collection::class);
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
        $bakerModel = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->setMethods(
                [
                    'getId',
                    'setId',
                    'setStoreId',
                    'getStoreId',
                    'getAttributeSetId',
                    'setAttributeSetId',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                    'getDataModel',
                    'setPasswordHash',
                    'getCollection'
                ]
            )
            ->setMockClassName('bakerModel')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class,
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
        $this->bakerFactory->expects($this->once())
            ->method('create')
            ->willReturn($bakerModel);
        $bakerModel->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, \CND\Baker\Api\Data\BakerInterface::class);
        $this->bakerMetadata->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn([$metadata]);
        $metadata->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('attribute-code');
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('attribute-code');
        $collection->expects($this->once())
            ->method('addNameToSelect');
        $collection->expects($this->at(2))
            ->method('joinAttribute')
            ->with('billing_postcode', 'baker_address/postcode', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(3))
            ->method('joinAttribute')
            ->with('billing_city', 'baker_address/city', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(4))
            ->method('joinAttribute')
            ->with('billing_telephone', 'baker_address/telephone', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(5))
            ->method('joinAttribute')
            ->with('billing_region', 'baker_address/region', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(6))
            ->method('joinAttribute')
            ->with('billing_country_id', 'baker_address/country_id', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(7))
            ->method('joinAttribute')
            ->with('company', 'baker_address/company', 'default_billing', null, 'left')
            ->willReturnSelf();
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $collection->expects($this->once())
            ->method('getSize')
            ->willReturn(23);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(23);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$bakerModel]));
        $bakerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->baker);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->baker]);

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testDeleteById()
    {
        $bakerId = 14;
        $bakerModel = $this->createPartialMock(\CND\Baker\Model\Baker::class, ['delete']);
        $this->bakerRegistry
            ->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($bakerModel);
        $bakerModel->expects($this->once())
            ->method('delete');
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($bakerId);

        $this->assertTrue($this->model->deleteById($bakerId));
    }

    public function testDelete()
    {
        $bakerId = 14;
        $bakerModel = $this->createPartialMock(\CND\Baker\Model\Baker::class, ['delete']);

        $this->baker->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $this->bakerRegistry
            ->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($bakerModel);
        $bakerModel->expects($this->once())
            ->method('delete');
        $this->bakerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($bakerId);
        $this->notificationStorage->expects($this->atLeastOnce())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $bakerId);

        $this->assertTrue($this->model->delete($this->baker));
    }
}
