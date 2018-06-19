<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Config\Backend\Show;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Customer Show Address Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AddressOnly extends Baker
{
    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     */
    protected function _getAttributeObjects()
    {
        return [$this->_eavConfig->getAttribute('baker_address', $this->_getAttributeCode())];
    }
}
