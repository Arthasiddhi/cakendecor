<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\BakerData;

/**
 * Section source interface
 *
 * @api Use to define data sections in baker data which are transported from backend to frontend local storage
 * @since 100.0.2
 */
interface SectionSourceInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData();
}
