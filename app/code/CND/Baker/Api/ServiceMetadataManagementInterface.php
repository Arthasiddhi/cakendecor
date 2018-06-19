<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Api;

/**
 * Interface for managing customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface ServiceMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_ADDRESS = 'baker_service';
}
