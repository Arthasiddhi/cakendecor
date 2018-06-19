<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Metadata;

use CND\Baker\Api\LocationMetadataInterface;

/**
 * Cached baker location attribute metadata
 */
class LocationCachedMetadata extends CachedMetadata implements LocationMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'baker_location';

    /**
     * Constructor
     *
     * @param LocationMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        LocationMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }


    public function getAttributes($formCode)
    {
        // TODO: Implement getAttributes() method.
    }

    public function getAllAttributesMetadata()
    {
        // TODO: Implement getAllAttributesMetadata() method.
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }public function getAttributeMetadata($attributeCode)
{
    // TODO: Implement getAttributeMetadata() method.
}

public function getCustomAttributesMetadata($dataInterfaceName = '')
{
    // TODO: Implement getCustomAttributesMetadata() method.
}
}
