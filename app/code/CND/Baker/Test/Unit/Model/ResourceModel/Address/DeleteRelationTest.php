<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\ResourceModel\Address;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddressTest
 */
class DeleteRelationTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \CND\Baker\Model\ResourceModel\Address\DeleteRelation */
    protected $relation;

    protected function setUp()
    {
        $this->bakerFactoryMock = $this->createPartialMock(
            \CND\Baker\Model\BakerFactory::class,
            ['create']
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            \CND\Baker\Model\ResourceModel\Address\DeleteRelation::class
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider getRelationDataProvider
     */
    public function testDeleteRelation($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        /** @var AbstractModel | \PHPUnit_Framework_MockObject_MockObject $addressModel  */
        $addressModel = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsBakerSaveTransaction', 'getId', 'getResource'])
            ->getMock();
        /** @var \CND\Baker\Model\Baker | \PHPUnit_Framework_MockObject_MockObject $bakerModel */
        $bakerModel = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultBilling', 'getDefaultShipping', 'getId'])
            ->getMock();

        $addressResource = $this->getMockForAbstractClass(
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
        $addressModel->expects($this->any())->method('getResource')->willReturn($addressResource);
        $addressModel->expects($this->any())->method('getId')->willReturn($addressId);
        $addressModel->expects($this->any())->method('getIsBakerSaveTransaction')->willReturn(false);

        $bakerModel->expects($this->any())->method("getDefaultBilling")->willReturn($isDefaultBilling);
        $bakerModel->expects($this->any())->method("getDefaultShipping")->willReturn($isDefaultShipping);

        if ($addressId && ($isDefaultBilling || $isDefaultShipping)) {
            $bakerId = 1;
            $addressResource->expects($this->exactly(2))->method('getConnection')->willReturn($connectionMock);
            $bakerModel->expects($this->any())->method('getId')->willReturn(1);
            $conditionSql = "entity_id = $bakerId";
            $connectionMock->expects($this->once())->method('quoteInto')
                ->with('entity_id = ?', $bakerId)
                ->willReturn($conditionSql);
            $addressResource->expects($this->once())->method('getTable')
                ->with('baker_entity')
                ->willReturn('baker_entity');
            $toUpdate = [];
            if ($isDefaultBilling) {
                $toUpdate['default_billing'] = null;
            }
            if ($isDefaultShipping) {
                $toUpdate['default_shipping'] = null;
            }
            $connectionMock->expects($this->once())->method('update')->with(
                'baker_entity',
                $toUpdate,
                $conditionSql
            );
        }
        $result = $this->relation->deleteRelation($addressModel, $bakerModel);
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
