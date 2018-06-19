<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

/**
 * Baker log model test.
 */
class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Baker log model.
     *
     * @var \CND\Baker\Model\Log
     */
    protected $log;

    /**
     * @var array
     */
    protected $logData = [
        'baker_id' => 369,
        'last_login_at' => '2015-03-04 12:00:00',
        'last_visit_at' => '2015-03-04 12:01:00',
        'last_logout_at' => '2015-03-04 12:05:00',
    ];

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->log = $objectManagerHelper->getObject(
            \CND\Baker\Model\Log::class,
            [
                'bakerId' => $this->logData['baker_id'],
                'lastLoginAt' => $this->logData['last_login_at'],
                'lastVisitAt' => $this->logData['last_visit_at'],
                'lastLogoutAt' => $this->logData['last_logout_at']
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetBakerId()
    {
        $this->assertEquals($this->logData['baker_id'], $this->log->getBakerId());
    }

    /**
     * @return void
     */
    public function testGetLastLoginAt()
    {
        $this->assertEquals($this->logData['last_login_at'], $this->log->getLastLoginAt());
    }

    /**
     * @return void
     */
    public function testGetLastVisitAt()
    {
        $this->assertEquals($this->logData['last_visit_at'], $this->log->getLastVisitAt());
    }

    /**
     * @return void
     */
    public function testGetLastLogoutAt()
    {
        $this->assertEquals($this->logData['last_logout_at'], $this->log->getLastLogoutAt());
    }
}
