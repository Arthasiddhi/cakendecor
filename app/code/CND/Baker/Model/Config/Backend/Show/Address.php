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
class Address extends Baker
{
    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     */
    protected function _getAttributeObjects()
    {
        $result = parent::_getAttributeObjects();
        $result[] = $this->_eavConfig->getAttribute('baker_address', $this->_getAttributeCode());
        return $result;
    }
}
