<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Form;

/**
 * Customer Form Attribute Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Form\Attribute
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('baker_form_attribute', 'attribute_id');
    }
}
