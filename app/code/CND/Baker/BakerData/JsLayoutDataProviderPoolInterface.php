<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\BakerData;

/**
 * Js layout data provider pool interface
 */
interface JsLayoutDataProviderPoolInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
