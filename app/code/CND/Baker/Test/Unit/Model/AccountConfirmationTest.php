<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Model\AccountConfirmation;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountConfirmationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AccountConfirmation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $accountConfirmation;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    protected function setUp()
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->registry = $this->createMock(Registry::class);

        $this->accountConfirmation = new AccountConfirmation(
            $this->scopeConfig,
            $this->registry
        );
    }

    /**
     * @param $bakerId
     * @param $bakerEmail
     * @param $skipConfirmationIfEmail
     * @param $isConfirmationEnabled
     * @param $expected
     * @dataProvider dataProviderIsConfirmationRequired
     */
    public function testIsConfirmationRequired(
        $bakerId,
        $bakerEmail,
        $skipConfirmationIfEmail,
        $isConfirmationEnabled,
        $expected
    ) {
        $websiteId = 1;

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->accountConfirmation::XML_PATH_IS_CONFIRM,
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            )->willReturn($isConfirmationEnabled);

        $this->registry->expects($this->any())
            ->method('registry')
            ->with('skip_confirmation_if_email')
            ->willReturn($skipConfirmationIfEmail);

        self::assertEquals(
            $expected,
            $this->accountConfirmation->isConfirmationRequired($websiteId, $bakerId, $bakerEmail)
        );
    }

    /**
     * @return array
     */
    public function dataProviderIsConfirmationRequired()
    {
        return [
            [null, 'baker@example.com', null, true, true],
            [null, 'baker@example.com', null, false, false],
            [1, 'baker@example.com', 'baker@example.com', true, false],
            [1, 'baker@example.com', 'baker1@example.com', false, false],
            [1, 'baker@example.com', 'baker1@example.com', true, true],
        ];
    }
}
