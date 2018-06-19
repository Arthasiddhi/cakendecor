<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller;

/**
 * Declarations of core registry keys used by the Baker module
 *
 */
class RegistryConstants
{
    /**
     * Registry key where current baker ID is stored
     */
    const CURRENT_CUSTOMER_ID = 'current_baker_id';

    /**
     * Registry key where current BakerGroup ID is stored
     */
    const CURRENT_GROUP_ID = 'current_group_id';
}
