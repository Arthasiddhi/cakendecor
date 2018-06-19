<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Adminhtml\Edit\Tab\View;

use CND\Baker\Block\Adminhtml\Edit\Tab\View\PersonalInfo;
use Magento\Framework\Stdlib\DateTime;

/**
 * Baker personal information template block test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $defaultTimezone = 'America/Los_Angeles';

    /**
     * @var string
     */
    protected $pathToDefaultTimezone = 'path/to/default/timezone';

    /**
     * @var PersonalInfo
     */
    protected $block;

    /**
     * @var \CND\Baker\Model\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerLog;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\Baker
     */
    protected $bakerModel;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $baker = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $baker->expects($this->any())->method('getId')->willReturn(1);
        $baker->expects($this->any())->method('getStoreId')->willReturn(1);

        $bakerDataFactory = $this->createPartialMock(
            \CND\Baker\Api\Data\BakerInterfaceFactory::class,
            ['create']
        );
        $bakerDataFactory->expects($this->any())->method('create')->willReturn($baker);

        $backendSession = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['getBakerData']);
        $backendSession->expects($this->any())->method('getBakerData')->willReturn(['account' => []]);

        $this->bakerLog = $this->createPartialMock(
            \CND\Baker\Model\Log::class,
            ['getLastLoginAt', 'getLastVisitAt', 'getLastLogoutAt', 'loadByBaker']
        );
        $this->bakerLog->expects($this->any())->method('loadByBaker')->willReturnSelf();

        $bakerLogger = $this->createPartialMock(\CND\Baker\Model\Logger::class, ['get']);
        $bakerLogger->expects($this->any())->method('get')->willReturn($this->bakerLog);

        $dateTime = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, ['now']);
        $dateTime->expects($this->any())->method('now')->willReturn('2015-03-04 12:00:00');

        $this->localeDate = $this->createPartialMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            ['scopeDate', 'formatDateTime', 'getDefaultTimezonePath']
        );
        $this->localeDate
            ->expects($this->any())
            ->method('getDefaultTimezonePath')
            ->willReturn($this->pathToDefaultTimezone);

        $this->scopeConfig = $this->createPartialMock(\Magento\Framework\App\Config::class, ['getValue']);
        $this->bakerRegistry = $this->createPartialMock(
            \CND\Baker\Model\BakerRegistry::class,
            ['retrieve']
        );
        $this->bakerModel = $this->createPartialMock(\CND\Baker\Model\Baker::class, ['isBakerLocked']);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManagerHelper->getObject(
            \CND\Baker\Block\Adminhtml\Edit\Tab\View\PersonalInfo::class,
            [
                'bakerDataFactory' => $bakerDataFactory,
                'dateTime' => $dateTime,
                'bakerLogger' => $bakerLogger,
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig,
                'backendSession' => $backendSession,
            ]
        );
        $this->block->setBakerRegistry($this->bakerRegistry);
    }

    /**
     * @return void
     */
    public function testGetStoreLastLoginDateTimezone()
    {
        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                $this->pathToDefaultTimezone,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($this->defaultTimezone);

        $this->assertEquals($this->defaultTimezone, $this->block->getStoreLastLoginDateTimezone());
    }

    /**
     * @param string $status
     * @param string|null $lastLoginAt
     * @param string|null $lastVisitAt
     * @param string|null $lastLogoutAt
     * @return void
     * @dataProvider getCurrentStatusDataProvider
     */
    public function testGetCurrentStatus($status, $lastLoginAt, $lastVisitAt, $lastLogoutAt)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                'baker/online_bakers/online_minutes_interval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(240); //TODO: it's value mocked because unit tests run data providers before all testsuite

        $this->bakerLog->expects($this->any())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->bakerLog->expects($this->any())->method('getLastVisitAt')->willReturn($lastVisitAt);
        $this->bakerLog->expects($this->any())->method('getLastLogoutAt')->willReturn($lastLogoutAt);

        $this->assertEquals($status, (string) $this->block->getCurrentStatus());
    }

    /**
     * @return array
     */
    public function getCurrentStatusDataProvider()
    {
        return [
            ['Offline', null, null, null],
            ['Offline', '2015-03-04 11:00:00', null, '2015-03-04 12:00:00'],
            ['Offline', '2015-03-04 11:00:00', '2015-03-04 11:40:00', null],
            ['Online', '2015-03-04 11:00:00', (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT), null]
        ];
    }

    /**
     * @param string $result
     * @param string|null $lastLoginAt
     * @dataProvider getLastLoginDateDataProvider
     * @return void
     */
    public function testGetLastLoginDate($result, $lastLoginAt)
    {
        $this->bakerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->localeDate->expects($this->any())->method('formatDateTime')->willReturn($lastLoginAt);

        $this->assertEquals($result, $this->block->getLastLoginDate());
    }

    /**
     * @return array
     */
    public function getLastLoginDateDataProvider()
    {
        return [
            ['2015-03-04 12:00:00', '2015-03-04 12:00:00'],
            ['Never', null]
        ];
    }

    /**
     * @param string $result
     * @param string|null $lastLoginAt
     * @dataProvider getStoreLastLoginDateDataProvider
     * @return void
     */
    public function testGetStoreLastLoginDate($result, $lastLoginAt)
    {
        $this->bakerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);

        $this->localeDate->expects($this->any())->method('scopeDate')->will($this->returnValue($lastLoginAt));
        $this->localeDate->expects($this->any())->method('formatDateTime')->willReturn($lastLoginAt);

        $this->assertEquals($result, $this->block->getStoreLastLoginDate());
    }

    /**
     * @return array
     */
    public function getStoreLastLoginDateDataProvider()
    {
        return [
            ['2015-03-04 12:00:00', '2015-03-04 12:00:00'],
            ['Never', null]
        ];
    }

    /**
     * @param string $expectedResult
     * @param bool $value
     * @dataProvider getAccountLockDataProvider
     * @return void
     */
    public function testGetAccountLock($expectedResult, $value)
    {
        $this->bakerRegistry->expects($this->once())->method('retrieve')->willReturn($this->bakerModel);
        $this->bakerModel->expects($this->once())->method('isBakerLocked')->willReturn($value);
        $expectedResult =  new \Magento\Framework\Phrase($expectedResult);
        $this->assertEquals($expectedResult, $this->block->getAccountLock());
    }

    /**
     * @return array
     */
    public function getAccountLockDataProvider()
    {
        return [
            ['result' => 'Locked', 'expectedValue' => true],
            ['result' => 'Unlocked', 'expectedValue' => false]
        ];
    }
}
