<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model\Baker\Attribute\Backend;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\StringUtils;
use CND\Baker\Model\Baker\Attribute\Backend\Password;

class PasswordTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Password
     */
    protected $testable;

    protected function setUp()
    {
        $string = new StringUtils();
        $this->testable = new \CND\Baker\Model\Baker\Attribute\Backend\Password($string);
    }

    public function testValidatePositive()
    {
        $password = 'password';

        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getPasswordConfirm'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('getPasswordConfirm')->willReturn($password);

        $this->assertTrue($this->testable->validate($object));
    }

    public function passwordNegativeDataProvider()
    {
        return [
            'less-then-6-char' => ['less6'],
            'with-space-prefix' => [' normal_password'],
            'with-space-suffix' => ['normal_password '],
        ];
    }

    /**
     * @dataProvider passwordNegativeDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testBeforeSaveNegative($password)
    {
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';

        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'setPasswordHash', 'hashPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('hashPassword')->willReturn($passwordHash);
        $object->expects($this->once())->method('setPasswordHash')->with($passwordHash)->willReturnSelf();

        $this->testable->beforeSave($object);
    }

    /**
     * @return array
     */
    public function randomValuesProvider()
    {
        return [
            [false],
            [1],
            ["23"],
            [null],
            [""],
            [-1],
            [12.3],
            [true],
            [0],
        ];
    }

    /**
     * @dataProvider randomValuesProvider
     * @param mixed $randomValue
     */
    public function testBakerGetPasswordAndGetPasswordConfirmAlwaysReturnsAString($randomValue)
    {
        /** @var \CND\Baker\Model\Baker|\PHPUnit_Framework_MockObject_MockObject $baker */
        $baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $baker->expects($this->exactly(2))->method('getData')->willReturn($randomValue);

        $this->assertInternalType(
            'string',
            $baker->getPassword(),
            'Baker password should always return a string'
        );

        $this->assertInternalType(
            'string',
            $baker->getPasswordConfirm(),
            'Baker password-confirm should always return a string'
        );
    }
}
