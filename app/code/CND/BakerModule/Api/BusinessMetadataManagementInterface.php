<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\BakerModule\Api;

/**
 * Interface for managing customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface BusinessMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_ADDRESS = 'baker_business';
}
