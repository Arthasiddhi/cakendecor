<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Metadata;

use CND\Baker\Api\BakerMetadataInterface;

/**
 * Cached customer attribute metadata service
 */
class BakerCachedMetadata extends CachedMetadata implements BakerMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'baker';

    /**
     * Constructor
     *
     * @param BakerMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        BakerMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
