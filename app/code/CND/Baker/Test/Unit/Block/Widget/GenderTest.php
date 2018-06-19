<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Block\Widget;

use CND\Baker\Block\Widget\Gender;
use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GenderTest extends \PHPUnit\Framework\TestCase
{
    /** Constants used in the unit tests */
    const CUSTOMER_ENTITY_TYPE = 'baker';

    const GENDER_ATTRIBUTE_CODE = 'gender';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Api\BakerMetadataInterface
     */
    private $bakerMetadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Api\Data\AttributeMetadataInterface */
    private $attribute;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Session */
    private $bakerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Api\BakerRepositoryInterface */
    private $bakerRepository;

    /** @var Gender */
    private $block;

    protected function setUp()
    {
        $this->attribute = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->bakerMetadata = $this->getMockBuilder(\CND\Baker\Api\BakerMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->bakerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->with(self::GENDER_ATTRIBUTE_CODE)
            ->will($this->returnValue($this->attribute));

        $this->bakerRepository = $this
            ->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->bakerSession = $this->createMock(\CND\Baker\Model\Session::class);

        $this->block = new \CND\Baker\Block\Widget\Gender(
            $this->createMock(\Magento\Framework\View\Element\Template\Context::class),
            $this->createMock(\CND\Baker\Helper\Address::class),
            $this->bakerMetadata,
            $this->bakerRepository,
            $this->bakerSession
        );
    }

    /**
     * Test the Gender::isEnabled() method.
     *
     * @param bool $isVisible Determines whether the 'gender' attribute is visible or enabled
     * @param bool $expectedValue The value we expect from Gender::isEnabled()
     * @return void
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isVisible, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isVisible')->will($this->returnValue($isVisible));
        $this->assertSame($expectedValue, $this->block->isEnabled());
    }

    /**
     * The testIsEnabled data provider.
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [[true, true], [false, false]];
    }

    public function testIsEnabledWithException()
    {
        $this->bakerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->throwException(new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            ))
        );
        $this->assertSame(false, $this->block->isEnabled());
    }

    /**
     * Test the Gender::isRequired() method.
     *
     * @param bool $isRequired Determines whether the 'gender' attribute is required
     * @param bool $expectedValue The value we expect from Gender::isRequired()
     * @return void
     *
     * @dataProvider isRequiredDataProvider
     */
    public function testIsRequired($isRequired, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isRequired')->will($this->returnValue($isRequired));
        $this->assertSame($expectedValue, $this->block->isRequired());
    }

    /**
     * The testIsRequired data provider.
     * @return array
     */
    public function isRequiredDataProvider()
    {
        return [[true, true], [false, false]];
    }

    public function testIsRequiredWithException()
    {
        $this->bakerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->throwException(new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            ))
        );
        $this->assertSame(false, $this->block->isRequired());
    }

    /**
     * Test the Gender::getBaker() method.
     * @return void
     */
    public function testGetBaker()
    {
        $bakerData = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();
        $this->bakerSession->expects($this->once())->method('getBakerId')->will($this->returnValue(1));
        $this->bakerRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->will($this->returnValue($bakerData));

        $baker = $this->block->getBaker();
        $this->assertSame($bakerData, $baker);
    }

    /**
     * Test the Gender::getGenderOptions() method.
     * @return void
     */
    public function testGetGenderOptions()
    {
        $options = [
            ['label' => __('Male'), 'value' => 'M'],
            ['label' => __('Female'), 'value' => 'F'],
            ['label' => __('Not Specified'), 'value' => 'NA']
        ];

        $this->attribute->expects($this->once())->method('getOptions')->will($this->returnValue($options));
        $this->assertSame($options, $this->block->getGenderOptions());
    }
}
