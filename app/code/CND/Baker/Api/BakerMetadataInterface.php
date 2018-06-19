<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

/**
 * Interface for retrieval information about customer attributes metadata.
 * @api
 * @since 100.0.2
 */
interface BakerMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_BAKER = 100;

    const ENTITY_TYPE_CUSTOMER = 'baker';

    const DATA_INTERFACE_NAME = \CND\Baker\Api\Data\BakerInterface::class;
}
