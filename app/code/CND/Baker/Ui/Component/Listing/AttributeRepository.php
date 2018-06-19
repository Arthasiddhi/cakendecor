<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Ui\Component\Listing;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\AddressMetadataManagementInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\BakerMetadataManagementInterface;
use CND\Baker\Api\Data\AttributeMetadataInterface;
use CND\Baker\Api\MetadataManagementInterface;
use CND\Baker\Model\Indexer\Attribute\Filter;

class AttributeRepository
{
    const BILLING_ADDRESS_PREFIX = 'billing_';

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var \CND\Baker\Api\BakerMetadataInterface
     */
    protected $bakerMetadata;

    /**
     * @var \CND\Baker\Api\AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var \CND\Baker\Api\BakerMetadataManagementInterface
     */
    protected $bakerMetadataManagement;

    /**
     * @var \CND\Baker\Api\AddressMetadataManagementInterface
     */
    protected $addressMetadataManagement;

    /**
     * @var \CND\Baker\Model\Indexer\Attribute\Filter
     */
    protected $attributeFilter;

    /**
     * @param BakerMetadataManagementInterface $bakerMetadataManagement
     * @param AddressMetadataManagementInterface $addressMetadataManagement
     * @param BakerMetadataInterface $bakerMetadata
     * @param AddressMetadataInterface $addressMetadata
     * @param Filter $attributeFiltering
     */
    public function __construct(
        BakerMetadataManagementInterface $bakerMetadataManagement,
        AddressMetadataManagementInterface $addressMetadataManagement,
        BakerMetadataInterface $bakerMetadata,
        AddressMetadataInterface $addressMetadata,
        Filter $attributeFiltering
    ) {
        $this->bakerMetadataManagement = $bakerMetadataManagement;
        $this->addressMetadataManagement = $addressMetadataManagement;
        $this->bakerMetadata = $bakerMetadata;
        $this->addressMetadata = $addressMetadata;
        $this->attributeFilter = $attributeFiltering;
    }

    /**
     * @return array
     */
    public function getList()
    {
        if (!$this->attributes) {
            $this->attributes = $this->getListForEntity(
                $this->bakerMetadata->getAllAttributesMetadata(),
                BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $this->bakerMetadataManagement
            );
            $this->attributes = array_merge(
                $this->attributes,
                $this->getListForEntity(
                    $this->addressMetadata->getAllAttributesMetadata(),
                    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    $this->addressMetadataManagement
                )
            );
        }

        return $this->attributeFilter->filter($this->attributes);
    }

    /**
     * @param AttributeMetadataInterface[] $metadata
     * @param string $entityTypeCode
     * @param MetadataManagementInterface $management
     * @return array
     */
    protected function getListForEntity(array $metadata, $entityTypeCode, MetadataManagementInterface $management)
    {
        $attributes = [];
        /** @var AttributeMetadataInterface $attribute */
        foreach ($metadata as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
                $attributeCode = self::BILLING_ADDRESS_PREFIX . $attribute->getAttributeCode();
            }
            $attributes[$attributeCode] = [
                AttributeMetadataInterface::ATTRIBUTE_CODE => $attributeCode,
                AttributeMetadataInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                AttributeMetadataInterface::FRONTEND_LABEL => $attribute->getFrontendLabel(),
                AttributeMetadataInterface::BACKEND_TYPE => $attribute->getBackendType(),
                AttributeMetadataInterface::OPTIONS => $this->getOptionArray($attribute->getOptions()),
                AttributeMetadataInterface::IS_USED_IN_GRID => $attribute->getIsUsedInGrid(),
                AttributeMetadataInterface::IS_VISIBLE_IN_GRID => $attribute->getIsVisibleInGrid(),
                AttributeMetadataInterface::IS_FILTERABLE_IN_GRID => $management->canBeFilterableInGrid($attribute),
                AttributeMetadataInterface::IS_SEARCHABLE_IN_GRID => $management->canBeSearchableInGrid($attribute),
                AttributeMetadataInterface::VALIDATION_RULES => $attribute->getValidationRules(),
                AttributeMetadataInterface::REQUIRED => $attribute->isRequired(),
                'entity_type_code' => $entityTypeCode,
            ];
        }

        return $attributes;
    }

    /**
     * Convert options to array
     *
     * @param array $options
     * @return array
     */
    protected function getOptionArray(array $options)
    {
        /** @var \CND\Baker\Api\Data\OptionInterface $option */
        foreach ($options as &$option) {
            $option = ['label' => (string)$option->getLabel(), 'value' => $option->getValue()];
        }
        return $options;
    }

    /**
     * @param string $code
     * @return []
     */
    public function getMetadataByCode($code)
    {
        return isset($this->getList()[$code]) ? $this->getList()[$code] : null;
    }
}
