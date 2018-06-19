<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\ResourceModel\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddressTest
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \CND\Baker\Model\BakerFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $bakerFactoryMock;

    /** @var  \CND\Baker\Model\ResourceModel\Address\Relation */
    protected $relation;

    protected function setUp()
    {
        $this->bakerFactoryMock = $this->createPartialMock(
            \CND\Baker\Model\BakerFactory::class,
            ['create']
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            \CND\Baker\Model\ResourceModel\Address\Relation::class,
            [
                'bakerFactory' => $this->bakerFactoryMock
            ]
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider getRelationDataProvider
     */
    public function testProcessRelation($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        $addressModel = $this->createPartialMock(\Magento\Framework\Model\AbstractModel::class, [
                '__wakeup',
                'getId',
                'getEntityTypeId',
                'getIsDefaultBilling',
                'getIsDefaultShipping',
                'hasDataChanges',
                'validateBeforeSave',
                'beforeSave',
                'afterSave',
                'isSaveAllowed',
                'getIsBakerSaveTransaction'
            ]);
        $bakerModel = $this->createPartialMock(
            \CND\Baker\Model\Baker::class,
            ['__wakeup', 'setDefaultBilling', 'setDefaultShipping', 'save', 'load', 'getResource', 'getId']
        );
        $bakerResource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['getConnection', 'getTable']
        );
        $connectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['update', 'quoteInto']
        );
        $bakerModel->expects($this->any())->method('getResource')->willReturn($bakerResource);
        $addressModel->expects($this->any())->method('getId')->willReturn($addressId);
        $addressModel->expects($this->any())->method('getIsDefaultShipping')->willReturn($isDefaultShipping);
        $addressModel->expects($this->any())->method('getIsDefaultBilling')->willReturn($isDefaultBilling);
        $addressModel->expects($this->any())->method('getIsBakerSaveTransaction')->willReturn(false);

        $bakerModel->expects($this->any())
             ->method('load')
             ->willReturnSelf();

        $this->bakerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($bakerModel);
        if ($addressId && ($isDefaultBilling || $isDefaultShipping)) {
            $bakerId = 1;
            $bakerResource->expects($this->exactly(2))->method('getConnection')->willReturn($connectionMock);
            $bakerModel->expects($this->any())->method('getId')->willReturn(1);
            $conditionSql = "entity_id = $bakerId";
            $connectionMock->expects($this->once())->method('quoteInto')
                ->with('entity_id = ?', $bakerId)
                ->willReturn($conditionSql);
            $bakerResource->expects($this->once())->method('getTable')
                ->with('baker_entity')
                ->willReturn('baker_entity');
            $toUpdate = [];
            if ($isDefaultBilling) {
                $toUpdate['default_billing'] = $addressId;
            }
            if ($isDefaultShipping) {
                $toUpdate['default_shipping'] = $addressId;
            }
            $connectionMock->expects($this->once())->method('update')->with(
                'baker_entity',
                $toUpdate,
                $conditionSql
            );
        }
        $result = $this->relation->processRelation($addressModel);
        $this->assertNull($result);
    }

    /**
     * Data provider for processRelation method
     *
     * @return array
     */
    public function getRelationDataProvider()
    {
        return [
            [null, true, true],
            [1, true, true],
            [1, true, false],
            [1, false, true],
            [1, false, false],
        ];
    }
}
