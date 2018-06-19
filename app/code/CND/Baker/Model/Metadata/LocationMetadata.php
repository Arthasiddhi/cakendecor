<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\Metadata;

use CND\Baker\Api\LocationMetadataInterface;
use CND\Baker\Model\AttributeMetadataConverter;
use CND\Baker\Model\AttributeMetadataDataProvider;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service to fetch customer location related custom attributes
 */
class LocationMetadata implements LocationMetadataInterface
{
    /**
     * @var array
     */
    private $locationDataObjectMethods;

    /**
     * @var AttributeMetadataConverter
     */
    private $attributeMetadataConverter;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @param AttributeMetadataConverter $attributeMetadataConverter
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        AttributeMetadataConverter $attributeMetadataConverter,
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->attributeMetadataConverter = $attributeMetadataConverter;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($formCode)
    {
        $attributes = [];
        $attributesFormCollection = $this->attributeMetadataDataProvider->loadAttributesCollection(
            LocationMetadataInterface::ENTITY_TYPE_LOCATION,
            $formCode
        );
        foreach ($attributesFormCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $this->attributeMetadataConverter
                ->createMetadataAttribute($attribute);
        }
        if (empty($attributes)) {
            throw NoSuchEntityException::singleField('formCode', $formCode);
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->attributeMetadataDataProvider
            ->getAttribute(LocationMetadataInterface::ENTITY_TYPE_LOCATION, $attributeCode);
        if ($attribute && ($attributeCode === 'id' || $attribute->getId() !== null)) {
            $attributeMetadata = $this->attributeMetadataConverter->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'entityType',
                        'fieldValue' => LocationMetadataInterface::ENTITY_TYPE_LOCATION,
                        'field2Name' => 'attributeCode',
                        'field2Value' => $attributeCode
                    ]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesMetadata()
    {
        /** @var AbstractAttribute[] $attribute */
        $attributeCodes = $this->attributeMetadataDataProvider->getAllAttributeCodes(
            LocationMetadataInterface::ENTITY_TYPE_LOCATION,
            LocationMetadataInterface::ATTRIBUTE_SET_ID_LOCATION
        );

        $allAttributesMetadata = [];

        foreach ($attributeCodes as $attributeCode) {
            try {
                $allAttributesMetadata[] = $this->getAttributeMetadata($attributeCode);
            } catch (NoSuchEntityException $e) {
                //If no such entity, skip
            }
        }

        return $allAttributesMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = LocationMetadataInterface::DATA_INTERFACE_NAME)
    {
        if (!$this->locationDataObjectMethods) {
            $dataObjectMethods = array_flip(get_class_methods($dataObjectClassName));
            $baseClassDataObjectMethods = array_flip(
                get_class_methods(\Magento\Framework\Api\AbstractExtensibleObject::class)
            );
            $this->locationDataObjectMethods = array_diff_key($dataObjectMethods, $baseClassDataObjectMethods);
        }
        $customAttributes = [];
        foreach ($this->getAllAttributesMetadata() as $attributeMetadata) {
            $attributeCode = $attributeMetadata->getAttributeCode();
            $camelCaseKey = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeCode);
            $isDataObjectMethod = isset($this->locationDataObjectMethods['get' . $camelCaseKey])
                || isset($this->locationDataObjectMethods['is' . $camelCaseKey]);

            if (!$isDataObjectMethod && !$attributeMetadata->isSystem()) {
                $customAttributes[] = $attributeMetadata;
            }
        }
        return $customAttributes;
    }
}
