<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\BakerData;

/**
 * Js layout data provider interface
 *
 * @api
 * @since 100.0.2
 */
interface JsLayoutDataProviderInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
