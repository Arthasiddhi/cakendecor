<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

/**
 * Interface for retrieval information about customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface ServiceMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_SERVICE = 103;

    const ENTITY_TYPE_ADDRESS = 'baker_service';

    const DATA_INTERFACE_NAME = \CND\Baker\Api\Data\LocationInterface::class;
}
