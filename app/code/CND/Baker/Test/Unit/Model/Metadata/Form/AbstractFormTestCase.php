<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Metadata\Form;

/** Test CND\Baker\Model\Metadata\Form\Multiline */
abstract class AbstractFormTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $localeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ResolverInterface */
    protected $localeResolverMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Psr\Log\LoggerInterface */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Api\Data\AttributeMetadataInterface */
    protected $attributeMetadataMock;

    protected function setUp()
    {
        $this->localeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->attributeMetadataMock = $this->createMock(\CND\Baker\Api\Data\AttributeMetadataInterface::class);
    }
}
