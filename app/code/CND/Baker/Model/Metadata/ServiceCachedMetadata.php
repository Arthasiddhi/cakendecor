<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Metadata;

use CND\Baker\Api\ServiceMetadataInterface;

/**
 * Cached baker service attribute metadata
 */
class ServiceCachedMetadata extends CachedMetadata implements ServiceMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'baker_service';

    /**
     * Constructor
     *
     * @param ServiceMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        ServiceMetadata $metadata,
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
