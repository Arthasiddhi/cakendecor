<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Adminhtml\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UnlockButtonTest
 * @package CND\Baker\Block\Adminhtml\Edit
 */
class UnlockButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistryMock;

    /**
     * @var  \Magento\Backend\Block\Widget\Context
     */
    protected $contextMock;

    /**
     * @var \CND\Baker\Model\Baker
     */
    protected $bakerModelMock;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryMock;

    /**
     * @var \CND\Baker\Block\Adminhtml\Edit\UnlockButton
     */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);
        $this->bakerRegistryMock = $this->createPartialMock(
            \CND\Baker\Model\BakerRegistry::class,
            ['retrieve']
        );
        $this->bakerModelMock = $this->createMock(\CND\Baker\Model\Baker::class);
        $this->registryMock = $this->createPartialMock(\Magento\Framework\Registry::class, ['registry']);

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->block = $objectManagerHelper->getObject(
            \CND\Baker\Block\Adminhtml\Edit\UnlockButton::class,
            [
                'context' => $this->contextMock,
                'bakerRegistry' => $this->bakerRegistryMock,
                'urlBuilder' => $this->urlBuilderMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @param array $result
     * @param bool $expectedValue
     * @dataProvider getButtonDataProvider
     */
    public function testGetButtonData($result, $expectedValue)
    {
        $this->registryMock->expects($this->any())->method('registry')->willReturn(1);
        $this->bakerRegistryMock->expects($this->once())->method('retrieve')->willReturn($this->bakerModelMock);
        $this->bakerModelMock->expects($this->once())->method('isBakerLocked')->willReturn($expectedValue);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturn('http://website.com/');

        $this->assertEquals($result, $this->block->getButtonData());
    }

    /**
     * @return array
     */
    public function getButtonDataProvider()
    {
        return [
            [
                'result' => [
                    'label' => new \Magento\Framework\Phrase('Unlock'),
                    'class' => 'unlock unlock-baker',
                    'on_click' => "location.href = 'http://website.com/';",
                    'sort_order' => 50,
                ],
                'expectedValue' => 'true'
            ],
            ['result' => [], 'expectedValue' => false]
        ];
    }
}
