<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Indexer\Address;

use CND\Baker\Model\ResourceModel\Address\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider extends \CND\Baker\Model\Indexer\AttributeProvider
{
    /**
     * EAV entity
     */
    const ENTITY = 'baker_address';

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        parent::__construct($eavConfig);
    }
}
