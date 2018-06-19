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
    const ATTRIBUTE_SET_ID_ADDRESS = 2;

    const ENTITY_TYPE_ADDRESS = 'Baker_Location';

    const DATA_INTERFACE_NAME = \CND\BakerModule\Api\Data\LocationInterface::class;
}
