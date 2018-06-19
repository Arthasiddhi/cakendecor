<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

/**
 * Baker log data logger test.
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Baker log data logger.
     *
     * @var \CND\Baker\Model\Logger
     */
    protected $logger;

    /**
     * @var \CND\Baker\Model\LogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logFactory;

    /**
     * Resource instance.
     *
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * DB connection instance.
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->connection = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'insertOnDuplicate', 'fetchRow']
        );
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->logFactory = $this->createPartialMock(\CND\Baker\Model\LogFactory::class, ['create']);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->logger = $objectManagerHelper->getObject(
            \CND\Baker\Model\Logger::class,
            [
                'resource' => $this->resource,
                'logFactory' => $this->logFactory
            ]
        );
    }

    /**
     * @param int $bakerId
     * @param array $data
     * @dataProvider logDataProvider
     * @return void
     */
    public function testLog($bakerId, $data)
    {
        $tableName = 'baker_log_table_name';
        $data = array_filter($data);

        if (!$data) {
            $this->expectException('\InvalidArgumentException');
            $this->expectExceptionMessage('Log data is empty');
            $this->logger->log($bakerId, $data);
            return;
        }

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('baker_log')
            ->willReturn($tableName);
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($tableName, array_merge(['baker_id' => $bakerId], $data), array_keys($data));

        $this->assertEquals($this->logger, $this->logger->log($bakerId, $data));
    }

    /**
     * @return array
     */
    public function logDataProvider()
    {
        return [
            [235, ['last_login_at' => '2015-03-04 12:00:00']],
            [235, ['last_login_at' => null]],
        ];
    }

    /**
     * @param int $bakerId
     * @param array $data
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGet($bakerId, $data)
    {
        $logArguments = [
            'bakerId' => $data['baker_id'],
            'lastLoginAt' => $data['last_login_at'],
            'lastLogoutAt' => $data['last_logout_at'],
            'lastVisitAt' => $data['last_visit_at']
        ];

        $select = $this->createMock(\Magento\Framework\DB\Select::class);

        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('order')->willReturnSelf();
        $select->expects($this->any())->method('limit')->willReturnSelf();

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->any())
            ->method('fetchRow')
            ->with($select)
            ->willReturn($data);

        $log = $this->getMockBuilder(\CND\Baker\Model\Log::class)
            ->setConstructorArgs($logArguments)
            ->getMock();

        $this->logFactory->expects($this->any())
            ->method('create')
            ->with($logArguments)
            ->willReturn($log);

        $this->assertEquals($log, $this->logger->get($bakerId));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [
                235,
                [
                    'baker_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => '2015-03-04 12:05:00',
                ]
            ],
            [
                235,
                [
                    'baker_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => null,
                ]
            ],
            [
                235,
                [
                    'baker_id' => null,
                    'last_login_at' => null,
                    'last_visit_at' => null,
                    'last_logout_at' => null,
                ]
            ],
        ];
    }
}
