<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Metadata;

use CND\Baker\Model\Attribute;
use CND\Baker\Model\AttributeMetadataDataProvider;
use CND\Baker\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeResolver
{
    /**
     * @var AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * Get attribute model by attribute data object
     *
     * @param string $entityType
     * @param AttributeMetadataInterface $attribute
     * @return Attribute
     * @throws NoSuchEntityException
     */
    public function getModelByAttribute($entityType, AttributeMetadataInterface $attribute)
    {
        /** @var Attribute $model */
        $model = $this->attributeMetadataDataProvider->getAttribute(
            $entityType,
            $attribute->getAttributeCode()
        );
        if ($model) {
            return $model;
        } else {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'entityType',
                        'fieldValue' => $entityType,
                        'field2Name' => 'attributeCode',
                        'field2Value' => $attribute->getAttributeCode()
                    ]
                )
            );
        }
    }
}
