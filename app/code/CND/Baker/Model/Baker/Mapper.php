<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Model\Baker;

use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Convert\ConvertArray;

/**
 * Class Mapper converts Address Service Data Object to an array
 */
class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert address data object to a flat array
     *
     * @param BakerInterface $baker
     * @return array
     */
    public function toFlatArray(BakerInterface $baker)
    {
        $flatArray = $this->extensibleDataObjectConverter->toNestedArray($baker, [], \CND\Baker\Api\Data\BakerInterface::class);
        unset($flatArray["addresses"]);
        return ConvertArray::toFlatArray($flatArray);
    }
}
