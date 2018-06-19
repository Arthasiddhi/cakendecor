<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Indexer\Location;

use CND\Baker\Model\ResourceModel\Location\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider extends \CND\Baker\Model\Indexer\AttributeProvider
{
    /**
     * EAV entity
     */
    const ENTITY = 'baker_location';

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        parent::__construct($eavConfig);
    }
}
