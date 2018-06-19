<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests CND\Baker\Model\Authorization\BakerSessionUserContext
 */
class BakerSessionUserContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CND\Baker\Model\Authorization\BakerSessionUserContext
     */
    protected $bakerSessionUserContext;

    /**
     * @var \CND\Baker\Model\Session
     */
    protected $bakerSession;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->bakerSession = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->bakerSessionUserContext = $this->objectManager->getObject(
            \CND\Baker\Model\Authorization\BakerSessionUserContext::class,
            ['bakerSession' => $this->bakerSession]
        );
    }

    public function testGetUserIdExist()
    {
        $userId = 1;
        $this->setupUserId($userId);
        $this->assertEquals($userId, $this->bakerSessionUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $userId = null;
        $this->setupUserId($userId);
        $this->assertEquals($userId, $this->bakerSessionUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_CUSTOMER, $this->bakerSessionUserContext->getUserType());
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function setupUserId($userId)
    {
        $this->bakerSession->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($userId));
    }
}
