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
interface LocationMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_LOCATION = 102;

    const ENTITY_TYPE_ADDRESS = 'baker_location';

    const DATA_INTERFACE_NAME = LocationInterface::class;
}
