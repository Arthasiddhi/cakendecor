<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Account;

class BakerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Block\Account\Baker */
    private $block;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $httpContext;

    protected function setUp()
    {
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\CND\Baker\Block\Account\Baker::class, ['httpContext' => $this->httpContext]);
    }

    public function bakerLoggedInDataProvider()
    {
        return [
            [1, true],
            [0, false],
        ];
    }

    /**
     * @param $isLoggedIn
     * @param $result
     * @dataProvider bakerLoggedInDataProvider
     */
    public function testBakerLoggedIn($isLoggedIn, $result)
    {
        $this->httpContext->expects($this->once())->method('getValue')
            ->with(\CND\Baker\Model\Context::CONTEXT_AUTH)
            ->willReturn($isLoggedIn);

        $this->assertSame($result, $this->block->bakerLoggedIn($isLoggedIn));
    }
}
