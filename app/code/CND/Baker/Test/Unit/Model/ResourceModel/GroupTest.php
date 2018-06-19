<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Model\ResourceModel\Group */
    protected $groupResourceModel;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \CND\Baker\Model\Vat|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerVat;

    /** @var \CND\Baker\Model\Group|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $bakersFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupManagement;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $relationProcessorMock;

    /**
     * @var Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snapshotMock;

    /**
     * Setting up dependencies.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->bakerVat = $this->createMock(\CND\Baker\Model\Vat::class);
        $this->bakersFactory = $this->createPartialMock(\CND\Baker\Model\ResourceModel\Baker\CollectionFactory::class, ['create']);
        $this->groupManagement = $this->createPartialMock(\CND\Baker\Api\GroupManagementInterface::class, ['getDefaultGroup', 'getNotLoggedInGroup', 'isReadOnly', 'getLoggedInGroups', 'getAllBakersGroup']);

        $this->groupModel = $this->createMock(\CND\Baker\Model\Group::class);

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->relationProcessorMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class);

        $this->snapshotMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class);

        $transactionManagerMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class
        );
        $transactionManagerMock->expects($this->any())
            ->method('start')
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            \CND\Baker\Model\ResourceModel\Group::class,
            [
                'context' => $contextMock,
                'groupManagement' => $this->groupManagement,
                'bakersFactory' => $this->bakersFactory,
                'entitySnapshot' => $this->snapshotMock
            ]
        );
    }

    /**
     * Test for save() method when we try to save entity with system's reserved ID.
     * 
     * @return void
     */
    public function testSaveWithReservedId()
    {
        $expectedId = 55;
        $this->snapshotMock->expects($this->once())->method('isModified')->willReturn(true);
        $this->snapshotMock->expects($this->once())->method('registerSnapshot')->willReturnSelf();

        $this->groupModel->expects($this->any())->method('getId')
            ->willReturn(\CND\Baker\Model\Group::CUST_GROUP_ALL);
        $this->groupModel->expects($this->any())->method('getData')
            ->willReturn([]);
        $this->groupModel->expects($this->any())->method('isSaveAllowed')
            ->willReturn(true);
        $this->groupModel->expects($this->any())->method('getStoredData')
            ->willReturn([]);
        $this->groupModel->expects($this->once())->method('setId')
            ->with($expectedId);

        $dbAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'lastInsertId',
                    'describeTable',
                    'update',
                    'select'
                ]
            )
            ->getMockForAbstractClass();
        $dbAdapter->expects($this->any())->method('describeTable')->willReturn([]);
        $dbAdapter->expects($this->any())->method('update')->willReturnSelf();
        $dbAdapter->expects($this->once())->method('lastInsertId')->willReturn($expectedId);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbAdapter->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $this->groupResourceModel->save($this->groupModel);
    }

    /**
     * Test for delete() method when we try to save entity with system's reserved ID.
     *
     * @return void
     */
    public function testDelete()
    {
        $dbAdapter = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($dbAdapter));

        $baker = $this->createPartialMock(\CND\Baker\Model\Baker::class, ['__wakeup', 'load', 'getId', 'getStoreId', 'setGroupId', 'save']);
        $bakerId = 1;
        $baker->expects($this->once())->method('getId')->will($this->returnValue($bakerId));
        $baker->expects($this->once())->method('load')->with($bakerId)->will($this->returnSelf());
        $defaultBakerGroup = $this->createPartialMock(\CND\Baker\Model\Group::class, ['getId']);
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')
            ->will($this->returnValue($defaultBakerGroup));
        $defaultBakerGroup->expects($this->once())->method('getId')
            ->will($this->returnValue(1));
        $baker->expects($this->once())->method('setGroupId')->with(1);
        $bakerCollection = $this->createMock(\CND\Baker\Model\ResourceModel\Baker\Collection::class);
        $bakerCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $bakerCollection->expects($this->once())->method('load')->will($this->returnValue([$baker]));
        $this->bakersFactory->expects($this->once())->method('create')
            ->will($this->returnValue($bakerCollection));

        $this->relationProcessorMock->expects($this->once())->method('delete');
        $this->groupModel->expects($this->any())->method('getData')->willReturn(['data' => 'value']);
        $this->groupResourceModel->delete($this->groupModel);
    }
}
