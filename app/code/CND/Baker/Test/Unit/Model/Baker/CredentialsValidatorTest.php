<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Baker;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CredentialsValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \CND\Baker\Model\Baker\CredentialsValidator
     */
    private $object;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->object = $this->objectManagerHelper
            ->getObject(\CND\Baker\Model\Baker\CredentialsValidator::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Password cannot be the same as email address.
     */
    public function testCheckPasswordDifferentFromEmail()
    {
        $email = 'test1@example.com';
        $password = strtoupper($email); // for case-insensitive check

        $this->object->checkPasswordDifferentFromEmail($email, $password);
    }
}
