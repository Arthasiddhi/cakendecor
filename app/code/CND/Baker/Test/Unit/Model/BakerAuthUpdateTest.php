<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Model\BakerAuthUpdate;

/**
 * Class BakerAuthUpdateTest
 */
class BakerAuthUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BakerAuthUpdate
     */
    protected $model;

    /**
     * @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerResourceModel;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->bakerRegistry =
            $this->createMock(\CND\Baker\Model\BakerRegistry::class);
        $this->bakerResourceModel =
            $this->createMock(\CND\Baker\Model\ResourceModel\Baker::class);

        $this->model = $this->objectManager->getObject(
            \CND\Baker\Model\BakerAuthUpdate::class,
            [
                'bakerRegistry' => $this->bakerRegistry,
                'bakerResourceModel' => $this->bakerResourceModel,
            ]
        );
    }

    /**
     * test SaveAuth
     */
    public function testSaveAuth()
    {
        $bakerId = 1;

        $bakerSecureMock = $this->createMock(\CND\Baker\Model\Data\BakerSecure::class);

        $dbAdapter = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->bakerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->willReturn($bakerSecureMock);

        $bakerSecureMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturn(1);

        $this->bakerResourceModel->expects($this->any())
            ->method('getConnection')
            ->willReturn($dbAdapter);

        $this->bakerResourceModel->expects($this->any())
            ->method('getTable')
            ->willReturn('baker_entity');

        $dbAdapter->expects($this->any())
            ->method('update')
            ->with(
                'baker_entity',
                [
                    'failures_num' => 1,
                    'first_failure' => 1,
                    'lock_expires' => 1
                ]
            );

        $dbAdapter->expects($this->any())
            ->method('quoteInto')
            ->with(
                'entity_id = ?',
                $bakerId
            );

        $this->model->saveAuth($bakerId);
    }
}
