<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Plugin\BakerRepository;

class TransactionWrapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\Plugin\BakerRepository\TransactionWrapper
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Model\ResourceModel\Baker
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Api\BakerRepositoryInterface
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \Closure
     */
    protected $rollbackClosureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerMock;

    /**
     * @var string
     */
    protected $passwordHash = true;

    const ERROR_MSG = "error occurred";

    protected function setUp()
    {
        $this->resourceMock = $this->createMock(\CND\Baker\Model\ResourceModel\Baker::class);
        $this->subjectMock = $this->createMock(\CND\Baker\Api\BakerRepositoryInterface::class);
        $this->bakerMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $bakerMock = $this->bakerMock;
        $this->closureMock = function () use ($bakerMock) {
            return $bakerMock;
        };
        $this->rollbackClosureMock = function () use ($bakerMock) {
            throw new \Exception(self::ERROR_MSG);
        };

        $this->model = new \CND\Baker\Model\Plugin\BakerRepository\TransactionWrapper($this->resourceMock);
    }

    public function testAroundSaveCommit()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('commit');

        $this->assertEquals(
            $this->bakerMock,
            $this->model->aroundSave($this->subjectMock, $this->closureMock, $this->bakerMock, $this->passwordHash)
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage error occurred
     */
    public function testAroundSaveRollBack()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('rollBack');

        $this->model->aroundSave(
            $this->subjectMock,
            $this->rollbackClosureMock,
            $this->bakerMock,
            $this->passwordHash
        );
    }
}
