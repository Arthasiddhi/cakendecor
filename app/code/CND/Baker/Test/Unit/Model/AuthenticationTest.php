<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use Magento\Backend\App\ConfigInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\Authentication;
use CND\Baker\Model\BakerRegistry;
use CND\Baker\Model\Data\BakerSecure;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthenticationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $backendConfigMock;

    /**
     * @var \CND\Baker\Model\BakerRegistry | \PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerRegistryMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;

    /**
     * @var BakerRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerRepositoryMock;

    /**
     * @var \CND\Baker\Model\Data\BakerSecure | \PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerSecureMock;

    /**
     * @var \CND\Baker\Model\Authentication
     */
    private $authentication;

    /**
     * @var DateTime
     */
    private $dateTimeMock;

    /**
     * @var \CND\Baker\Model\BakerAuthUpdate | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAuthUpdate;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->backendConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->bakerRegistryMock = $this->createPartialMock(
            BakerRegistry::class,
            ['retrieveSecureData', 'retrieve']
        );
        $this->bakerRepositoryMock = $this->getMockBuilder(BakerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock->expects($this->any())
            ->method('formatDate')
            ->willReturn('formattedDate');
        $this->bakerSecureMock = $this->createPartialMock(BakerSecure::class, [
                'getId',
                'getPasswordHash',
                'isBakerLocked',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
                'setFirstFailure',
                'setFailuresNum',
                'setLockExpires'
            ]);

        $this->bakerAuthUpdate = $this->getMockBuilder(\CND\Baker\Model\BakerAuthUpdate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authentication = $this->objectManager->getObject(
            Authentication::class,
            [
                'bakerRegistry' => $this->bakerRegistryMock,
                'backendConfig' => $this->backendConfigMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'encryptor' => $this->encryptorMock,
                'dateTime' => $this->dateTimeMock,
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->authentication,
            'bakerAuthUpdate',
            $this->bakerAuthUpdate
        );
    }

    public function testProcessAuthenticationFailureLockingIsDisabled()
    {
        $bakerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\CND\Baker\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                [\CND\Baker\Model\Authentication::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(0, 0);
        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);
        $this->authentication->processAuthenticationFailure($bakerId);
    }

    /**
     * @param int $failureNum
     * @param string $firstFailure
     * @param string $lockExpires
     * @param int $setFailureNumCallCtr
     * @param int $setFailureNumValue
     * @param int $setFirstFailureCallCtr
     * @param int $setFirstFailureValue
     * @param int $setLockExpiresCallCtr
     * @param int $setLockExpiresValue
     * @dataProvider processAuthenticationFailureDataProvider
     */
    public function testProcessAuthenticationFailureFirstAttempt(
        $failureNum,
        $firstFailure,
        $lockExpires,
        $setFailureNumCallCtr,
        $setFailureNumValue,
        $setFirstFailureCallCtr,
        $setLockExpiresCallCtr,
        $setLockExpiresValue
    ) {
        $bakerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\CND\Baker\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                [\CND\Baker\Model\Authentication::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(10, 5);

        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);
        $this->bakerAuthUpdate->expects($this->once())
            ->method('saveAuth')
            ->with($bakerId)
            ->willReturnSelf();

        $this->bakerSecureMock->expects($this->once())->method('getFailuresNum')->willReturn($failureNum);
        $this->bakerSecureMock->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn($firstFailure ? (new \DateTime())->modify($firstFailure)->format('Y-m-d H:i:s') : null);
        $this->bakerSecureMock->expects($this->once())
            ->method('getLockExpires')
            ->willReturn($lockExpires ? (new \DateTime())->modify($lockExpires)->format('Y-m-d H:i:s') : null);
        $this->bakerSecureMock->expects($this->exactly($setFirstFailureCallCtr))->method('setFirstFailure');
        $this->bakerSecureMock->expects($this->exactly($setFailureNumCallCtr))
            ->method('setFailuresNum')
            ->with($setFailureNumValue);
        $this->bakerSecureMock->expects($this->exactly($setLockExpiresCallCtr))
            ->method('setLockExpires')
            ->with($setLockExpiresValue);

        $this->authentication->processAuthenticationFailure($bakerId);
    }

    public function processAuthenticationFailureDataProvider()
    {
        return [
            'first attempt' => [0, null, null, 1, 1, 1, 1, null],
            'not locked' => [3, '-400 second', null, 1, 4, 0, 0, null],
            'lock expired' => [5, '-400 second', '-100 second', 1, 1, 1, 1, null],
            'max attempt' => [4, '-400 second', null, 1, 5, 0, 1, 'formattedDate'],
        ];
    }

    public function testUnlock()
    {
        $bakerId = 1;
        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);
        $this->bakerAuthUpdate->expects($this->once())
            ->method('saveAuth')
            ->with($bakerId)
            ->willReturnSelf();
        $this->bakerSecureMock->expects($this->once())->method('setFailuresNum')->with(0);
        $this->bakerSecureMock->expects($this->once())->method('setFirstFailure')->with(null);
        $this->bakerSecureMock->expects($this->once())->method('setLockExpires')->with(null);
        $this->authentication->unlock($bakerId);
    }

    /**
     * @return array
     */
    public function validatePasswordAndLockStatusDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return void
     */
    public function testIsLocked()
    {
        $bakerId = 7;

        $bakerModelMock = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerModelMock->expects($this->once())
            ->method('isBakerLocked');
        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($bakerId)
            ->willReturn($bakerModelMock);

        $this->authentication->isLocked($bakerId);
    }

    /**
     * @param bool $result
     * @dataProvider validateBakerPassword
     */
    public function testAuthenticate($result)
    {
        $bakerId = 7;
        $password = '1234567';
        $hash = '1b2af329dd0';

        $bakerMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $this->bakerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($bakerMock);

        $this->bakerSecureMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);

        $this->bakerSecureMock->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($hash);

        $this->bakerRegistryMock->expects($this->any())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);

        $this->encryptorMock->expects($this->once())
            ->method('validateHash')
            ->with($password, $hash)
            ->willReturn($result);

        if ($result) {
            $this->assertTrue($this->authentication->authenticate($bakerId, $password));
        } else {
            $this->backendConfigMock->expects($this->exactly(2))
                ->method('getValue')
                ->withConsecutive(
                    [\CND\Baker\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                    [\CND\Baker\Model\Authentication::MAX_FAILURES_PATH]
                )
                ->willReturnOnConsecutiveCalls(1, 1);
            $this->bakerSecureMock->expects($this->once())
                ->method('isBakerLocked')
                ->willReturn(false);

            $this->bakerRegistryMock->expects($this->once())
                ->method('retrieve')
                ->with($bakerId)
                ->willReturn($this->bakerSecureMock);

            $this->bakerAuthUpdate->expects($this->once())
                ->method('saveAuth')
                ->with($bakerId)
                ->willReturnSelf();

            $this->expectException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);
            $this->authentication->authenticate($bakerId, $password);
        }
    }

    /**
     * @return array
     */
    public function validateBakerPassword()
    {
        return [
            [true],
            [false],
        ];
    }
}
